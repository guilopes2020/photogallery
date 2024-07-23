<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index() {
        $images = Image::all();
        return view('index', ['images' => $images]);
    }

    public function upload(Request $request) {

        if (!$request->hasFile('image')) {
            return false;
        }

        $tile = $request->only('title');
        $image = $request->file('image');
        $imageName = $image->hashName();
        $hash = $image->storePublicly('uploads', 'public', $imageName);
        $url = asset('storage/'.$hash);

        Image::create([
            'title' => $tile['title'],
            'url'   => $url,
        ]);

        return redirect()->route('index');
    }

    public function delete() {

    }
}
