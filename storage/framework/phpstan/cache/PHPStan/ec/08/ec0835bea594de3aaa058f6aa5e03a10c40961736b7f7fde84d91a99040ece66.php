<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/PayrollReportLog.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PayrollReportLog
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-d88fe8ea110dcab2386aaca52c8d54e9f9c07bbf73ab7ab51a57e7752685ef42',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PayrollReportLog',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/PayrollReportLog.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PayrollReportLog',
    'shortName' => 'PayrollReportLog',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $payroll_id
 * @property string|null $payroll_item_id
 * @property string|null $employee_id
 * @property string|null $biometric_employee_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string|null $report_type
 * @property string|null $cutoff_month
 * @property string|null $cutoff_year
 * @property string|null $cutoff_type
 * @property string|null $contribution_month
 * @property string|null $contribution_year
 * @property \\Carbon\\CarbonInterface|null $period_start
 * @property \\Carbon\\CarbonInterface|null $period_end
 * @property string|float|int $basis_amount
 * @property string|float|int $computed_amount
 * @property string|null $status
 * @property string|null $remarks
 * @property \\Carbon\\CarbonInterface|null $generated_at
 * @property string|null $generated_by
 * @property array<string, mixed>|null $meta
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 33,
    'endLine' => 79,
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
        'declaringClassName' => 'App\\Models\\PayrollReportLog',
        'implementingClassName' => 'App\\Models\\PayrollReportLog',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'payroll_id\', \'payroll_item_id\', \'employee_id\', \'biometric_employee_id\', \'employee_no\', \'employee_name\', \'report_type\', \'cutoff_month\', \'cutoff_year\', \'cutoff_type\', \'contribution_month\', \'contribution_year\', \'period_start\', \'period_end\', \'basis_amount\', \'computed_amount\', \'status\', \'remarks\', \'generated_at\', \'generated_by\', \'meta\']',
          'attributes' => 
          array (
            'startLine' => 35,
            'endLine' => 57,
            'startTokenPos' => 43,
            'startFilePos' => 1117,
            'endTokenPos' => 108,
            'endFilePos' => 1626,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 35,
        'endLine' => 57,
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
        'declaringClassName' => 'App\\Models\\PayrollReportLog',
        'implementingClassName' => 'App\\Models\\PayrollReportLog',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'period_start\' => \'date\', \'period_end\' => \'date\', \'generated_at\' => \'datetime\', \'basis_amount\' => \'decimal:2\', \'computed_amount\' => \'decimal:2\', \'meta\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 59,
            'endLine' => 66,
            'startTokenPos' => 117,
            'startFilePos' => 1653,
            'endTokenPos' => 161,
            'endFilePos' => 1871,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 59,
        'endLine' => 66,
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
      'payroll' => 
      array (
        'name' => 'payroll',
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
        'startLine' => 69,
        'endLine' => 72,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollReportLog',
        'implementingClassName' => 'App\\Models\\PayrollReportLog',
        'currentClassName' => 'App\\Models\\PayrollReportLog',
        'aliasName' => NULL,
      ),
      'payrollItem' => 
      array (
        'name' => 'payrollItem',
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
        'startLine' => 75,
        'endLine' => 78,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollReportLog',
        'implementingClassName' => 'App\\Models\\PayrollReportLog',
        'currentClassName' => 'App\\Models\\PayrollReportLog',
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