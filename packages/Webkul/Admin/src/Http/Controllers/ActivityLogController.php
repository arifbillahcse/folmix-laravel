<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\View\View;
use Webkul\Admin\DataGrids\ActivityLogDataGrid;

class ActivityLogController extends Controller
{
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
}
