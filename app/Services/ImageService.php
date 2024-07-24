<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\ImageServiceInterface;
use Error;
use Exception;

class ImageService implements ImageServiceInterface
{
    public function storeNewImage($image, $title): Image
    {
        try {
            $url = $this->storeImageInDisk($image);
            return $this->storageImageInDatabase($title, $url);
        } catch (Exception $e) {
            throw new Error('Erro ao gravar a imagem, tente novamente!');
            return false;
        }
        
    }

    public function deleteImageFromDisk($imageUrl): void
    {
        $imagePath = str_replace(asset('storage/'), '', $imageUrl);
        Storage::disk('public')->delete($imagePath);
    }

    public function deleteDatabaseImage($databaseImage): bool
    {
        if (!$databaseImage) {
            return false;
        }

        $databaseImage->delete();
        return true;
    }

    public function rollback($databaseImage)
    {
        $this->deleteDatabaseImage($databaseImage);
        $this->deleteImageFromDisk($databaseImage->url);
    }

    private function storeImageInDisk($image): string
    {
        $imageName = $image->storePublicly('uploads', 'public');
        return asset('storage/'.$imageName);
    }

    private function storageImageInDatabase($tile, $url): Image
    {
        return Image::create([
            'title' => $tile,
            'url'   => $url,
        ]);
    }
}