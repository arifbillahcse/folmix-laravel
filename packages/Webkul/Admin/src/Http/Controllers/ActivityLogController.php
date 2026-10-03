<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\ActivityLogDataGrid;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\ActivityLog\Repositories\ActivityLogRepository;

class ActivityLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ActivityLogRepository $activityLogRepository) {}

    /**
     * Display a listing of recorded admin/customer activity.
     *
     * @return View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ActivityLogDataGrid::class)->process();
        }

        return view('admin::activity-log.index');
    }

    /**
     * Delete a single log entry.
     *
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $this->activityLogRepository->delete($id);

        return new JsonResponse([
            'message' => trans('admin::app.activity-log.index.delete-success'),
        ]);
    }

    /**
     * Delete several log entries at once.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $this->activityLogRepository->massDestroy($massDestroyRequest->input('indices'));

        return new JsonResponse([
            'message' => trans('admin::app.activity-log.index.mass-delete-success'),
        ]);
    }
}
