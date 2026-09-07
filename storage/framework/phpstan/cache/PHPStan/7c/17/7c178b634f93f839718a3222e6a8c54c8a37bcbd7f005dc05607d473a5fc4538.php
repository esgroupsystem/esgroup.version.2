<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\PayrollEmployeeSalary.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PayrollEmployeeSalary
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-73c45d5ef19829b482844ed4289bbf83665c6783f8426b65ab5b83077f28e89f',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PayrollEmployeeSalary',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/PayrollEmployeeSalary.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PayrollEmployeeSalary',
    'shortName' => 'PayrollEmployeeSalary',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property int|null $employee_biometric_id
 * @property int|null $employee_id
 * @property string $biometric_employee_id
 * @property string|null $employee_no
 * @property string $employee_name
 * @property string|null $crosschex_id
 * @property string $rate_type
 * @property string|float|int $basic_salary
 * @property string|float|int $allowance
 * @property string $allowance_release_schedule
 * @property string|float|int $sim_load_allowance
 * @property string $sim_load_release_schedule
 * @property string $sss_contribution_cutoff
 * @property string $pagibig_contribution_cutoff
 * @property string $philhealth_contribution_cutoff
 * @property string|float|int $ot_rate_per_hour
 * @property string|float|int $late_deduction_per_minute
 * @property string|float|int $undertime_deduction_per_minute
 * @property string|float|int $absent_deduction_per_day
 * @property string|float|int $sss_loan
 * @property string|float|int $pagibig_loan
 * @property string|float|int $vale
 * @property string|float|int $other_loans
 * @property-read array<string, mixed> $payroll_preview
 * @property string|float|int $sss_loan_total_amount
 * @property string|float|int $sss_loan_payment_amount
 * @property string $sss_loan_deduction_schedule
 * @property \\Carbon\\CarbonInterface|null $sss_loan_start_date
 * @property string|float|int $pagibig_loan_total_amount
 * @property string|float|int $pagibig_loan_payment_amount
 * @property string $pagibig_loan_deduction_schedule
 * @property \\Carbon\\CarbonInterface|null $pagibig_loan_start_date
 * @property string|float|int $philhealth_loan_total_amount
 * @property string|float|int $philhealth_loan_payment_amount
 * @property string $philhealth_loan_deduction_schedule
 * @property \\Carbon\\CarbonInterface|null $philhealth_loan_start_date
 * @property string|float|int $cash_advance_total_amount
 * @property string|float|int $cash_advance_payment_amount
 * @property string $cash_advance_deduction_schedule
 * @property \\Carbon\\CarbonInterface|null $cash_advance_start_date
 * @property string|float|int $other_loan_total_amount
 * @property string|float|int $other_loan_payment_amount
 * @property string $other_loan_deduction_schedule
 * @property \\Carbon\\CarbonInterface|null $other_loan_start_date
 * @property bool $is_active
 * @property-read string $payroll_display_name
 * @property-read EmployeeBiometric|null $employeeBiometric
 * @property-read Employee|null $employee
 * @property-read array<string, mixed> $payroll_preview
 * @property-read \\Illuminate\\Database\\Eloquent\\Collection<int, PayrollEmployeeSalaryOtherDeduction> $otherDeductions
 * @property string|null $remarks
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 68,
    'endLine' => 213,
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
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_biometric_id\', \'employee_id\', \'biometric_employee_id\', \'employee_no\', \'employee_name\', \'crosschex_id\', \'rate_type\', \'basic_salary\', \'allowance\', \'allowance_release_schedule\', \'sim_load_allowance\', \'sim_load_release_schedule\', \'sss_contribution_cutoff\', \'pagibig_contribution_cutoff\', \'philhealth_contribution_cutoff\', \'ot_rate_per_hour\', \'late_deduction_per_minute\', \'undertime_deduction_per_minute\', \'absent_deduction_per_day\', \'sss_loan\', \'pagibig_loan\', \'vale\', \'other_loans\', \'sss_loan_total_amount\', \'sss_loan_payment_amount\', \'sss_loan_deduction_schedule\', \'sss_loan_start_date\', \'pagibig_loan_total_amount\', \'pagibig_loan_payment_amount\', \'pagibig_loan_deduction_schedule\', \'pagibig_loan_start_date\', \'philhealth_loan_total_amount\', \'philhealth_loan_payment_amount\', \'philhealth_loan_deduction_schedule\', \'philhealth_loan_start_date\', \'cash_advance_total_amount\', \'cash_advance_payment_amount\', \'cash_advance_deduction_schedule\', \'cash_advance_start_date\', \'other_loan_total_amount\', \'other_loan_payment_amount\', \'other_loan_deduction_schedule\', \'other_loan_start_date\', \'is_active\', \'remarks\']',
          'attributes' => 
          array (
            'startLine' => 70,
            'endLine' => 126,
            'startTokenPos' => 58,
            'startFilePos' => 3029,
            'endTokenPos' => 195,
            'endFilePos' => 4517,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 70,
        'endLine' => 126,
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
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_biometric_id\' => \'integer\', \'employee_id\' => \'integer\', \'basic_salary\' => \'decimal:2\', \'allowance\' => \'decimal:2\', \'sim_load_allowance\' => \'decimal:2\', \'ot_rate_per_hour\' => \'decimal:2\', \'late_deduction_per_minute\' => \'decimal:4\', \'undertime_deduction_per_minute\' => \'decimal:4\', \'absent_deduction_per_day\' => \'decimal:2\', \'sss_loan\' => \'decimal:2\', \'pagibig_loan\' => \'decimal:2\', \'vale\' => \'decimal:2\', \'other_loans\' => \'decimal:2\', \'sss_loan_total_amount\' => \'decimal:2\', \'sss_loan_payment_amount\' => \'decimal:2\', \'sss_loan_start_date\' => \'date\', \'pagibig_loan_total_amount\' => \'decimal:2\', \'pagibig_loan_payment_amount\' => \'decimal:2\', \'pagibig_loan_start_date\' => \'date\', \'philhealth_loan_total_amount\' => \'decimal:2\', \'philhealth_loan_payment_amount\' => \'decimal:2\', \'philhealth_loan_start_date\' => \'date\', \'cash_advance_total_amount\' => \'decimal:2\', \'cash_advance_payment_amount\' => \'decimal:2\', \'cash_advance_start_date\' => \'date\', \'other_loan_total_amount\' => \'decimal:2\', \'other_loan_payment_amount\' => \'decimal:2\', \'other_loan_start_date\' => \'date\', \'is_active\' => \'boolean\']',
          'attributes' => 
          array (
            'startLine' => 128,
            'endLine' => 167,
            'startTokenPos' => 204,
            'startFilePos' => 4544,
            'endTokenPos' => 409,
            'endFilePos' => 5887,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 128,
        'endLine' => 167,
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
        'startLine' => 170,
        'endLine' => 173,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'currentClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'aliasName' => NULL,
      ),
      'employee' => 
      array (
        'name' => 'employee',
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
        'docComment' => '/** @return BelongsTo<Employee, $this> */',
        'startLine' => 176,
        'endLine' => 179,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'currentClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'aliasName' => NULL,
      ),
      'otherDeductions' => 
      array (
        'name' => 'otherDeductions',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return HasMany<PayrollEmployeeSalaryOtherDeduction, $this> */',
        'startLine' => 182,
        'endLine' => 186,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'currentClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'aliasName' => NULL,
      ),
      'activeOtherDeductions' => 
      array (
        'name' => 'activeOtherDeductions',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasMany',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return HasMany<PayrollEmployeeSalaryOtherDeduction, $this> */',
        'startLine' => 189,
        'endLine' => 194,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'currentClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'aliasName' => NULL,
      ),
      'scopeActive' => 
      array (
        'name' => 'scopeActive',
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
            'startLine' => 196,
            'endLine' => 196,
            'startColumn' => 33,
            'endColumn' => 46,
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
        'startLine' => 196,
        'endLine' => 199,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'currentClassName' => 'App\\Models\\PayrollEmployeeSalary',
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
            'startLine' => 201,
            'endLine' => 201,
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
        'startLine' => 201,
        'endLine' => 207,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'currentClassName' => 'App\\Models\\PayrollEmployeeSalary',
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
        'startLine' => 209,
        'endLine' => 212,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalary',
        'currentClassName' => 'App\\Models\\PayrollEmployeeSalary',
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