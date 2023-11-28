<?php

namespace App\Http\Controllers\Supports;

use Core\Providers\Facades\Storages\BaseStorage;
use Illuminate\Support\Facades\Storage;

trait HasUploadFile
{
    public function uploadFileToTmp($file)
    {
        $this->uploadFile($file, true);
    }

    public function uploadFile($file, $uploadTmp = false)
    {
        if (empty($file)) {
            return '';
        }

        $isBase64 = false;

        if (!isset($file) || empty($file) || is_scalar($file)) {
            $isBase64 = isBase64Img($file);

            if (!$isBase64) {
                return '';
            }
        }

        $pathInfo = BaseStorage::mbPathInfo($file->getClientOriginalName());
        $fileName = data_get($pathInfo, 'basename') ?? '';
        $fileName = str_replace(['!', '@', '#', '$', '%', '^', '&', '/', '*', '+', '?', '|', '[', ']', '{', '}', '\\'], [], $fileName);
        $unique = hash('sha1', uniqid(time(), true));
        $fileName = $unique . '.' . ($isBase64 ? 'png' : $file->getClientOriginalExtension());

        // upload to media
        if (!$uploadTmp) {
            $mediaUrl = BaseStorage::uploadFileToMedia($file, $fileName);

            if ($mediaUrl['status']) {
                return $mediaUrl['filename'];
            }
        }

        return BaseStorage::uploadToTmp($file, $fileName);
    }

    public function deleteFile($filePath)
    {
        Storage::delete($filePath);
    }
}
