<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    public function index() {
        $images = Image::all();
        return view('index', ['images' => $images]);
    }

    public function upload(Request $request) {

        $this->validateRequest($request);

        $tile = $request->only('title');
        $image = $request->file('image');

        try {
            $url = $this->storeImageInDisk($image);
            $databaseImage = $this->storageImageInDatabase($tile['title'], $url);
        } catch (Exception $e) {
            $this->deleteDatabaseImage($databaseImage);
            $this->deleteImageFromDisk($url);
            
            return redirect()->back()->withErrors(['error' => 'erro ao salvar a imagem, tente novamente!']);
        }

        return redirect()->route('index');
    }

    public function delete($id) {
        $image = Image::findOrFail($id);
        $url = parse_url($image->url);
        $path = ltrim($url['path'], '/storage\/');

        if (!Storage::disk('public')->exists($path)) {
            return false;
        }

        Storage::disk('public')->delete($path);
        $image->delete();

        return redirect()->route('index');
    }
    
    private function validateRequest(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255|min:6',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            Rule::dimensions()->maxWidth(800)->maxHeight(800)
        ]);
    }

    private function storeImageInDisk($image) {
        $imageName = $image->storePublicly('uploads', 'public');
        return asset('storage/'.$imageName);
    }

    private function storageImageInDatabase($tile, $url) {
        return Image::create([
            'title' => $tile,
            'url'   => $url,
        ]);
    }

    private function deleteImageFromDisk($imageUrl) {
        $imagePath = str_replace(asset('storage/'), '', $imageUrl);
        Storage::disk('public')->delete($imagePath);
    }

    private function deleteDatabaseImage($databaseImage) {
        if ($databaseImage) {
            $databaseImage->delete();
        }
    }
}
