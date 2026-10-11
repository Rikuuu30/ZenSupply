<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

/** Validates and re-encodes uploaded images into safe, display-sized JPEGs. */
class ImageUploadService
{
    public function storeThumbnail(UploadedFile $file, string $folder, string $prefix, int $size): string
    {
        if (! in_array($folder, ['avatars', 'products'], true) || ! in_array($prefix, ['avatar', 'product'], true)) {
            throw new RuntimeException('Invalid image destination.');
        }
        if (! $file->isValid() || $file->hasMoved() || $file->getSize() > 2 * 1024 * 1024) {
            throw new RuntimeException('Choose a valid image no larger than 2 MB.');
        }

        $imageInfo = @getimagesize($file->getTempName());
        $mime = $file->getMimeType();
        if ($imageInfo === false || ! in_array($mime, ['image/jpeg', 'image/png'], true)) {
            throw new RuntimeException('Only genuine JPG or PNG images are accepted.');
        }
        [$width, $height] = $imageInfo;
        if ($width < 1 || $height < 1 || $width > 8000 || $height > 8000 || $width * $height > 24_000_000) {
            throw new RuntimeException('The image dimensions are too large to process safely.');
        }

        $directory = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('The upload folder could not be created.');
        }
        $filename = $prefix . '-' . bin2hex(random_bytes(16)) . '.jpg';
        $path = $directory . $filename;
        try {
            service('image')->withFile($file->getTempName())->fit($size, $size, 'center')->save($path, 84);
        } catch (\Throwable $exception) {
            if (is_file($path)) {
                unlink($path);
            }
            throw new RuntimeException('The image could not be processed. Please try another JPG or PNG.', 0, $exception);
        }

        return $folder . '/' . $filename;
    }

    public function removeGenerated(?string $relativePath): void
    {
        if ($relativePath === null || preg_match('~\A(avatars/avatar|products/product)-[a-f0-9]{32}\.jpg\z~', $relativePath) !== 1) {
            return;
        }
        $path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($path)) {
            unlink($path);
        }
    }
}
