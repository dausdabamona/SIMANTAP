<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AuditLogController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->ajax()) {
            // Fallback: show user auth events from session/log
            return DataTables::of(collect([]))->make(true);
        }

        // Read from Laravel log for audit trail (placeholder)
        return view('audit-log.index');
    }
}
