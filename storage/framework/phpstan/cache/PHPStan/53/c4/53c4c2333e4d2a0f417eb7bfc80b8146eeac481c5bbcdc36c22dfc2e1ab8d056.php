<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\PayrollEntry.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PayrollEntry
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-c6c3d961a521dc925343f790026467ab3db97c991e62f081231d26ef068135fa',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PayrollEntry',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/PayrollEntry.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PayrollEntry',
    'shortName' => 'PayrollEntry',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $payroll_period_id
 * @property string|null $payroll_employee_id
 * @property string|null $days_worked
 * @property string|null $worked_minutes
 * @property string|null $late_minutes
 * @property string|null $undertime_minutes
 * @property string|null $overtime_minutes
 * @property string|float|int $basic_pay
 * @property string|float|int $overtime_pay
 * @property string|float|int $allowances
 * @property string|float|int $gross_pay
 * @property string|float|int $sss
 * @property string|float|int $philhealth
 * @property string|float|int $pagibig
 * @property string|float|int $withholding_tax
 * @property string|float|int $other_deductions
 * @property string|float|int $net_pay
 * @property string|null $status
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 31,
    'endLine' => 84,
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
        'declaringClassName' => 'App\\Models\\PayrollEntry',
        'implementingClassName' => 'App\\Models\\PayrollEntry',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'payroll_period_id\', \'payroll_employee_id\', \'days_worked\', \'worked_minutes\', \'late_minutes\', \'undertime_minutes\', \'overtime_minutes\', \'basic_pay\', \'overtime_pay\', \'allowances\', \'gross_pay\', \'sss\', \'philhealth\', \'pagibig\', \'withholding_tax\', \'other_deductions\', \'net_pay\', \'status\']',
          'attributes' => 
          array (
            'startLine' => 33,
            'endLine' => 52,
            'startTokenPos' => 48,
            'startFilePos' => 1018,
            'endTokenPos' => 104,
            'endFilePos' => 1450,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 33,
        'endLine' => 52,
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
        'declaringClassName' => 'App\\Models\\PayrollEntry',
        'implementingClassName' => 'App\\Models\\PayrollEntry',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'basic_pay\' => \'decimal:2\', \'overtime_pay\' => \'decimal:2\', \'allowances\' => \'decimal:2\', \'gross_pay\' => \'decimal:2\', \'sss\' => \'decimal:2\', \'philhealth\' => \'decimal:2\', \'pagibig\' => \'decimal:2\', \'withholding_tax\' => \'decimal:2\', \'other_deductions\' => \'decimal:2\', \'net_pay\' => \'decimal:2\']',
          'attributes' => 
          array (
            'startLine' => 54,
            'endLine' => 65,
            'startTokenPos' => 113,
            'startFilePos' => 1477,
            'endTokenPos' => 185,
            'endFilePos' => 1851,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 54,
        'endLine' => 65,
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
        'startLine' => 68,
        'endLine' => 71,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEntry',
        'implementingClassName' => 'App\\Models\\PayrollEntry',
        'currentClassName' => 'App\\Models\\PayrollEntry',
        'aliasName' => NULL,
      ),
      'payrollPeriod' => 
      array (
        'name' => 'payrollPeriod',
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
        'docComment' => '/** @return BelongsTo<PayrollPeriod, $this> */',
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
        'declaringClassName' => 'App\\Models\\PayrollEntry',
        'implementingClassName' => 'App\\Models\\PayrollEntry',
        'currentClassName' => 'App\\Models\\PayrollEntry',
        'aliasName' => NULL,
      ),
      'adjustments' => 
      array (
        'name' => 'adjustments',
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
        'docComment' => '/** @return HasMany<PayrollAdjustment, $this> */',
        'startLine' => 80,
        'endLine' => 83,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEntry',
        'implementingClassName' => 'App\\Models\\PayrollEntry',
        'currentClassName' => 'App\\Models\\PayrollEntry',
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