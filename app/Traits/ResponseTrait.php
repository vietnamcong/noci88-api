<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\UserLog;
use App\Models\Attachment;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Handlers\FileUploadHandler;
use DB;

trait ResponseTrait
{
	// Default status code 200
	protected $statusCode = FoundationResponse::HTTP_OK;

	protected $imageMaxSize = 10 * 1024 * 1024;
	protected $fileMaxSize = 10 * 1024 * 1024;

	public function getStatusCode()
	{
		return $this->statusCode;
	}

	public function setStatusCode($statusCode)
	{
		$this->statusCode = $statusCode;
		return $this;
	}

	public function respond($data, $header = [])
	{
		return response()->json($data, $this->getStatusCode(), $header)
			->setEncodingOptions(JSON_UNESCAPED_UNICODE);
	}

	public function message($message, $status = "success")
	{
		return $this->status($status, [], null, $message);
	}

	public function messageWithCode($message, $code, $status = "success")
	{
		return $this->status($status, [], $code, $message);
	}

	public function internalError($message = "Internal Error!")
	{
		return $this->failed($message, FoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
	}

	public function success($data, $message = '', $status = "success")
	{
		return $this->status($status, $data, null, $message);
	}

	public function successWithUrl($data, $url, $message = '', $status = "success")
	{
		$data['url'] = $url;
		return $this->status($status, $data, null, $message);
	}

	public function successWithCode($data, $message, $code, $status = "success")
	{
		return $this->status($status, $data, $code, $message);
	}

	public function failed($message, $code = FoundationResponse::HTTP_BAD_REQUEST, $status = 'error', $data = [])
	{
		return $this->status($status, $data, $code, $message);
	}

	/**
	 * Return formatted response information
	 *
	 * @param string $status
	 * @param array $data
	 * @param integer $code
	 * @param string $message
	 * @return void
	 * @Description
	 * @example
	 * @since
	 * @date 2020-02-22
	 */
	public function status($status, array $data, $code = null, $message = '')
	{
		if ($code) {
			$this->setStatusCode($code);
		}
		$status = [
			'status' => $status,
			'code' => $this->statusCode,
			'message' => $message
		];

		$data = array_merge($status, $data);
		return $this->respond($data);
	}

	/**
	 * Page Not Found 404
	 */
	public function notFond($message = 'Not Fond!')
	{
		return $this->failed($message, Foundationresponse::HTTP_NOT_FOUND);
	}

	public function sendMessageTelegram($message)
	{
		$config = SystemConfig::query()->latest()->getConfigGroup('telegram');
        $botID = $config["telegram_bot_id"];
        $chatID = $config["telegram_chat_id"];

		// $chatId = env('TELEGRAM_CHAT_ID');
		$telegramToken = env('TELEGRAM_TOKEN');

		$url = "https://api.telegram.org/bot$telegramToken/sendMessage";
		// $url = "https://api.telegram.org/bot$telegramToken/sendMessage";

		$response = Http::post($url, [
			'chat_id' => $chatId,
			'text' => $message,
			'parse_mode' => 'HTML'
		]);

		// Log::info('sendMessageTelegram: '.$response);
		if ($response->ok()) {
			// Message sent successfully
			return true;
		} else {
			// Failed to send the message
			return false;
		}
	}

	public function writeLogs($request, $input)
	{
		$ip = get_client_ip();
		$get_address = Http::get("http://ip-api.com/json/{$ip}");
		$data = '';
		$address = '';

		if ($get_address->ok()) {
			$data = $get_address->json();
			$address = optional($data)['regionName'] . ', ' . optional($data)['city'] . ', ' . optional($data)['country'];
		}

		$log = [
			'user_id' => isset($input['user']) ? $input['user']->id : auth()->id(),
			'ip' => $ip,
			'url' => $request->route()->getName() ?? '',
			'ua' => json_encode($request->userAgent()),
			'other' => json_encode($data),
			'address' => $address,
			'data' => json_encode($input['data']) ?? '',
			'action' => $input['action'] ?? '',
			'type' => $input['type'] ?? '',
			'description' => $input['description'] ?? '',
		];

		try {
			DB::transaction(function () use ($log) {
				UserLog::create($log);
			});

			return true;
		} catch (Exception $e) {
			Log::error($e->getMessage());
			return false;
		}
	}

	public function uploadImages($file, $folder)
	{
		// File must have filetype
		$fileName = explode('.', $file->getClientOriginalName());
		if (count($fileName) < 2) return $this->failed(trans('res.upload.file_type_error'));

		// Determine whether the file exceeds the size
		if ($file->getSize() > $this->imageMaxSize) return $this->failed(trans('res.upload.file_size_error'));

		$resultImage = app(FileUploadHandler::class)->uploadImage($file, $folder, false); // $request->get("max_width", false)
		$urlImage = Arr::only($resultImage['data'], ['file_url'])['file_url'];

		return $urlImage;
	}

	public function uploadFiles($file, $folder)
	{
		// File must have filetype
		$fileName = explode('.', $file->getClientOriginalName());
		if (count($fileName) < 2) return $this->failed(trans('res.upload.file_type_error'));

		// Determine whether the file exceeds the size
		if ($file->getSize() > $this->imageMaxSize) return $this->failed(trans('res.upload.file_size_error'));

		$resultImage = app(FileUploadHandler::class)->uploadFile($file, $folder, false); // $request->get("max_width", false)
		$urlFile = Arr::only($resultImage['data'], ['file_url'])['file_url'];

		return $urlFile;
	}

	public function getdDmensions($url)
	{
		return app(FileUploadHandler::class)->getImageSizeByUrl($url);
	}

	public function deleteImages($file)
	{
		if (!Str::startsWith($file, 'http')) return $this->success([], trans('res.base.delete_success'));

		$fileNameImage = basename($file);
		$attach = Attachment::query()->where('storage_name', $fileNameImage)->first();
		if ($attach) {
			app(FileUploadHandler::class)->deleteByStoragePath($attach->storage_path);
			$attach->delete();
		}

		return true;
	}

	public function checkPermission($permission)
    {
        if (!auth()->user()->can($permission)) {
			// abort(403, 'Bạn không có quyền thực hiện hành động này.');
			return false;
        }
    }
}
