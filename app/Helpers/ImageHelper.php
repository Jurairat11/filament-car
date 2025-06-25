<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class ImageHelper
{

    public static function convertToUrl(?string $filename): ?string
    {
        //Log::info('Helper called with: ' . $filename); // หรือ dd($filename);

        if (!$filename || str_starts_with($filename, 'http')) {
        return $filename;
        }

        $baseUrl = env('APP_URL', config('app.url'));
        return $baseUrl . '/storage/form-attachments/' . $filename;
    }

}
