<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Biometrics\BiometricCompanyRepository;
use App\Repositories\Biometrics\BiometricsLogRepository;
use App\Repositories\Biometrics\EmployeeBiometricRepository;
use App\Repositories\Contracts\Biometrics\BiometricCompanyRepositoryInterface;
use App\Repositories\Contracts\Biometrics\BiometricsLogRepositoryInterface;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use App\Repositories\Contracts\Fleet\BusForSaleRecordRepositoryInterface;
use App\Repositories\Contracts\Fleet\BusRepositoryInterface;
use App\Repositories\Contracts\Fleet\DieselStockRepositoryInterface;
use App\Repositories\Contracts\Fleet\OdometerSubmissionRepositoryInterface;
use App\Repositories\Contracts\General\HrReportRepositoryInterface;
use App\Repositories\Contracts\HR\ClaimRepositoryInterface;
use App\Repositories\Contracts\HR\DepartmentRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeAttachmentRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeHistoryRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeLogRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeRepositoryInterface;
use App\Repositories\Contracts\HR\HrOffenseRepositoryInterface;
use App\Repositories\Contracts\HR\LeaveRepositoryInterface;
use App\Repositories\Contracts\IT\CctvConcernRepositoryInterface;
use App\Repositories\Contracts\IT\ItInventoryItemRepositoryInterface;
use App\Repositories\Contracts\IT\JobOrderRepositoryInterface;
use App\Repositories\Contracts\Maintenance\CategoryRepositoryInterface;
use App\Repositories\Contracts\Maintenance\JobOrderMaintenanceRepositoryInterface;
use App\Repositories\Contracts\Maintenance\LocationRepositoryInterface;
use App\Repositories\Contracts\Maintenance\PartsOutRepositoryInterface;
use App\Repositories\Contracts\Maintenance\ProductRepositoryInterface;
use App\Repositories\Contracts\Maintenance\ReceivingRepositoryInterface;
use App\Repositories\Contracts\Maintenance\StockRepositoryInterface;
use App\Repositories\Contracts\Maintenance\StockTransferRepositoryInterface;
use App\Repositories\Contracts\Payroll\AttendanceAdjustmentRepositoryInterface;
use App\Repositories\Contracts\Payroll\AttendanceSummaryRepositoryInterface;
use App\Repositories\Contracts\Payroll\BenefitRecordRepositoryInterface;
use App\Repositories\Contracts\Payroll\BenefitSettlementRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollAuditLogRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollRepositoryInterface;
use App\Repositories\Contracts\Scheduling\EmployeeSalaryRepositoryInterface;
use App\Repositories\Contracts\Scheduling\HolidayRepositoryInterface;
use App\Repositories\Contracts\Scheduling\PlottingScheduleRepositoryInterface;
use App\Repositories\Contracts\Security\RoleRepositoryInterface;
use App\Repositories\Contracts\Security\UserRepositoryInterface;
use App\Repositories\Fleet\BusDetailRepository;
use App\Repositories\Fleet\BusForSaleRecordRepository;
use App\Repositories\Fleet\BusRepository;
use App\Repositories\Fleet\DieselStockRepository;
use App\Repositories\Fleet\OdometerSubmissionRepository;
use App\Repositories\General\HrReportRepository;
use App\Repositories\HR\ClaimRepository;
use App\Repositories\HR\DepartmentRepository;
use App\Repositories\HR\EmployeeAttachmentRepository;
use App\Repositories\HR\EmployeeHistoryRepository;
use App\Repositories\HR\EmployeeLogRepository;
use App\Repositories\HR\EmployeeRepository;
use App\Repositories\HR\HrOffenseRepository;
use App\Repositories\HR\LeaveRepository;
use App\Repositories\IT\CctvConcernRepository;
use App\Repositories\IT\ItInventoryItemRepository;
use App\Repositories\IT\JobOrderRepository;
use App\Repositories\Maintenance\CategoryRepository;
use App\Repositories\Maintenance\JobOrderMaintenanceRepository;
use App\Repositories\Maintenance\LocationRepository;
use App\Repositories\Maintenance\PartsOutRepository;
use App\Repositories\Maintenance\ProductRepository;
use App\Repositories\Maintenance\ReceivingRepository;
use App\Repositories\Maintenance\StockRepository;
use App\Repositories\Maintenance\StockTransferRepository;
use App\Repositories\Payroll\AttendanceAdjustmentRepository;
use App\Repositories\Payroll\AttendanceSummaryRepository;
use App\Repositories\Payroll\BenefitRecordRepository;
use App\Repositories\Payroll\BenefitSettlementRepository;
use App\Repositories\Payroll\PayrollAuditLogRepository;
use App\Repositories\Payroll\PayrollRepository;
use App\Repositories\Scheduling\EmployeeSalaryRepository;
use App\Repositories\Scheduling\HolidayRepository;
use App\Repositories\Scheduling\PlottingScheduleRepository;
use App\Repositories\Security\RoleRepository;
use App\Repositories\Security\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every repository interface to its Eloquent class.
 * A module converted to the layered structure adds its pairs here.
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $singletons = [
        // Biometrics
        BiometricCompanyRepositoryInterface::class => BiometricCompanyRepository::class,
        BiometricsLogRepositoryInterface::class => BiometricsLogRepository::class,
        EmployeeBiometricRepositoryInterface::class => EmployeeBiometricRepository::class,

        // Fleet
        BusDetailRepositoryInterface::class => BusDetailRepository::class,
        BusForSaleRecordRepositoryInterface::class => BusForSaleRecordRepository::class,
        BusRepositoryInterface::class => BusRepository::class,
        DieselStockRepositoryInterface::class => DieselStockRepository::class,
        OdometerSubmissionRepositoryInterface::class => OdometerSubmissionRepository::class,

        // General (dashboards)
        HrReportRepositoryInterface::class => HrReportRepository::class,

        // Human Resources
        ClaimRepositoryInterface::class => ClaimRepository::class,
        DepartmentRepositoryInterface::class => DepartmentRepository::class,
        EmployeeAttachmentRepositoryInterface::class => EmployeeAttachmentRepository::class,
        EmployeeHistoryRepositoryInterface::class => EmployeeHistoryRepository::class,
        EmployeeLogRepositoryInterface::class => EmployeeLogRepository::class,
        EmployeeRepositoryInterface::class => EmployeeRepository::class,
        HrOffenseRepositoryInterface::class => HrOffenseRepository::class,
        LeaveRepositoryInterface::class => LeaveRepository::class,

        // IT Support
        CctvConcernRepositoryInterface::class => CctvConcernRepository::class,
        ItInventoryItemRepositoryInterface::class => ItInventoryItemRepository::class,
        JobOrderRepositoryInterface::class => JobOrderRepository::class,

        // Maintenance, Inventory & Products
        CategoryRepositoryInterface::class => CategoryRepository::class,
        JobOrderMaintenanceRepositoryInterface::class => JobOrderMaintenanceRepository::class,
        LocationRepositoryInterface::class => LocationRepository::class,
        PartsOutRepositoryInterface::class => PartsOutRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        ReceivingRepositoryInterface::class => ReceivingRepository::class,
        StockRepositoryInterface::class => StockRepository::class,
        StockTransferRepositoryInterface::class => StockTransferRepository::class,

        // Payroll
        AttendanceAdjustmentRepositoryInterface::class => AttendanceAdjustmentRepository::class,
        AttendanceSummaryRepositoryInterface::class => AttendanceSummaryRepository::class,
        BenefitRecordRepositoryInterface::class => BenefitRecordRepository::class,
        BenefitSettlementRepositoryInterface::class => BenefitSettlementRepository::class,
        PayrollAuditLogRepositoryInterface::class => PayrollAuditLogRepository::class,
        PayrollRepositoryInterface::class => PayrollRepository::class,

        // Scheduling & Rates
        EmployeeSalaryRepositoryInterface::class => EmployeeSalaryRepository::class,
        HolidayRepositoryInterface::class => HolidayRepository::class,
        PlottingScheduleRepositoryInterface::class => PlottingScheduleRepository::class,

        // Security
        RoleRepositoryInterface::class => RoleRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
    ];
}
