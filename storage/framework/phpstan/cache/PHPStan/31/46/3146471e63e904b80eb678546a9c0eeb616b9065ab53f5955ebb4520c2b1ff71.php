<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\AttendanceDailySummary.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\AttendanceDailySummary
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-14e0f2ed815e2943f132bf9052e2e2cda3e87bcc4ace75cc2813c18fc7bddd92',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\AttendanceDailySummary',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/AttendanceDailySummary.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\AttendanceDailySummary',
    'shortName' => 'AttendanceDailySummary',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $payroll_employee_id
 * @property \\Carbon\\CarbonInterface|null $work_date
 * @property \\Carbon\\CarbonInterface|null $time_in
 * @property \\Carbon\\CarbonInterface|null $time_out
 * @property string|null $worked_minutes
 * @property string|null $late_minutes
 * @property string|null $undertime_minutes
 * @property string|null $overtime_minutes
 * @property string|null $status
 * @property string|null $remarks
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 22,
    'endLine' => 48,
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
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\AttendanceDailySummary',
        'implementingClassName' => 'App\\Models\\AttendanceDailySummary',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'payroll_employee_id\', \'work_date\', \'time_in\', \'time_out\', \'worked_minutes\', \'late_minutes\', \'undertime_minutes\', \'overtime_minutes\', \'status\', \'remarks\']',
          'attributes' => 
          array (
            'startLine' => 24,
            'endLine' => 35,
            'startTokenPos' => 43,
            'startFilePos' => 666,
            'endTokenPos' => 75,
            'endFilePos' => 907,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 24,
        'endLine' => 35,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'casts' => 
      array (
        'declaringClassName' => 'App\\Models\\AttendanceDailySummary',
        'implementingClassName' => 'App\\Models\\AttendanceDailySummary',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'work_date\' => \'date\', \'time_in\' => \'datetime\', \'time_out\' => \'datetime\']',
          'attributes' => 
          array (
            'startLine' => 37,
            'endLine' => 41,
            'startTokenPos' => 84,
            'startFilePos' => 934,
            'endTokenPos' => 107,
            'endFilePos' => 1038,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 37,
        'endLine' => 41,
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
      'payrollEmployee' => 
      array (
        'name' => 'payrollEmployee',
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
        'docComment' => '/** @return BelongsTo<PayrollEmployee, $this> */',
        'startLine' => 44,
        'endLine' => 47,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\AttendanceDailySummary',
        'implementingClassName' => 'App\\Models\\AttendanceDailySummary',
        'currentClassName' => 'App\\Models\\AttendanceDailySummary',
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