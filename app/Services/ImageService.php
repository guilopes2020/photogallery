<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\ImageServiceInterface;
use Error;
use Exception;

class ImageService implements ImageServiceInterface
{
    private $rollbackStack = null;

    public function storeNewImage($image, $title): Image
    {
        try {
            $url = $this->storeImageInDisk($image);
            return $this->storageImageInDatabase($title, $url);
        } catch (Exception $e) {
            throw new Error('Erro ao gravar a imagem, tente novamente!');
        }
        
    }

    public function deleteImageFromDisk($imageUrl): bool
    {
        $imagePath = str_replace(asset('storage/'), '', $imageUrl);
        Storage::disk('public')->delete($imagePath);
        return true;
    }

    public function deleteDatabaseImage($databaseImage): bool
    {
        if (!$databaseImage) {
            return false;
        }

        $databaseImage->delete();
        return true;
    }

    public function rollback()
    {
        while (!empty($this->rollbackStack)) {
            $rolbackAction = array_pop($this->rollbackStack);
            $method = $rolbackAction['method'];
            $params = $rolbackAction['params'];
            if (method_exists($this, $method)) {
                call_user_func_array([$this, $method], $params);
            }
        }
    }

    private function storeImageInDisk($image): string
    {
        $imageName = $image->storePublicly('uploads', 'public');
        $url = asset('storage/'.$imageName);
        $this->addToRollbackQueue('deleteImageFromDisk', [$url]);
        return $url;
    }

    private function storageImageInDatabase($tile, $url): Image
    {
        $image =  Image::create([
            'title' => $tile,
            'url'   => $url
        ]);

        $this->addToRollbackQueue('deleteDatabaseImage', [$image]);

        return $image;
    }

    private function addToRollbackQueue($method, $params = [])
    {
        $this->rollbackStack[] = [
            'methhod' => $method,
            'params'  => $params
        ];
    }
}