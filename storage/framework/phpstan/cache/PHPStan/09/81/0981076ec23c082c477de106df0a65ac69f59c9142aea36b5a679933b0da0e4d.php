<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/PayrollEmployee.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PayrollEmployee
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-47b057b377aadfed694eb3f6ae8b04e72e2a9b5bd146e880f1017fd099f75fec',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PayrollEmployee',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/PayrollEmployee.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PayrollEmployee',
    'shortName' => 'PayrollEmployee',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $employee_id
 * @property string|null $source
 * @property string|null $crosschex_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string|null $department
 * @property string|null $position
 * @property string|float|int $daily_rate
 * @property string|float|int $monthly_rate
 * @property string|float|int $hourly_rate
 * @property bool|null $is_active
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 23,
    'endLine' => 57,
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
        'declaringClassName' => 'App\\Models\\PayrollEmployee',
        'implementingClassName' => 'App\\Models\\PayrollEmployee',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_id\', \'source\', \'crosschex_id\', \'employee_no\', \'employee_name\', \'department\', \'position\', \'daily_rate\', \'monthly_rate\', \'hourly_rate\', \'is_active\']',
          'attributes' => 
          array (
            'startLine' => 25,
            'endLine' => 37,
            'startTokenPos' => 43,
            'startFilePos' => 644,
            'endTokenPos' => 78,
            'endFilePos' => 895,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 25,
        'endLine' => 37,
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
        'declaringClassName' => 'App\\Models\\PayrollEmployee',
        'implementingClassName' => 'App\\Models\\PayrollEmployee',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'daily_rate\' => \'decimal:2\', \'monthly_rate\' => \'decimal:2\', \'hourly_rate\' => \'decimal:2\', \'is_active\' => \'boolean\']',
          'attributes' => 
          array (
            'startLine' => 39,
            'endLine' => 44,
            'startTokenPos' => 87,
            'startFilePos' => 922,
            'endTokenPos' => 117,
            'endFilePos' => 1076,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 39,
        'endLine' => 44,
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
      'attendanceSummaries' => 
      array (
        'name' => 'attendanceSummaries',
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
        'docComment' => '/** @return HasMany<AttendanceDailySummary, $this> */',
        'startLine' => 47,
        'endLine' => 50,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployee',
        'implementingClassName' => 'App\\Models\\PayrollEmployee',
        'currentClassName' => 'App\\Models\\PayrollEmployee',
        'aliasName' => NULL,
      ),
      'payrollEntries' => 
      array (
        'name' => 'payrollEntries',
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
        'docComment' => '/** @return HasMany<PayrollEntry, $this> */',
        'startLine' => 53,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollEmployee',
        'implementingClassName' => 'App\\Models\\PayrollEmployee',
        'currentClassName' => 'App\\Models\\PayrollEmployee',
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