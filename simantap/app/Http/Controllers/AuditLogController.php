<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __invoke()
    {
        return view('coming-soon', ['page' => 'Audit Log']);
    }
}
