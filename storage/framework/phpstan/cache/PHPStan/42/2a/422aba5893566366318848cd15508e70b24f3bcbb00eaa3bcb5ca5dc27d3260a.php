<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\PayrollAttendanceAdjustment.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PayrollAttendanceAdjustment
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-fdb9210fd4223443bf4cd9e56424d5373c1074961f69e3e859777ca090792346',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PayrollAttendanceAdjustment',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/PayrollAttendanceAdjustment.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PayrollAttendanceAdjustment',
    'shortName' => 'PayrollAttendanceAdjustment',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int|null $employee_biometric_id
 * @property int|null $employee_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string $adjustment_type
 * @property \\Carbon\\CarbonInterface|null $work_date
 * @property \\Carbon\\CarbonInterface|null $date_from
 * @property \\Carbon\\CarbonInterface|null $date_to
 * @property \\Carbon\\CarbonInterface|null $offset_source_date
 * @property \\Carbon\\CarbonInterface|null $payroll_effective_date
 * @property int|null $approved_minutes
 * @property bool $defer_to_next_payroll
 * @property bool $is_paid
 * @property bool $ignore_late
 * @property bool $ignore_undertime
 * @property string|null $status
 * @property string|null $adjusted_time_in
 * @property string|null $adjusted_time_out
 * @property string|null $offset_source_time_in
 * @property string|null $offset_source_time_out
 * @property-read string $type_label
 * @property-read string $status_label
 * @property-read string $period_label
 * @property-read string $adjusted_time_label
 * @property-read string $offset_proof_label
 * @property string|null $biometric_employee_id
 * @property string|null $crosschex_id
 * @property string|null $adjusted_day_type
 * @property array<string, mixed>|null $offset_source_logs
 * @property int|null $paid_payroll_id
 * @property int|null $paid_payroll_item_id
 * @property string|null $reason
 * @property string|null $remarks
 * @property string|null $approved_by
 * @property \\Carbon\\CarbonInterface|null $approved_at
 * @property string|null $rejected_by
 * @property \\Carbon\\CarbonInterface|null $rejected_at
 * @property string|null $rejection_reason
 * @property string|null $encoded_by
 * @property \\Carbon\\CarbonInterface|null $encoded_at
 * @property-read mixed $payroll_display_name
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 56,
    'endLine' => 487,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Model',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
      'TYPE_SICK_LEAVE' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_SICK_LEAVE',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'sick_leave\'',
          'attributes' => 
          array (
            'startLine' => 58,
            'endLine' => 58,
            'startTokenPos' => 55,
            'startFilePos' => 2117,
            'endTokenPos' => 55,
            'endFilePos' => 2128,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 58,
        'endLine' => 58,
        'startColumn' => 5,
        'endColumn' => 48,
      ),
      'TYPE_MEDICAL_LEAVE' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_MEDICAL_LEAVE',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'medical_leave\'',
          'attributes' => 
          array (
            'startLine' => 60,
            'endLine' => 60,
            'startTokenPos' => 66,
            'startFilePos' => 2170,
            'endTokenPos' => 66,
            'endFilePos' => 2184,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 60,
        'endLine' => 60,
        'startColumn' => 5,
        'endColumn' => 54,
      ),
      'TYPE_CHANGE_SCHEDULE' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_CHANGE_SCHEDULE',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'change_schedule\'',
          'attributes' => 
          array (
            'startLine' => 62,
            'endLine' => 62,
            'startTokenPos' => 77,
            'startFilePos' => 2228,
            'endTokenPos' => 77,
            'endFilePos' => 2244,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 62,
        'endLine' => 62,
        'startColumn' => 5,
        'endColumn' => 58,
      ),
      'TYPE_OFFSET' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_OFFSET',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'offset\'',
          'attributes' => 
          array (
            'startLine' => 64,
            'endLine' => 64,
            'startTokenPos' => 88,
            'startFilePos' => 2279,
            'endTokenPos' => 88,
            'endFilePos' => 2286,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 64,
        'endLine' => 64,
        'startColumn' => 5,
        'endColumn' => 40,
      ),
      'TYPE_OFFICIAL_BUSINESS' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_OFFICIAL_BUSINESS',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'official_business\'',
          'attributes' => 
          array (
            'startLine' => 66,
            'endLine' => 66,
            'startTokenPos' => 99,
            'startFilePos' => 2332,
            'endTokenPos' => 99,
            'endFilePos' => 2350,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 66,
        'endLine' => 66,
        'startColumn' => 5,
        'endColumn' => 62,
      ),
      'TYPE_HOLIDAY_WORK' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_HOLIDAY_WORK',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'holiday_work\'',
          'attributes' => 
          array (
            'startLine' => 68,
            'endLine' => 68,
            'startTokenPos' => 110,
            'startFilePos' => 2391,
            'endTokenPos' => 110,
            'endFilePos' => 2404,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 68,
        'endLine' => 68,
        'startColumn' => 5,
        'endColumn' => 52,
      ),
      'TYPE_OVERTIME' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_OVERTIME',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'overtime\'',
          'attributes' => 
          array (
            'startLine' => 70,
            'endLine' => 70,
            'startTokenPos' => 121,
            'startFilePos' => 2441,
            'endTokenPos' => 121,
            'endFilePos' => 2450,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 70,
        'endLine' => 70,
        'startColumn' => 5,
        'endColumn' => 44,
      ),
      'TYPE_TYPHOON_DISASTER' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_TYPHOON_DISASTER',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'typhoon_disaster\'',
          'attributes' => 
          array (
            'startLine' => 77,
            'endLine' => 77,
            'startTokenPos' => 134,
            'startFilePos' => 2727,
            'endTokenPos' => 134,
            'endFilePos' => 2744,
          ),
        ),
        'docComment' => '/**
 * Legacy generic type. New records must use one of the threshold-specific
 * variants below. It is still recognized as a 3-hour rule so old data can
 * be rebuilt safely before/after the data migration.
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 77,
        'endLine' => 77,
        'startColumn' => 5,
        'endColumn' => 60,
      ),
      'TYPE_TYPHOON_DISASTER_3H' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_TYPHOON_DISASTER_3H',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'typhoon_disaster_3h\'',
          'attributes' => 
          array (
            'startLine' => 79,
            'endLine' => 79,
            'startTokenPos' => 145,
            'startFilePos' => 2792,
            'endTokenPos' => 145,
            'endFilePos' => 2812,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 79,
        'endLine' => 79,
        'startColumn' => 5,
        'endColumn' => 66,
      ),
      'TYPE_TYPHOON_DISASTER_4H' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_TYPHOON_DISASTER_4H',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'typhoon_disaster_4h\'',
          'attributes' => 
          array (
            'startLine' => 81,
            'endLine' => 81,
            'startTokenPos' => 156,
            'startFilePos' => 2860,
            'endTokenPos' => 156,
            'endFilePos' => 2880,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 81,
        'endLine' => 81,
        'startColumn' => 5,
        'endColumn' => 66,
      ),
      'TYPE_TYPHOON_DISASTER_5H' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_TYPHOON_DISASTER_5H',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'typhoon_disaster_5h\'',
          'attributes' => 
          array (
            'startLine' => 83,
            'endLine' => 83,
            'startTokenPos' => 167,
            'startFilePos' => 2928,
            'endTokenPos' => 167,
            'endFilePos' => 2948,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 83,
        'endLine' => 83,
        'startColumn' => 5,
        'endColumn' => 66,
      ),
      'TYPE_TYPHOON_DISASTER_6H' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_TYPHOON_DISASTER_6H',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'typhoon_disaster_6h\'',
          'attributes' => 
          array (
            'startLine' => 85,
            'endLine' => 85,
            'startTokenPos' => 178,
            'startFilePos' => 2996,
            'endTokenPos' => 178,
            'endFilePos' => 3016,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 85,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 66,
      ),
      'TYPHOON_DISASTER_TYPES' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPHOON_DISASTER_TYPES',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[self::TYPE_TYPHOON_DISASTER, self::TYPE_TYPHOON_DISASTER_3H, self::TYPE_TYPHOON_DISASTER_4H, self::TYPE_TYPHOON_DISASTER_5H, self::TYPE_TYPHOON_DISASTER_6H]',
          'attributes' => 
          array (
            'startLine' => 87,
            'endLine' => 93,
            'startTokenPos' => 189,
            'startFilePos' => 3062,
            'endTokenPos' => 216,
            'endFilePos' => 3265,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 87,
        'endLine' => 93,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
      'STATUS_PENDING' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'STATUS_PENDING',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'pending\'',
          'attributes' => 
          array (
            'startLine' => 95,
            'endLine' => 95,
            'startTokenPos' => 227,
            'startFilePos' => 3303,
            'endTokenPos' => 227,
            'endFilePos' => 3311,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 95,
        'endLine' => 95,
        'startColumn' => 5,
        'endColumn' => 44,
      ),
      'STATUS_APPROVED' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'STATUS_APPROVED',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'approved\'',
          'attributes' => 
          array (
            'startLine' => 97,
            'endLine' => 97,
            'startTokenPos' => 238,
            'startFilePos' => 3350,
            'endTokenPos' => 238,
            'endFilePos' => 3359,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 97,
        'endLine' => 97,
        'startColumn' => 5,
        'endColumn' => 46,
      ),
      'STATUS_REJECTED' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'STATUS_REJECTED',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'rejected\'',
          'attributes' => 
          array (
            'startLine' => 99,
            'endLine' => 99,
            'startTokenPos' => 249,
            'startFilePos' => 3398,
            'endTokenPos' => 249,
            'endFilePos' => 3407,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 99,
        'endLine' => 99,
        'startColumn' => 5,
        'endColumn' => 46,
      ),
      'GLOBAL_DISASTER_BIOMETRIC_ID' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'GLOBAL_DISASTER_BIOMETRIC_ID',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'GLOBAL-DISASTER\'',
          'attributes' => 
          array (
            'startLine' => 101,
            'endLine' => 101,
            'startTokenPos' => 260,
            'startFilePos' => 3459,
            'endTokenPos' => 260,
            'endFilePos' => 3475,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 101,
        'endLine' => 101,
        'startColumn' => 5,
        'endColumn' => 66,
      ),
      'GLOBAL_DISASTER_EMPLOYEE_NAME' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'GLOBAL_DISASTER_EMPLOYEE_NAME',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'ALL EMPLOYEES\'',
          'attributes' => 
          array (
            'startLine' => 103,
            'endLine' => 103,
            'startTokenPos' => 271,
            'startFilePos' => 3528,
            'endTokenPos' => 271,
            'endFilePos' => 3542,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 103,
        'endLine' => 103,
        'startColumn' => 5,
        'endColumn' => 65,
      ),
      'TYPES' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPES',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[self::TYPE_SICK_LEAVE => \'Sick Leave\', self::TYPE_MEDICAL_LEAVE => \'Medical Leave\', self::TYPE_CHANGE_SCHEDULE => \'Change Schedule\', self::TYPE_OFFSET => \'Offset / Company Compensatory Leave\', self::TYPE_OFFICIAL_BUSINESS => \'Official Business\', self::TYPE_HOLIDAY_WORK => \'Holiday Work\', self::TYPE_OVERTIME => \'Overtime - Manager Approval Required\', self::TYPE_TYPHOON_DISASTER_3H => \'Typhoon / Disaster - All Employees - 3hrs\', self::TYPE_TYPHOON_DISASTER_4H => \'Typhoon / Disaster - All Employees - 4hrs\', self::TYPE_TYPHOON_DISASTER_5H => \'Typhoon / Disaster - All Employees - 5hrs\', self::TYPE_TYPHOON_DISASTER_6H => \'Typhoon / Disaster - All Employees - 6hrs\']',
          'attributes' => 
          array (
            'startLine' => 105,
            'endLine' => 117,
            'startTokenPos' => 282,
            'startFilePos' => 3571,
            'endTokenPos' => 383,
            'endFilePos' => 4333,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 105,
        'endLine' => 117,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
      'TYPE_RULES' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'TYPE_RULES',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[self::TYPE_SICK_LEAVE => [\'date_mode\' => \'range\', \'manual_time_mode\' => \'none\', \'default_paid\' => true, \'default_ignore_late\' => true, \'default_ignore_undertime\' => true, \'approval_required\' => false], self::TYPE_MEDICAL_LEAVE => [\'date_mode\' => \'range\', \'manual_time_mode\' => \'none\', \'default_paid\' => true, \'default_ignore_late\' => true, \'default_ignore_undertime\' => true, \'approval_required\' => false], self::TYPE_CHANGE_SCHEDULE => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'schedule\', \'default_paid\' => false, \'default_ignore_late\' => false, \'default_ignore_undertime\' => false, \'approval_required\' => false], self::TYPE_OFFSET => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'none\', \'default_paid\' => false, \'default_ignore_late\' => false, \'default_ignore_undertime\' => false, \'approval_required\' => true, \'uses_time_credit\' => true, \'defer_to_next_payroll\' => false, \'preserves_overtime_entitlement\' => true], self::TYPE_OFFICIAL_BUSINESS => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'actual\', \'default_paid\' => true, \'default_ignore_late\' => true, \'default_ignore_undertime\' => true, \'approval_required\' => false], self::TYPE_HOLIDAY_WORK => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'actual\', \'default_paid\' => false, \'default_ignore_late\' => true, \'default_ignore_undertime\' => true, \'approval_required\' => false], self::TYPE_OVERTIME => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'overtime\', \'default_paid\' => false, \'default_ignore_late\' => false, \'default_ignore_undertime\' => false, \'approval_required\' => true], self::TYPE_TYPHOON_DISASTER => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'none\', \'default_paid\' => true, \'default_ignore_late\' => true, \'default_ignore_undertime\' => true, \'approval_required\' => false], self::TYPE_TYPHOON_DISASTER_3H => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'none\', \'default_paid\' => true, \'default_ignore_late\' => true, \'default_ignore_undertime\' => true, \'approval_required\' => false], self::TYPE_TYPHOON_DISASTER_4H => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'none\', \'default_paid\' => true, \'default_ignore_late\' => true, \'default_ignore_undertime\' => true, \'approval_required\' => false], self::TYPE_TYPHOON_DISASTER_5H => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'none\', \'default_paid\' => true, \'default_ignore_late\' => true, \'default_ignore_undertime\' => true, \'approval_required\' => false], self::TYPE_TYPHOON_DISASTER_6H => [\'date_mode\' => \'single\', \'manual_time_mode\' => \'none\', \'default_paid\' => true, \'default_ignore_late\' => true, \'default_ignore_undertime\' => true, \'approval_required\' => false]]',
          'attributes' => 
          array (
            'startLine' => 124,
            'endLine' => 224,
            'startTokenPos' => 396,
            'startFilePos' => 4582,
            'endTokenPos' => 1055,
            'endFilePos' => 8327,
          ),
        ),
        'docComment' => '/**
 * Central definition of how each adjustment behaves.
 * Attendance and payroll services use these semantics instead of treating
 * every adjustment as a generic paid manual-time record.
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 124,
        'endLine' => 224,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_biometric_id\', \'biometric_employee_id\', \'employee_no\', \'employee_name\', \'crosschex_id\', \'work_date\', \'date_from\', \'date_to\', \'adjustment_type\', \'adjusted_time_in\', \'adjusted_time_out\', \'adjusted_day_type\', \'offset_source_date\', \'offset_source_time_in\', \'offset_source_time_out\', \'offset_source_logs\', \'approved_minutes\', \'defer_to_next_payroll\', \'payroll_effective_date\', \'paid_payroll_id\', \'paid_payroll_item_id\', \'is_paid\', \'ignore_late\', \'ignore_undertime\', \'reason\', \'remarks\', \'status\', \'approved_by\', \'approved_at\', \'rejected_by\', \'rejected_at\', \'rejection_reason\', \'encoded_by\', \'encoded_at\']',
          'attributes' => 
          array (
            'startLine' => 226,
            'endLine' => 261,
            'startTokenPos' => 1064,
            'startFilePos' => 8357,
            'endTokenPos' => 1168,
            'endFilePos' => 9245,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 226,
        'endLine' => 261,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      'casts' => 
      array (
        'name' => 'casts',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 263,
        'endLine' => 284,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'employeeBiometric' => 
      array (
        'name' => 'employeeBiometric',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return BelongsTo<EmployeeBiometric, $this> */',
        'startLine' => 287,
        'endLine' => 290,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'encoder' => 
      array (
        'name' => 'encoder',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return BelongsTo<User, $this> */',
        'startLine' => 293,
        'endLine' => 296,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'approver' => 
      array (
        'name' => 'approver',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return BelongsTo<User, $this> */',
        'startLine' => 299,
        'endLine' => 302,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'rejector' => 
      array (
        'name' => 'rejector',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return BelongsTo<User, $this> */',
        'startLine' => 305,
        'endLine' => 308,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'paidPayroll' => 
      array (
        'name' => 'paidPayroll',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return BelongsTo<Payroll, $this> */',
        'startLine' => 311,
        'endLine' => 314,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'paidPayrollItem' => 
      array (
        'name' => 'paidPayrollItem',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return BelongsTo<PayrollItem, $this> */',
        'startLine' => 317,
        'endLine' => 320,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'scopeApproved' => 
      array (
        'name' => 'scopeApproved',
        'parameters' => 
        array (
          'query' => 
          array (
            'name' => 'query',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Builder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 322,
            'endLine' => 322,
            'startColumn' => 35,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Builder',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 322,
        'endLine' => 328,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'scopePending' => 
      array (
        'name' => 'scopePending',
        'parameters' => 
        array (
          'query' => 
          array (
            'name' => 'query',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Builder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 330,
            'endLine' => 330,
            'startColumn' => 34,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Builder',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 330,
        'endLine' => 333,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'scopeForPayrollActiveEmployees' => 
      array (
        'name' => 'scopeForPayrollActiveEmployees',
        'parameters' => 
        array (
          'query' => 
          array (
            'name' => 'query',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Database\\Eloquent\\Builder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 335,
            'endLine' => 335,
            'startColumn' => 52,
            'endColumn' => 65,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Builder',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 335,
        'endLine' => 344,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'rulesFor' => 
      array (
        'name' => 'rulesFor',
        'parameters' => 
        array (
          'type' => 
          array (
            'name' => 'type',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 346,
            'endLine' => 346,
            'startColumn' => 37,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 346,
        'endLine' => 356,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'isTyphoonDisasterType' => 
      array (
        'name' => 'isTyphoonDisasterType',
        'parameters' => 
        array (
          'type' => 
          array (
            'name' => 'type',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 358,
            'endLine' => 358,
            'startColumn' => 50,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 358,
        'endLine' => 361,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'typhoonDisasterRequiredMinutes' => 
      array (
        'name' => 'typhoonDisasterRequiredMinutes',
        'parameters' => 
        array (
          'type' => 
          array (
            'name' => 'type',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 363,
            'endLine' => 363,
            'startColumn' => 59,
            'endColumn' => 71,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'int',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 363,
        'endLine' => 373,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'typhoonDisasterRequiredHours' => 
      array (
        'name' => 'typhoonDisasterRequiredHours',
        'parameters' => 
        array (
          'type' => 
          array (
            'name' => 'type',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 375,
            'endLine' => 375,
            'startColumn' => 57,
            'endColumn' => 69,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'int',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 375,
        'endLine' => 380,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'typeLabel' => 
      array (
        'name' => 'typeLabel',
        'parameters' => 
        array (
          'type' => 
          array (
            'name' => 'type',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 382,
            'endLine' => 382,
            'startColumn' => 38,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 382,
        'endLine' => 389,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'getTypeLabelAttribute' => 
      array (
        'name' => 'getTypeLabelAttribute',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 391,
        'endLine' => 394,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'getStatusLabelAttribute' => 
      array (
        'name' => 'getStatusLabelAttribute',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 396,
        'endLine' => 403,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'getPeriodLabelAttribute' => 
      array (
        'name' => 'getPeriodLabelAttribute',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 405,
        'endLine' => 416,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'getAdjustedTimeLabelAttribute' => 
      array (
        'name' => 'getAdjustedTimeLabelAttribute',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 418,
        'endLine' => 439,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'getOffsetProofLabelAttribute' => 
      array (
        'name' => 'getOffsetProofLabelAttribute',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 441,
        'endLine' => 454,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'isGlobalDisasterAdjustment' => 
      array (
        'name' => 'isGlobalDisasterAdjustment',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 456,
        'endLine' => 459,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'isDeferredOffset' => 
      array (
        'name' => 'isDeferredOffset',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Legacy compatibility only. New Offset records are company compensatory-
 * leave credits and are never deferred as a cash payment to a later payroll.
 * Separately approved overtime remains independently payable.
 */',
        'startLine' => 466,
        'endLine' => 470,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'isCompensatoryOffset' => 
      array (
        'name' => 'isCompensatoryOffset',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 472,
        'endLine' => 476,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'isApprovalRequired' => 
      array (
        'name' => 'isApprovalRequired',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 478,
        'endLine' => 481,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
      'getPayrollDisplayNameAttribute' => 
      array (
        'name' => 'getPayrollDisplayNameAttribute',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 483,
        'endLine' => 486,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'implementingClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'currentClassName' => 'App\\Models\\PayrollAttendanceAdjustment',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));