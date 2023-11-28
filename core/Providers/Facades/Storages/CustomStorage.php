<?php

namespace Core\Providers\Facades\Storages;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class CustomStorage
{
    /**
     * @param $name
     * @param $arguments
     * @return false|mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([Storage::class, $name], $arguments);
    }

    /**
     * @param $file
     * @return array|string|string[]
     */
    public function path($file)
    {
        return str_replace('/', '\\', storage_path($file));
    }

    /**
     * @param $fileName
     * @return string
     */
    public function url($fileName)
    {
        if (!$fileName) {
            return '';
        }

        if (strpos($fileName, 'http') !== false) {
            return $fileName;
        }

        $fileName = str_replace('\\', '/', $fileName);

        return urldecode(Storage::url($fileName));
    }

    /**
     * @param $filePath
     * @param $newName
     * @return mixed|string
     * @throws \Exception
     */
    public function moveFromTmpToMedia($filePath, $newName = '')
    {
        if (!Storage::exists($filePath)) {
            throw new UploadException(trans('messages.file_dose_not_exist', ['file' => $filePath]));
        }

        $newFilePath = getMediaDir($newName ? $newName : $filePath);
        $nameBackup = $newFilePath . '_' . time();

        if (Storage::exists($newFilePath)) {
            // rename
            Storage::move($newFilePath, $nameBackup);
        }

        try {
            $r = Storage::move($filePath, $newFilePath);

            if (!$r) {
                throw new UploadException(trans('messages.file_upload_failed', ['file' => $filePath]));
            }

            if (Storage::exists($nameBackup)) {
                // rename
                Storage::delete($nameBackup);
            }

            return $newFilePath;
        } catch (\Exception $exception) {
            // rollback
            if (Storage::exists($nameBackup)) {
                // rename
                Storage::move($nameBackup, $newFilePath);
            }

            throw $exception;
        }
    }

    /**
     * @param $file
     * @param $content
     * @return bool
     */
    public function put($file, $content)
    {
        if (!$this->isUploadFile($content)) {
            $content = $this->base64ToFile($content);
        }

        return Storage::put($file, $content);
    }

    /**
     * @param $data
     * @return bool
     */
    public function isUploadFile($data)
    {
        return $data instanceof UploadedFile;
    }

    /**
     * @param $fileData
     * @return false|string
     */
    public function base64ToFile($fileData)
    {
        @list($type, $fileData) = explode(';', $fileData);
        @list(, $fileData) = explode(',', $fileData);

        return base64_decode($fileData);
    }

    /**
     * Custom from function uploadToTmp() and moveTmpToMedia()
     * @param $file
     * @param string $fileName
     * @param array $options
     * @return array
     */
    public function uploadFileToMedia($file, $fileName = '', $options = ['public-read'])
    {
        if (empty($fileName)) {
            $fileName = $this->genFileName($file);
        }

        $this->validationFile($fileName, $file);
        $newFileSavePath = getMediaDir($fileName);

        if ($this->isUploadFile($file)) {
            $r = Storage::putFileAs(getMediaDir(), $file, $fileName, $options);
        } else {
            $r = $this->put($newFileSavePath, $file);
        }

        return [
            'status' => $r ? true : false,
            'filename' => $r ? $newFileSavePath : '',
        ];
    }

    /**
     * @param $fileName
     * @param $content
     * @return bool
     */
    protected function validationFile($fileName, $content)
    {
        if ($this->isUploadFile($content)) {
            $ext = $content->getClientOriginalExtension();
        } else {
            $ext = Arr::last(explode('.', $fileName));
        }

        $extBlacklist = (array) getConfig('ext_blacklist', ['php', 'phtml', 'html']);

        if (in_array($ext, $extBlacklist)) {
            throw new UploadException(trans('messages.file_upload_failed', ['file' => $fileName]));
        }

        return true;
    }

    /**
     * @param $file
     * @return string
     */
    public function genFileName($file)
    {
        $pathInfo = $this->mbPathinfo($file->getClientOriginalName());
        $filename = data_get($pathInfo, 'filename') ?? '';
        $filename = str_replace([ '!', '@', '#', '$', '%', '^', '&', '/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'], [], $filename);
        $fileName = getControllerName() . '/' . $filename . '_' . time() . '.' . data_get($pathInfo, 'extension');

        return $fileName;
    }

    /**
     * Get path info of file upload
     *
     * @param $filepath
     * @return string[]
     */
    public function mbPathInfo($filepath) {
        preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $filepath, $m);

        return [
            'dirname' => $m[1] ?? '',
            'basename' => $m[2] ?? '',
            'extension' => $m[5] ?? '',
            'filename' => $m[3] ?? '',
        ];
    }

    /**
     * Upload file to tmp
     *
     * @param $fileName
     * @param $content
     * @return string
     */
    public function uploadToTmp($content, $fileName = null)
    {
        if (empty($fileName)) {
            $fileName = $this->genFileName($content);
        }

        $this->validationFile($fileName, $content);
        $newFilePath = getTmpUploadDir(date('Y-m-d')) . '/' . $fileName;
        $this->deleteTmpDaily();

        if ($this->isUploadFile($content)) {
            $r = Storage::putFileAs(getTmpUploadDir(date('Y-m-d')), $content, $fileName, 'public');

            if (!$r) {
                throw new  UploadException(trans('messages.file_upload_failed', ['file' => $newFilePath]));
            }

            return $newFilePath;
        }

        $r = $this->put($newFilePath, $content);

        if (!$r) {
            throw new UploadException(trans('messages.file_upload_failed', ['file' => $newFilePath]));
        }

        return $newFilePath;
    }

    /**
     * delete tmp file
     */
    public function deleteTmpDaily()
    {
        for ($i = 1; $i <= 30; $i++) {
            $directory = getTmpUploadDir(today()->subDays($i)->format('Y-m-d'));
            Storage::deleteDirectory($directory);
        }
    }
}
