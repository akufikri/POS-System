<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function upload(UploadedFile $file, string $folder = 'products'): string
    {
        // Cloudinary support: set CLOUDINARY_URL in .env
        if (config('services.cloudinary.url')) {
            return $this->uploadToCloudinary($file, $folder);
        }

        // Fallback: local storage (good for development)
        $path = $file->store($folder, 'public');
        return Storage::disk('public')->url($path);
    }

    public function delete(string $url): void
    {
        if (config('services.cloudinary.url')) {
            // Cloudinary delete by public_id would go here
            return;
        }

        $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
        Storage::disk('public')->delete($path);
    }

    private function uploadToCloudinary(UploadedFile $file, string $folder): string
    {
        $cloudinaryUrl = config('services.cloudinary.url');
        preg_match('/cloudinary:\/\/(\d+):([^@]+)@(.+)/', $cloudinaryUrl, $matches);

        [, $apiKey, $apiSecret, $cloudName] = $matches;

        $timestamp = time();
        $signature = sha1("folder={$folder}&timestamp={$timestamp}{$apiSecret}");

        $response = \Illuminate\Support\Facades\Http::attach(
            'file', file_get_contents($file->getRealPath()), $file->getClientOriginalName()
        )->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'folder' => $folder,
            'signature' => $signature,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Cloudinary upload failed: ' . $response->body());
        }

        return $response->json('secure_url');
    }
}
