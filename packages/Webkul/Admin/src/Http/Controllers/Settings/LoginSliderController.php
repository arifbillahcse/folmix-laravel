<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Settings\LoginSlidersDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Theme\Repositories\LoginSliderRepository;

class LoginSliderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected LoginSliderRepository $loginSliderRepository) {}

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(LoginSlidersDataGrid::class)->process();
        }

        return view('admin::settings.login-sliders.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'title' => 'nullable|string',
            'link' => 'nullable|url',
            'sort_order' => 'nullable|numeric',
            'image' => 'array',
            'image.*' => 'image|extensions:jpeg,jpg,png,svg,webp',
        ]);

        $data = request()->only(['title', 'link', 'sort_order', 'image']);

        $data['status'] = request()->input('status') == 'on';

        $this->loginSliderRepository->create($data);

        return new JsonResponse([
            'message' => trans('admin::app.settings.login-sliders.index.create-success'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $slider = $this->loginSliderRepository->findOrFail($id);

        return new JsonResponse([
            'data' => $slider,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(): JsonResponse
    {
        $this->validate(request(), [
            'title' => 'nullable|string',
            'link' => 'nullable|url',
            'sort_order' => 'nullable|numeric',
            'image' => 'array',
            'image.*' => 'image|extensions:jpeg,jpg,png,svg,webp',
        ]);

        $data = request()->only(['title', 'link', 'sort_order', 'image']);

        $data['status'] = request()->input('status') == 'on';

        $this->loginSliderRepository->update($data, request()->id);

        return new JsonResponse([
            'message' => trans('admin::app.settings.login-sliders.index.update-success'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->loginSliderRepository->delete($id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.login-sliders.index.delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.login-sliders.index.delete-failed'),
            ], 500);
        }
    }

    /**
     * Mass update the status of the resources.
     */
    public function massUpdate(): JsonResponse
    {
        $indices = request()->input('indices', []);

        foreach ($indices as $id) {
            $this->loginSliderRepository->update([
                'status' => request()->input('value'),
            ], $id);
        }

        return new JsonResponse([
            'message' => trans('admin::app.settings.login-sliders.index.update-success'),
        ]);
    }

    /**
     * Mass delete the resources.
     */
    public function massDestroy(): JsonResponse
    {
        foreach (request()->input('indices', []) as $id) {
            $this->loginSliderRepository->delete($id);
        }

        return new JsonResponse([
            'message' => trans('admin::app.settings.login-sliders.index.delete-success'),
        ]);
    }
}
