<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
/**
 * 用户错误行为触发的异常
 * Class InvalidRequestException
 * @package App\Exceptions
 */
class InvalidRequestException extends Exception
{
    use ResponseTrait;

    protected $data;

    public function __construct(string $message = "", int $code = 400, $data = [])
    {
        $this->data = $data;
        parent::__construct($message, $code);
    }

    /**
     * Laravel 5.5 之后支持在异常类中定义 render() 方法，该异常被触发时系统会调用 render() 方法来输出
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->failed($this->message, $this->code,'error', $this->data);
        }

		return response()->json(['messages' => $this->message, 'code' => $this->code]);
    }
}