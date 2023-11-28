<?php

namespace Core\Services;

use Core\Providers\Facades\Storages\BaseStorage;
use Core\Repositories\BaseRepository;

class BaseService
{
    /**
     * @var BaseRepository $repository
     */
    protected $repository;

    /**
     * @param $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return BaseRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Store data
     *
     * @param $params
     * @return bool
     */
    public function store($params)
    {
        try {
            $this->prepareDataBeforeSave($params);
            $this->uploadFile($params);
            $this->getRepository()->create($params);

            return true;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return false;
    }

    /**
     * Update data
     *
     * @param $id
     * @param $params
     * @return bool
     */
    public function update($id, $params)
    {
        try {
            $this->prepareDataBeforeSave($params);
            $this->uploadFile($params);
            $this->getRepository()->update($id, $params);

            return true;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return false;
    }

    /**
     * * Upload file
     *
     * @param $params
     * @param string $subFolder
     * @param bool $uploadTmp
     */
    protected function uploadFile(&$params, $subFolder = '', $uploadTmp = false)
    {
        $uploadFields = $params['upload_fields'] ?? [];

        foreach ($uploadFields as $field) {
            $file = $params[$field] ?? '';

            if (empty($file)) {
                continue;
            }

            $isBase64 = false;

            if (!isset($file) || empty($file) || is_scalar($file)) {
                $isBase64 = isBase64Img($file);

                if (!$isBase64) {
                    continue;
                }
            }

            $pathInfo = BaseStorage::mbPathInfo($file->getClientOriginalName());
            $fileName = data_get($pathInfo, 'basename') ?? '';
            $fileName = str_replace(['!', '@', '#', '$', '%', '^', '&', '/', '*', '+', '?', '|', '[', ']', '{', '}', '\\'], [], $fileName);
            $originalName = $isBase64 ? 'base64' : $fileName;
            $unique = hash('sha1', uniqid(time(), true));
            $fileName = ($subFolder ? $subFolder . '/' : '') . $unique . '.' . ($isBase64 ? 'png' : $file->getClientOriginalExtension());

            // upload to media
            if (!$uploadTmp) {
                $mediaUrl = BaseStorage::uploadFileToMedia($file, $fileName);

                if ($mediaUrl['status']) {
                    $params[$field] = $mediaUrl['filename'];
                }

                continue;
            }

            $fileName = BaseStorage::uploadToTmp($file, $fileName);
            $params[$field] = $originalName;
            $params['original_files'] = [$field => $originalName];
            $tmp[$field] = $fileName;
            $params['tmp_file'] = $tmp;
        }
    }

    /**
     * Delete item
     *
     * @param $id
     * @return bool
     */
    public function destroy($id)
    {
        try {
            $this->getRepository()->delete($id);

            return true;
        } catch (\Exception $exception) {
            logError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return false;
    }

    /**
     * @param $params
     * @return mixed
     */
    protected function prepareDataBeforeSave(&$params)
    {

    }
}
