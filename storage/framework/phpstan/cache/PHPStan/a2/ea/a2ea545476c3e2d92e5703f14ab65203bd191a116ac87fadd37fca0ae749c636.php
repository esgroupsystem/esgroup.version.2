<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\EmployeePlottingSchedule.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\EmployeePlottingSchedule
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-5fbc8fd9b1de96de26ef2b49cb96d78fb1dcf3da284e191d7db922a4d9901dfd',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\EmployeePlottingSchedule',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/EmployeePlottingSchedule.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\EmployeePlottingSchedule',
    'shortName' => 'EmployeePlottingSchedule',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int|null $employee_biometric_id
 * @property string|null $crosschex_id
 * @property string|null $biometric_employee_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property \\Carbon\\CarbonInterface|null $work_date
 * @property string|null $shift_name
 * @property WorkdayType|null $workday_type
 * @property int|null $paid_work_minutes
 * @property int|null $lunch_break_minutes
 * @property string|null $time_in
 * @property string|null $time_out
 * @property int|null $grace_minutes
 * @property string|null $status
 * @property string|null $day_off
 * @property array<string, mixed>|null $day_offs
 * @property string|null $remarks
 * @property-read mixed $formatted_time_in
 * @property-read mixed $formatted_time_out
 * @property-read mixed $is_flexible
 * @property-read mixed $is_permanent
 * @property-read mixed $payroll_display_name
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 38,
    'endLine' => 191,
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
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_biometric_id\', \'crosschex_id\', \'biometric_employee_id\', \'employee_no\', \'employee_name\', \'work_date\', \'shift_name\', \'workday_type\', \'paid_work_minutes\', \'lunch_break_minutes\', \'time_in\', \'time_out\', \'grace_minutes\', \'status\', \'day_off\', \'day_offs\', \'remarks\']',
          'attributes' => 
          array (
            'startLine' => 40,
            'endLine' => 58,
            'startTokenPos' => 63,
            'startFilePos' => 1258,
            'endTokenPos' => 116,
            'endFilePos' => 1669,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 40,
        'endLine' => 58,
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
        'startLine' => 60,
        'endLine' => 71,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
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
        'startLine' => 74,
        'endLine' => 77,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'scopePermanent' => 
      array (
        'name' => 'scopePermanent',
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
            'startLine' => 83,
            'endLine' => 83,
            'startColumn' => 36,
            'endColumn' => 49,
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
        'docComment' => '/**
 * @param  Builder<EmployeePlottingSchedule>  $query
 * @return Builder<EmployeePlottingSchedule>
 */',
        'startLine' => 83,
        'endLine' => 86,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
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
            'startLine' => 89,
            'endLine' => 89,
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
        'docComment' => '/** @return Builder<EmployeePlottingSchedule> */',
        'startLine' => 89,
        'endLine' => 95,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'getFormattedTimeInAttribute' => 
      array (
        'name' => 'getFormattedTimeInAttribute',
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
        'startLine' => 97,
        'endLine' => 100,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'getFormattedTimeOutAttribute' => 
      array (
        'name' => 'getFormattedTimeOutAttribute',
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
        'startLine' => 102,
        'endLine' => 105,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'getIsFlexibleAttribute' => 
      array (
        'name' => 'getIsFlexibleAttribute',
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
        'startLine' => 107,
        'endLine' => 110,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'getIsPermanentAttribute' => 
      array (
        'name' => 'getIsPermanentAttribute',
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
        'startLine' => 112,
        'endLine' => 115,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'resolvedWorkdayType' => 
      array (
        'name' => 'resolvedWorkdayType',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'App\\Enums\\WorkdayType',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 117,
        'endLine' => 121,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'paidWorkMinutes' => 
      array (
        'name' => 'paidWorkMinutes',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 123,
        'endLine' => 130,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'lunchBreakMinutes' => 
      array (
        'name' => 'lunchBreakMinutes',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 132,
        'endLine' => 135,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'requiredClockMinutes' => 
      array (
        'name' => 'requiredClockMinutes',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 137,
        'endLine' => 140,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'paidWorkHours' => 
      array (
        'name' => 'paidWorkHours',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'float',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 142,
        'endLine' => 145,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'resolvedDayOffs' => 
      array (
        'name' => 'resolvedDayOffs',
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
        'startLine' => 147,
        'endLine' => 159,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'isDayOffOn' => 
      array (
        'name' => 'isDayOffOn',
        'parameters' => 
        array (
          'date' => 
          array (
            'name' => 'date',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Carbon\\Carbon',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 161,
            'endLine' => 161,
            'startColumn' => 32,
            'endColumn' => 43,
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
        'startLine' => 161,
        'endLine' => 164,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'aliasName' => NULL,
      ),
      'normalizeDayOffs' => 
      array (
        'name' => 'normalizeDayOffs',
        'parameters' => 
        array (
          'dayOffs' => 
          array (
            'name' => 'dayOffs',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 166,
            'endLine' => 166,
            'startColumn' => 39,
            'endColumn' => 52,
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
        'startLine' => 166,
        'endLine' => 185,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
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
        'startLine' => 187,
        'endLine' => 190,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'implementingClassName' => 'App\\Models\\EmployeePlottingSchedule',
        'currentClassName' => 'App\\Models\\EmployeePlottingSchedule',
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