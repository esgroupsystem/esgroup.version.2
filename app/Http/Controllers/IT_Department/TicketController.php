<?php

namespace App\Http\Controllers\IT_Department;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index Routes
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return view('it_department.ticket_job_order');
    }

    public function cctvindex()
    {
        return view('it_department.cctv_concern');
    }

    public function createjobordersIndex()
    {
        return view('it_department.create_joborder');
    }

    /*
    |--------------------------------------------------------------------------
    | Saving/Create Routes
    |--------------------------------------------------------------------------
    */





}
