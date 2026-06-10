<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Redimensiona, optimiza y convierte una imagen subida a formato WebP utilizando la librería GD nativa de PHP.
     * Cuenta con fallback automático al almacenamiento estándar si la extensión GD no está disponible o falla.
     */
    public function optimizeAndSave(UploadedFile $file, string $directory, int $maxWidth = 1200, int $quality = 75): string
    {
        // Fallback: Si no está activa la extensión GD, guardar el archivo tal cual
        if (!extension_loaded('gd')) {
            return $file->store($directory, 'local');
        }

        $tempPath = $file->getRealPath();
        $mime = $file->getMimeType();

        // Cargar imagen según su tipo MIME
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $srcImage = @imagecreatefromjpeg($tempPath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($tempPath);
                break;
            case 'image/webp':
                $srcImage = @imagecreatefromwebp($tempPath);
                break;
            case 'image/gif':
                $srcImage = @imagecreatefromgif($tempPath);
                break;
            default:
                // Formato no soportado para conversión, guardar archivo original
                return $file->store($directory, 'local');
        }

        if (!$srcImage) {
            // Error al cargar la imagen, usar fallback
            return $file->store($directory, 'local');
        }

        // Corregir orientación basada en metadatos EXIF (común en fotos de celulares)
        if (($mime === 'image/jpeg' || $mime === 'image/jpg') && function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($tempPath);
                if (isset($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $srcImage = imagerotate($srcImage, 180, 0);
                            break;
                        case 6:
                            $srcImage = imagerotate($srcImage, -90, 0);
                            break;
                        case 8:
                            $srcImage = imagerotate($srcImage, 90, 0);
                            break;
                    }
                }
            } catch (\Exception $e) {
                // Ignorar errores de rotación EXIF y continuar
            }
        }

        $width = imagesx($srcImage);
        $height = imagesy($srcImage);

        // Calcular nuevas dimensiones si supera el ancho máximo
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) (($height / $width) * $newWidth);

            // Redimensionar
            if (function_exists('imagescale')) {
                $dstImage = imagescale($srcImage, $newWidth, $newHeight);
            } else {
                $dstImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            }
        } else {
            $dstImage = $srcImage;
        }

        if (!$dstImage) {
            imagedestroy($srcImage);
            return $file->store($directory, 'local');
        }

        // Capturar buffer de salida para WebP
        ob_start();
        $success = imagewebp($dstImage, null, $quality);
        $webpData = ob_get_clean();

        // Liberar memoria
        if ($dstImage !== $srcImage) {
            imagedestroy($dstImage);
        }
        imagedestroy($srcImage);

        if (!$success || !$webpData) {
            return $file->store($directory, 'local');
        }

        // Guardar el archivo WebP en el storage local
        $filename = md5(uniqid()) . '.webp';
        $relativePath = $directory . '/' . $filename;

        Storage::disk('local')->put($relativePath, $webpData);

        return $relativePath;
    }

    /**
     * Redimensiona, optimiza y convierte una imagen subida a formato WebP y la guarda en el directorio public_path físico.
     */
    public function optimizeAndSavePublic(UploadedFile $file, string $directory, int $maxWidth = 1200, int $quality = 75): string
    {
        $filename = md5(uniqid()) . '.webp';
        $publicDir = public_path($directory);

        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        // Fallback: Si no está activa la extensión GD, guardar el archivo tal cual
        if (!extension_loaded('gd')) {
            $fallbackFilename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->move($publicDir, $fallbackFilename);
            return $directory . '/' . $fallbackFilename;
        }

        $tempPath = $file->getRealPath();
        $mime = $file->getMimeType();

        // Cargar imagen según su tipo MIME
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $srcImage = @imagecreatefromjpeg($tempPath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($tempPath);
                break;
            case 'image/webp':
                $srcImage = @imagecreatefromwebp($tempPath);
                break;
            case 'image/gif':
                $srcImage = @imagecreatefromgif($tempPath);
                break;
            default:
                // Formato no soportado para conversión, guardar archivo original
                $fallbackFilename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
                $file->move($publicDir, $fallbackFilename);
                return $directory . '/' . $fallbackFilename;
        }

        if (!$srcImage) {
            $fallbackFilename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->move($publicDir, $fallbackFilename);
            return $directory . '/' . $fallbackFilename;
        }

        // Corregir orientación basada en metadatos EXIF (común en fotos de celulares)
        if (($mime === 'image/jpeg' || $mime === 'image/jpg') && function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($tempPath);
                if (isset($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $srcImage = imagerotate($srcImage, 180, 0);
                            break;
                        case 6:
                            $srcImage = imagerotate($srcImage, -90, 0);
                            break;
                        case 8:
                            $srcImage = imagerotate($srcImage, 90, 0);
                            break;
                    }
                }
            } catch (\Exception $e) {
                // Ignorar
            }
        }

        $width = imagesx($srcImage);
        $height = imagesy($srcImage);

        // Calcular nuevas dimensiones si supera el ancho máximo
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) (($height / $width) * $newWidth);

            // Redimensionar
            if (function_exists('imagescale')) {
                $dstImage = imagescale($srcImage, $newWidth, $newHeight);
            } else {
                $dstImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            }
        } else {
            $dstImage = $srcImage;
        }

        if (!$dstImage) {
            imagedestroy($srcImage);
            $fallbackFilename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->move($publicDir, $fallbackFilename);
            return $directory . '/' . $fallbackFilename;
        }

        // Capturar buffer de salida para WebP
        ob_start();
        $success = imagewebp($dstImage, null, $quality);
        $webpData = ob_get_clean();

        // Liberar memoria
        if ($dstImage !== $srcImage) {
            imagedestroy($dstImage);
        }
        imagedestroy($srcImage);

        if (!$success || !$webpData) {
            $fallbackFilename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->move($publicDir, $fallbackFilename);
            return $directory . '/' . $fallbackFilename;
        }

        // Guardar el archivo WebP directamente en public_path
        $absolutePath = $publicDir . '/' . $filename;
        file_put_contents($absolutePath, $webpData);

        return $directory . '/' . $filename;
    }
}
