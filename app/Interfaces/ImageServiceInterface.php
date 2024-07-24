<?php

namespace App\Interfaces;

use App\Models\Image;

interface ImageServiceInterface
{
    public function storeNewImage($image, $title): Image;
    public function deleteImageFromDisk($imageUrl): bool;
    public function deleteDatabaseImage($databaseImage): bool;
}