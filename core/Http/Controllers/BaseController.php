<?php

namespace Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** @var array $viewData */
    protected $viewData = [];

    /** @var string $title */
    protected $title = '';

    /**
     * @param $data
     */
    protected function setViewData($data)
    {
        $this->viewData = array_merge($this->getViewData(), (array)$data);
    }

    /**
     * @param null $item
     * @return array|mixed
     */
    protected function getViewData($item = null)
    {
        if ($item) return data_get($this->viewData, $item, null);

        return $this->viewData;
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     */
    protected function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param null $view
     * @param array $data
     * @param array $mergeData
     * @return Application|Factory|View
     */
    public function render($view = null, array $data = [], array $mergeData = [])
    {
        $area = getArea();
        $view = $area . '.' . ($view ?: (getControllerName() . '.' . getActionName()));
        $data = array_merge($data, $this->getViewData(), [
            'title' => $this->getTitle(),
        ]);

        return view($view, $data, $mergeData);
    }

    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return JsonResponse
     */
    public function renderJson(array $data = [], int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        return response()->json($data, $status, $headers, $options);
    }
}
