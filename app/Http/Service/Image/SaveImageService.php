<?php

declare(strict_types=1);

namespace App\Http\Service\Image;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait SaveImageService
{

    public function saveImageBase64(?string $base64Image, string $folder = 'products'): ?string
    {
        if(empty($base64Image)){
            return null;
        }

        // Obtener el tipo MIME de la imagen
        preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches);
        if (!isset($matches[1])) {
            throw new Exception("Error: Not extension");
        }

        $fileExtension = '.' . $matches[1]; // Extraer extensión (jpg, png, etc.)
        $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));

        if ($image === false) {
            throw new Exception("Error: Cannot decode Base64");
        }

        // Generar un nombre único
        $filename = Str::uuid() . $fileExtension;
        $path = $folder . '/' . $filename;

        // Guardar la imagen en el storage
        Storage::disk('public')->put($path, $image);

        return asset('storage/'.$path);
    }
}