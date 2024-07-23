<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index() {
        return view('index');
    }

    public function upload(Request $request) {

        if (!$request->hasFile('image')) {
            return false;
        }

        $image = $request->file('image');
        $imageName = $image->hashName();
        $hash = $image->storePublicly('uploads', 'public', $imageName);
        dd($hash);
    }

    public function delete() {

    }
}
