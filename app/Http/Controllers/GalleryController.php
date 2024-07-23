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

        $request->validate([
            'title' => 'required|string|max:255|min:6',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            Rule::dimensions()->maxWidth(800)->maxHeight(800)
        ]);

        $tile = $request->only('title');
        $image = $request->file('image');
        $imageName = $image->hashName();
        $hash = $image->storePublicly('uploads', 'public', $imageName);
        $url = asset('storage/'.$hash);

        try {
            Image::create([
                'title' => $tile['title'],
                'url'   => $url,
            ]);
        } catch (Exception $e) {
            Storage::disk('public')->delete($hash);
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
}
