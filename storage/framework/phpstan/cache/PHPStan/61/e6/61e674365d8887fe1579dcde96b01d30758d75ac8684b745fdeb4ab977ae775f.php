<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/PaymentLog.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PaymentLog
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-10fbe4e56cee7271eb20cd1b9708f59442ebbb70c8f8f402a538d9e4c6dc5103',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PaymentLog',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/PaymentLog.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PaymentLog',
    'shortName' => 'PaymentLog',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $payroll_id
 * @property string|null $payroll_item_id
 * @property string|null $employee_id
 * @property string|null $payroll_employee_salary_id
 * @property string|null $biometric_employee_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string|null $log_type
 * @property string|null $source_type
 * @property string|null $source_id
 * @property string|null $source_name
 * @property string|null $deduction_schedule
 * @property string|null $cutoff_month
 * @property string|null $cutoff_year
 * @property string|null $cutoff_type
 * @property string|null $contribution_month
 * @property string|null $contribution_year
 * @property \\Carbon\\CarbonInterface|null $period_start
 * @property \\Carbon\\CarbonInterface|null $period_end
 * @property string|float|int $amount
 * @property string|float|int $employee_share
 * @property string|float|int $employer_share
 * @property string|float|int $balance_before
 * @property string|float|int $balance_after
 * @property string|null $payment_no
 * @property string|null $remaining_payments
 * @property string|null $reference
 * @property string|null $remarks
 * @property \\Carbon\\CarbonInterface|null $posted_at
 * @property string|null $created_by
 * @property array<string, mixed>|null $meta
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 43,
    'endLine' => 102,
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
        'declaringClassName' => 'App\\Models\\PaymentLog',
        'implementingClassName' => 'App\\Models\\PaymentLog',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'payroll_id\', \'payroll_item_id\', \'employee_id\', \'payroll_employee_salary_id\', \'biometric_employee_id\', \'employee_no\', \'employee_name\', \'log_type\', \'source_type\', \'source_id\', \'source_name\', \'deduction_schedule\', \'cutoff_month\', \'cutoff_year\', \'cutoff_type\', \'contribution_month\', \'contribution_year\', \'period_start\', \'period_end\', \'amount\', \'employee_share\', \'employer_share\', \'balance_before\', \'balance_after\', \'payment_no\', \'remaining_payments\', \'reference\', \'remarks\', \'posted_at\', \'created_by\', \'meta\']',
          'attributes' => 
          array (
            'startLine' => 45,
            'endLine' => 77,
            'startTokenPos' => 43,
            'startFilePos' => 1528,
            'endTokenPos' => 138,
            'endFilePos' => 2289,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 45,
        'endLine' => 77,
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
        'declaringClassName' => 'App\\Models\\PaymentLog',
        'implementingClassName' => 'App\\Models\\PaymentLog',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'period_start\' => \'date\', \'period_end\' => \'date\', \'posted_at\' => \'datetime\', \'amount\' => \'decimal:2\', \'employee_share\' => \'decimal:2\', \'employer_share\' => \'decimal:2\', \'balance_before\' => \'decimal:2\', \'balance_after\' => \'decimal:2\', \'meta\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 79,
            'endLine' => 89,
            'startTokenPos' => 147,
            'startFilePos' => 2316,
            'endTokenPos' => 212,
            'endFilePos' => 2646,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 79,
        'endLine' => 89,
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
        'startLine' => 92,
        'endLine' => 95,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PaymentLog',
        'implementingClassName' => 'App\\Models\\PaymentLog',
        'currentClassName' => 'App\\Models\\PaymentLog',
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
        'startLine' => 98,
        'endLine' => 101,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PaymentLog',
        'implementingClassName' => 'App\\Models\\PaymentLog',
        'currentClassName' => 'App\\Models\\PaymentLog',
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