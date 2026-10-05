<?php

declare(strict_types=1);

namespace Tests\Feature\HR;

use App\Mail\LeaveNoticeMail;
use App\Models\Claim;
use App\Models\ConductorLeave;
use App\Models\Department;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\EmployeeBiometric;
use App\Models\EmployeeLeave;
use App\Models\HrOffense;
use App\Models\Position;
use App\Models\User;
use App\Repositories\Contracts\HR\ClaimRepositoryInterface;
use App\Repositories\Contracts\HR\DepartmentRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeAttachmentRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeHistoryRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeLogRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeRepositoryInterface;
use App\Repositories\Contracts\HR\HrOffenseRepositoryInterface;
use App\Repositories\Contracts\HR\LeaveRepositoryInterface;
use App\Repositories\HR\ClaimRepository;
use App\Repositories\HR\DepartmentRepository;
use App\Repositories\HR\EmployeeAttachmentRepository;
use App\Repositories\HR\EmployeeHistoryRepository;
use App\Repositories\HR\EmployeeLogRepository;
use App\Repositories\HR\EmployeeRepository;
use App\Repositories\HR\HrOffenseRepository;
use App\Repositories\HR\LeaveRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Human Resources on the layered structure: bindings, removed routes, the leave notice
 * workflow and reminders for all three kinds, and the profile write actions not covered
 * by HrPagesTest.
 */
final class HrLayeredStructureTest extends TestCase
{
    use RefreshDatabase;

    private const LEAVE_ROUTES = [
        'employee' => 'employee-leave.employee',
        'driver' => 'driver-leave.driver',
        'conductor' => 'conductor-leave.conductor',
    ];

    private User $user;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Uploads and deletes must never touch the real storage folder.
        Storage::fake('local');

        $permissions = [];
        foreach (['employees', 'departments', 'violations', 'claims', 'employee-leave', 'driver-leave', 'conductor-leave'] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $ability) {
                $permissions[] = Permission::findOrCreate("{$module}.{$ability}", 'web')->name;
            }
        }
        $role = Role::findOrCreate('HR Tester', 'web');
        $role->syncPermissions($permissions);
        $this->user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false, 'full_name' => 'Helen HR']);
        $this->user->assignRole($role);

        $this->department = Department::query()->create(['name' => 'Operations']);
    }

    public function test_repository_interfaces_resolve_to_their_eloquent_classes(): void
    {
        $this->assertInstanceOf(EmployeeRepository::class, app(EmployeeRepositoryInterface::class));
        $this->assertInstanceOf(EmployeeHistoryRepository::class, app(EmployeeHistoryRepositoryInterface::class));
        $this->assertInstanceOf(EmployeeAttachmentRepository::class, app(EmployeeAttachmentRepositoryInterface::class));
        $this->assertInstanceOf(EmployeeLogRepository::class, app(EmployeeLogRepositoryInterface::class));
        $this->assertInstanceOf(DepartmentRepository::class, app(DepartmentRepositoryInterface::class));
        $this->assertInstanceOf(HrOffenseRepository::class, app(HrOffenseRepositoryInterface::class));
        $this->assertInstanceOf(ClaimRepository::class, app(ClaimRepositoryInterface::class));
        $this->assertInstanceOf(LeaveRepository::class, app(LeaveRepositoryInterface::class));
    }

    public function test_unused_and_broken_routes_are_removed(): void
    {
        // Two unused JSON position lists, and a history "edit" page whose method never existed (a 500).
        foreach (['employees.positions', 'employees.departments.positions', 'employees.staff.history.edit'] as $name) {
            $this->assertFalse(Route::has($name), "{$name} should not exist.");
        }
    }

    public function test_leave_notice_workflow_for_all_three_kinds(): void
    {
        foreach (['employee' => 'Clerk', 'driver' => 'Driver', 'conductor' => 'Conductor'] as $kind => $title) {
            $route = self::LEAVE_ROUTES[$kind];
            $employee = $this->employee(ucfirst($kind).' One', $title);
            $leave = $this->leaveFor($kind, $employee, now()->subDays(30), now()->subDays(25));

            // Notices need picture proof, and come in order.
            $this->client()->post(route("{$route}.action", $leave), ['action_type' => 'first'])->assertSessionHasErrors('proof_image');
            $this->client()->post(route("{$route}.action", $leave), ['action_type' => 'second', 'proof_image' => UploadedFile::fake()->image('p.jpg')])
                ->assertSessionHas('flash_notification', fn ($flash) => str_contains(json_encode($flash), 'Send the 1st Notice before the 2nd Notice.'));

            foreach (['first', 'second', 'terminate'] as $action) {
                $this->client()->post(route("{$route}.action", $leave), ['action_type' => $action, 'note' => "{$action} sent", 'proof_image' => UploadedFile::fake()->image("{$action}.jpg")])
                    ->assertRedirect(route("{$route}.index"));
            }

            $leave->refresh();
            $this->assertSame(['Terminated', 3, 'terminate sent'], [$leave->status, $leave->offense_level, $leave->last_action_note]);
            $this->assertSame('Terminated', $employee->refresh()->status, "{$kind}: the final notice terminates the employee.");
            Storage::disk('local')->assertExists($leave->final_notice_proof);
            $this->assertStringStartsWith("{$kind}-leave/notices/{$leave->id}/final/", $leave->final_notice_proof);

            $this->client()->get(route("{$route}.proof", [$leave->id, 'final']))
                ->assertOk()
                ->assertHeader('Content-Disposition', 'inline');

            // A closed leave refuses further actions.
            $this->client()->post(route("{$route}.action", $leave), ['action_type' => 'cancel'])
                ->assertSessionHas('flash_notification', fn ($flash) => str_contains(json_encode($flash), 'This leave record is already closed.'));
        }

        $this->get('/driver-leave/abc/proof/first')->assertNotFound();
    }

    public function test_leave_ready_cancel_and_moving_a_leave_to_another_employee(): void
    {
        $first = $this->employee('Dina Driver', 'Driver');
        $second = $this->employee('Dario Driver', 'Driver');

        $this->client()->post(route('driver-leave.driver.store'), [
            'employee_id' => $first->id, 'leave_type' => 'Vacation Leave', 'start_date' => now()->toDateString(), 'end_date' => now()->addDays(2)->toDateString(),
        ])->assertRedirect(route('driver-leave.driver.index'));
        $leave = DriverLeave::query()->sole();
        $this->assertSame([3, 'On Leave'], [$leave->days, $first->refresh()->status]);

        // Moving the leave returns the first driver to Active.
        $this->client()->put(route('driver-leave.driver.update', $leave), [
            'employee_id' => $second->id, 'leave_type' => 'Vacation Leave', 'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
        ])->assertRedirect(route('driver-leave.driver.index'));
        $this->assertSame([2, $second->id], [$leave->refresh()->days, (int) $leave->employee_id]);
        $this->assertSame(['Active', 'On Leave'], [$first->refresh()->status, $second->refresh()->status]);

        $this->client()->post(route('driver-leave.driver.action', $leave), ['action_type' => 'ready'])
            ->assertSessionHas('flash_notification', fn ($flash) => str_contains(json_encode($flash), 'Driver marked as Ready for Duty and returned to Active.'));
        $this->assertSame(['Completed', 'Active'], [$leave->refresh()->status, $second->refresh()->status]);
        $this->assertNotNull($leave->ready_for_duty_notified_at);

        $conductor = $this->employee('Connie Conductor', 'Conductor');
        $other = $this->leaveFor('conductor', $conductor, now(), now()->addDay());
        $this->client()->post(route('conductor-leave.conductor.action', $other), ['action_type' => 'cancel'])->assertRedirect();
        $this->assertSame(['Cancelled', 'Active'], [$other->refresh()->status, $conductor->refresh()->status]);

        // A leave id from another table is not found on this page.
        $this->client()->get(route('employee-leave.employee.edit', $leave->id + 100))->assertNotFound();
    }

    public function test_ready_for_duty_reminders_email_hr_once_per_step(): void
    {
        Mail::fake();
        User::factory()->create(['role' => 'HR Officer', 'email' => 'hr@example.test', 'account_status' => 'active']);

        $returning = $this->leaveFor('driver', $this->employee('Ready Driver', 'Driver'), now()->subDays(5), now()->subDay());
        $late = $this->leaveFor('conductor', $this->employee('Late Conductor', 'Conductor'), now()->subDays(20), now()->subDays(12));
        $onLeave = $this->leaveFor('employee', $this->employee('Still Away', 'Clerk'), now()->subDay(), now()->addDays(3));

        foreach (['driver', 'conductor', 'employee'] as $kind) {
            $this->artisan("leaves:{$kind}-ready-for-duty")->assertSuccessful();
        }

        $this->assertNotNull($returning->refresh()->ready_for_duty_notified_at);
        $this->assertSame('Ready for Duty email sent to HR', $returning->last_action_note);
        $this->assertNotNull($late->refresh()->second_notice_sent_at);
        $this->assertNull($onLeave->refresh()->ready_for_duty_notified_at);
        Mail::assertSent(LeaveNoticeMail::class, 2);
        Mail::assertSent(LeaveNoticeMail::class, fn (LeaveNoticeMail $mail): bool => $mail->noticeType === '2nd Notice' && $mail->category === 'Conductor');

        // Running again sends nothing new.
        $this->artisan('leaves:driver-ready-for-duty')->assertSuccessful();
        Mail::assertSent(LeaveNoticeMail::class, 2);
    }

    public function test_profile_files_attachments_and_history(): void
    {
        $employee = $this->employee('Paula Profile', 'Clerk');
        $offense = HrOffense::query()->create(['section' => 'SEC-1', 'offense_description' => 'Late', 'offense_type' => 'A', 'offense_gravity' => 'LIGHT']);
        $second = HrOffense::query()->create(['section' => 'SEC-2', 'offense_description' => 'Absent', 'offense_type' => 'B', 'offense_gravity' => 'GRAVE']);

        $this->client()->get(route('employees.staff.profile-picture', $employee))->assertNotFound();
        $this->client()->get(route('employees.staff.asset-file', [$employee, 'resume']))->assertNotFound();

        $this->client()->post(route('employees.assets.update', $employee), ['resume' => UploadedFile::fake()->create('resume.pdf', 20, 'application/pdf')])
            ->assertSessionHasNoErrors();
        $this->client()->get(route('employees.staff.asset-file', [$employee, 'resume']))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->client()->post(route('employees.staff.attachments.store', $employee), ['attachment' => UploadedFile::fake()->create('memo.pdf', 10, 'application/pdf')])
            ->assertRedirect(route('employees.staff.show', $employee->id));
        $attachment = $employee->attachments()->sole();
        $this->client()->get(route('employees.staff.attachments.download', [$employee->id, $attachment->id]))->assertOk()->assertDownload('memo.pdf');
        $this->client()->delete(route('employees.staff.attachments.destroy', [$employee->id, $attachment->id]))->assertRedirect();
        $this->assertSame(0, $employee->attachments()->count());
        Storage::disk('local')->assertMissing($attachment->file_path);

        $case = ['title' => 'Violations', 'ir_number' => 'IR-1', 'offense_id' => [$offense->id], 'disciplinary_action' => ['Final Warning']];
        $this->client()->post(route('employees.staff.history.store', $employee), $case)->assertSessionHasNoErrors();
        $history = $employee->histories()->sole();

        // Editing replaces the whole IR case.
        $this->client()->put(route('employees.staff.history.update', [$employee->id, $history->id]), [
            ...$case, 'ir_number' => 'IR-2', 'offense_id' => [$offense->id, $second->id], 'description' => ['Late twice', 'Absent once'],
        ])->assertSessionHas('flash_notification', fn ($flash) => str_contains(json_encode($flash), 'Violation history updated successfully!'));
        $this->assertSame(['IR-2', 'IR-2'], $employee->histories()->orderBy('id')->pluck('ir_number')->all());

        $this->client()->delete(route('employees.staff.history.destroy', [$employee->id, $employee->histories()->first()->id]))->assertRedirect();
        $this->assertSame(0, $employee->histories()->count(), 'Deleting one row removes the whole IR case.');
        $this->assertDatabaseHas('employee_logs', ['employee_id' => $employee->id, 'action' => 'removed_violation_history']);
    }

    public function test_biometric_link_permanent_id_check_pdf_and_delete(): void
    {
        $employee = $this->employee('Bea Biometric', 'Clerk');
        $employee->update(['employee_id_permanent' => '4711']);
        $other = $this->employee('Other Person', 'Clerk');
        $biometric = EmployeeBiometric::query()->create([
            'source_key' => 'main:4711', 'source_crosschex_account' => 'main', 'source_employee_no' => '4711',
            'source_employee_name' => 'Bea Biometric', 'display_name' => 'Bea Biometric', 'employment_status' => 'active', 'is_payroll_active' => true,
        ]);

        $this->client()->put(route('employees.biometric-link.update', $employee->id), ['employee_biometric_id' => $biometric->id])
            ->assertSessionHas('success', 'Employee linked to the biometric record.');
        $this->assertSame($biometric->id, (int) $employee->refresh()->employee_biometric_id);

        $this->client()->put(route('employees.biometric-link.update', $other->id), ['employee_biometric_id' => $biometric->id])
            ->assertSessionHasErrors(['employee_biometric_id' => 'That biometric record is already linked to another employee. Unlink it there first.']);

        $this->client()->put(route('employees.biometric-link.update', $employee->id), ['employee_biometric_id' => null])
            ->assertSessionHas('success', 'Biometric link removed.');
        $this->assertNull($employee->refresh()->employee_biometric_id);

        $this->client()->getJson(route('employees.staff.checkPermanentId', ['value' => '4711']))->assertExactJson(['exists' => true, 'message' => 'ID already exists in database.']);
        $this->client()->getJson(route('employees.staff.checkPermanentId', ['value' => '4711', 'ignore_id' => $employee->id]))->assertExactJson(['exists' => false, 'message' => 'ID is available.']);
        $this->client()->getJson(route('employees.staff.checkPermanentId', ['value' => '']))->assertExactJson(['exists' => false, 'message' => '']);

        $this->client()->get(route('employees.staff.print', $employee->id))->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $this->client()->delete(route('employees.staff.destroy', $other->id))->assertSessionHas('success', 'Employee deleted successfully.');
        $this->assertDatabaseMissing('employees', ['id' => $other->id]);
        $this->client()->delete(route('employees.staff.destroy', 999999))->assertSessionHas('error', 'Unable to delete employee.');
    }

    public function test_department_position_and_claim_deletes(): void
    {
        $position = Position::query()->create(['department_id' => $this->department->id, 'title' => 'Dispatcher']);

        $this->client()->delete(route('employees.positions.destroy', $position->id))->assertSessionHas('success', 'Position deleted successfully!');
        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
        $this->client()->delete(route('employees.departments.destroy', $this->department->id))->assertSessionHas('success', 'Department deleted successfully!');
        $this->assertDatabaseMissing('departments', ['id' => $this->department->id]);

        $claim = Claim::query()->create(['employee_id' => $this->employee('Clara Claim')->id, 'claim_type' => 'SSS', 'status' => 'Draft']);
        $this->client()->delete(route('claims.destroy', $claim))->assertRedirect(route('claims.index'))->assertSessionHas('success', 'Claim deleted successfully.');
        $this->assertDatabaseCount('claims', 0);

        // An unknown date field falls back to "date_filed".
        $this->client()->get(route('claims.index', ['date_field' => 'password']))
            ->assertInertia(fn ($page) => $page->where('filters.date_field', 'date_filed'));
    }

    private function employee(string $name, ?string $positionTitle = null): Employee
    {
        $position = $positionTitle === null ? null : Position::query()->firstOrCreate(['department_id' => $this->department->id, 'title' => $positionTitle]);

        return Employee::query()->create([
            'employee_id' => 'EMP-'.fake()->unique()->numerify('#####'),
            'full_name' => $name,
            'company' => 'Jell Transport',
            'garage' => 'Mirasol',
            'status' => 'Active',
            'department_id' => $position?->department_id,
            'position_id' => $position?->id,
        ]);
    }

    private function leaveFor(string $kind, Employee $employee, $start, $end): EmployeeLeave|DriverLeave|ConductorLeave
    {
        $model = ['employee' => EmployeeLeave::class, 'driver' => DriverLeave::class, 'conductor' => ConductorLeave::class][$kind];
        $employee->update(['status' => 'On Leave']);

        return $model::query()->create([
            'employee_id' => $employee->id, 'leave_type' => 'Medical Leave', 'start_date' => $start->toDateString(), 'end_date' => $end->toDateString(),
            'days' => 1, 'offense_level' => 0, 'status' => 'Active',
        ]);
    }

    private function client(): static
    {
        return $this->actingAs($this->user)
            ->withSession(['_token' => 'hr-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'hr-test');
    }
}
