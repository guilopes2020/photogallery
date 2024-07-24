<?php

namespace App\Interfaces;

use App\Models\Image;

interface ImageServiceInterface
{
    public function storeNewImage($image, $title): Image;
    public function deleteImageFromDisk($imageUrl): void;
    public function deleteDatabaseImage($databaseImage): bool;
}