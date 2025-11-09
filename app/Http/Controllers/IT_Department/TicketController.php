<?php

namespace App\Http\Controllers\IT_Department;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        return view('it_department.ticket_job_order');
    }

    public function cctvindex()
    {
        return view('it_department.cctv_concern');
    }
}
