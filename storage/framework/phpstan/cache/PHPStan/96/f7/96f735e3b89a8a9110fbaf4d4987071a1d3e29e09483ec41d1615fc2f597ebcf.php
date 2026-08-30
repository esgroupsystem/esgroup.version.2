<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/PayrollEmployeeSalaryOtherDeduction.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PayrollEmployeeSalaryOtherDeduction
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-417530f86980727f55265a89272779cc3e868522b4a6ff7f001fad1790d4fdbe',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PayrollEmployeeSalaryOtherDeduction',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/PayrollEmployeeSalaryOtherDeduction.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PayrollEmployeeSalaryOtherDeduction',
    'shortName' => 'PayrollEmployeeSalaryOtherDeduction',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $payroll_employee_salary_id
 * @property string|null $name
 * @property string|float|int $total_amount
 * @property string|float|int $payment_amount
 * @property string|null $deduction_schedule
 * @property \\Carbon\\CarbonInterface|null $start_date
 * @property string|null $remarks
 * @property bool|null $is_active
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 20,
    'endLine' => 45,
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
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalaryOtherDeduction',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalaryOtherDeduction',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'payroll_employee_salary_id\', \'name\', \'total_amount\', \'payment_amount\', \'deduction_schedule\', \'start_date\', \'remarks\', \'is_active\']',
          'attributes' => 
          array (
            'startLine' => 22,
            'endLine' => 31,
            'startTokenPos' => 43,
            'startFilePos' => 584,
            'endTokenPos' => 69,
            'endFilePos' => 786,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 22,
        'endLine' => 31,
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
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalaryOtherDeduction',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalaryOtherDeduction',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'total_amount\' => \'decimal:2\', \'payment_amount\' => \'decimal:2\', \'start_date\' => \'date\', \'is_active\' => \'boolean\']',
          'attributes' => 
          array (
            'startLine' => 33,
            'endLine' => 38,
            'startTokenPos' => 78,
            'startFilePos' => 813,
            'endTokenPos' => 108,
            'endFilePos' => 965,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 33,
        'endLine' => 38,
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
      'salary' => 
      array (
        'name' => 'salary',
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
        'docComment' => '/** @return BelongsTo<PayrollEmployeeSalary, $this> */',
        'startLine' => 41,
        'endLine' => 44,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployeeSalaryOtherDeduction',
        'implementingClassName' => 'App\\Models\\PayrollEmployeeSalaryOtherDeduction',
        'currentClassName' => 'App\\Models\\PayrollEmployeeSalaryOtherDeduction',
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