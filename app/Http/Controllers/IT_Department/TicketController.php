<?php

namespace App\Http\Controllers\IT_Department;

use App\Helpers\Notifier;
use App\Http\Controllers\Controller;
use App\Mail\JobOrderCreatedMail;
use App\Models\BusDetail;
use App\Models\JobOrder;
use App\Models\JobOrderFile;
use App\Models\JobOrderLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $buses = BusDetail::orderBy('body_number')->get();

        return view('it_department.create_joborder', compact('buses'));
    }

    /*
    |--------------------------------------------------------------------------
    | Saving/Create Routes
    |--------------------------------------------------------------------------
    */

    public function storeJoborders(Request $request)
    {
        try {
            $validated = $request->validate([
                'garage' => ['nullable'],
                'bus_name' => ['nullable'],
                'body_number' => ['required', 'string', 'max:255'],
                'plate_number' => ['nullable', 'string', 'max:255'],

                'job_name' => ['nullable', 'string', 'max:255'],
                'job_type' => ['required', 'string', 'max:255'],
                'job_datestart' => ['required', 'string', 'max:255'],
                'job_time_start' => ['required', 'string', 'max:255'],
                'job_time_end' => ['required', 'string', 'max:255'],
                'job_sitNumber' => ['nullable', 'string', 'max:20'],
                'job_remarks' => ['nullable', 'string'],
                'job_status' => ['required', Rule::in(['Pending', 'Assigned', 'In Progress', 'Completed'])],
                'job_assign_person' => ['nullable', 'string', 'max:255'],
                'direction' => ['nullable', 'string', 'max:255'],
                'driver_name' => ['nullable', 'string', 'max:255'],
                'conductor_name' => ['nullable', 'string', 'max:255'],

                'files.*' => ['nullable', 'file', 'max:5120'],
            ]);

            $user = Auth::user();

            return DB::transaction(function () use ($validated, $request, $user) {

                $bus = BusDetail::where('body_number', $validated['body_number'])->first();

                if (! $bus) {
                    Log::warning('Bus not found', ['body_number' => $validated['body_number']]);
                    flash('Selected bus does not exist.')->error();

                    return back()->withInput();
                }

                $validated['garage'] = $bus->garage;
                $validated['bus_name'] = $bus->name;
                $validated['plate_number'] = $bus->plate_number;

                $job = JobOrder::create([
                    'bus_detail_id' => $bus->id,
                    'created_by' => optional($user)->id,
                    'job_name' => $validated['job_name'] ?? 'Job Order',
                    'job_type' => $validated['job_type'],
                    'job_datestart' => $validated['job_datestart'],
                    'job_time_start' => $validated['job_time_start'],
                    'job_time_end' => $validated['job_time_end'],
                    'job_sitNumber' => $validated['job_sitNumber'] ?? null,
                    'job_remarks' => $validated['job_remarks'] ?? null,
                    'job_status' => $validated['job_status'],
                    'job_assign_person' => $validated['job_assign_person'] ?? null,
                    'job_date_filled' => now(),
                    'job_creator' => optional($user)->name,
                    'driver_name' => $validated['driver_name'] ?? null,
                    'conductor_name' => $validated['conductor_name'] ?? null,
                    'direction' => $validated['direction'] ?? null,
                ]);

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $upload) {
                        $stored = $upload->store("joborders/{$job->id}", 'public');

                        JobOrderFile::create([
                            'job_id' => $job->id,
                            'file_name' => $upload->getClientOriginalName(),
                            'file_remarks' => null,
                            'file_notes' => null,
                            'file_path' => $stored,
                        ]);
                    }
                }

                JobOrderLog::create([
                    'joborder_id' => $job->id,
                    'action' => 'created',
                    'meta' => [
                        'job_type' => $job->job_type,
                        'status' => $job->job_status,
                        'bus' => $bus->body_number,
                    ],
                    'user_id' => optional($user)->id,
                ]);

                // ✅ Send notification email (reusable)
                Notifier::notifyRoles(
                    ['IT Head', 'IT Officer'],
                    new JobOrderCreatedMail($job)
                );

                flash("Job Order #{$job->id} created successfully!")->success();

                return redirect()->route('tickets.joborder.index');
            });

        } catch (ValidationException $e) {

            // Log validation errors
            Log::warning('Job Order Validation Failed', [
                'route' => request()->route()->getName(),
                'errors' => $e->validator->errors()->toArray(),
                'input' => request()->except(['files']),
            ]);

            flash('Validation failed. Please check the fields.')->error();

            return back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (\Exception $e) {

            // Log unexpected exception
            Log::error('Job Order Creation Error', [
                'route' => request()->route()->getName(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => request()->except(['files']),
            ]);

            flash('Something went wrong while creating the job order.')->error();

            return back()
                ->with('debug', app()->environment('local') ? $e->getMessage() : null)
                ->withInput();
        }
    }
}
