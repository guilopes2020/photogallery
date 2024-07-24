<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Services\ImageService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $images = Image::all();
        return view('index', ['images' => $images]);
    }

    public function upload(Request $request)
    {

        $this->validateRequest($request);

        $tile = $request->only('title');
        $image = $request->file('image');

        try {
            $this->imageService->storeNewImage($image, $tile['title']);
        } catch (Exception $e) {
            $this->imageService->rollback();
            
            return redirect()->back()->withErrors(['error' => 'erro ao salvar a imagem, tente novamente!']);
        }

        return redirect()->route('index');
    }

    public function delete($id)
    {
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
    
    private function validateRequest(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|min:6',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            Rule::dimensions()->maxWidth(800)->maxHeight(800)
        ]);
    }

}
