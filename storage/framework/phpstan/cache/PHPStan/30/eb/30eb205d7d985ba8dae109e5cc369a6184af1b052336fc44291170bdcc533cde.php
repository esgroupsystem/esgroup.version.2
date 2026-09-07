<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\EmployeeHistory.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\EmployeeHistory
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-1609d8f2133a64a4e1c3d829e522d6e2d1249fe2fff35f6a2519b6c90e0b325a',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\EmployeeHistory',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/EmployeeHistory.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\EmployeeHistory',
    'shortName' => 'EmployeeHistory',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $employee_id
 * @property string|null $ir_number
 * @property string|null $title
 * @property string|null $offense_id
 * @property array<string, mixed>|null $disciplinary_action
 * @property string|null $sda_amount
 * @property string|null $sda_terms
 * @property \\Carbon\\CarbonInterface|null $sda_start_date
 * @property \\Carbon\\CarbonInterface|null $sda_end_date
 * @property string|null $description
 * @property string|null $remarks
 * @property string|null $start_date
 * @property string|null $end_date
 * @property \\Carbon\\CarbonInterface|null $suspension_start_date
 * @property \\Carbon\\CarbonInterface|null $suspension_end_date
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 27,
    'endLine' => 66,
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
        'declaringClassName' => 'App\\Models\\EmployeeHistory',
        'implementingClassName' => 'App\\Models\\EmployeeHistory',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_id\', \'ir_number\', \'title\', \'offense_id\', \'disciplinary_action\', \'sda_amount\', \'sda_terms\', \'sda_start_date\', \'sda_end_date\', \'description\', \'remarks\', \'start_date\', \'end_date\', \'suspension_start_date\', \'suspension_end_date\']',
          'attributes' => 
          array (
            'startLine' => 29,
            'endLine' => 45,
            'startTokenPos' => 43,
            'startFilePos' => 885,
            'endTokenPos' => 90,
            'endFilePos' => 1246,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 29,
        'endLine' => 45,
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
        'declaringClassName' => 'App\\Models\\EmployeeHistory',
        'implementingClassName' => 'App\\Models\\EmployeeHistory',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'disciplinary_action\' => \'array\', \'sda_start_date\' => \'date\', \'sda_end_date\' => \'date\', \'suspension_start_date\' => \'date\', \'suspension_end_date\' => \'date\']',
          'attributes' => 
          array (
            'startLine' => 47,
            'endLine' => 53,
            'startTokenPos' => 99,
            'startFilePos' => 1273,
            'endTokenPos' => 136,
            'endFilePos' => 1475,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 47,
        'endLine' => 53,
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
        'startLine' => 56,
        'endLine' => 59,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeeHistory',
        'implementingClassName' => 'App\\Models\\EmployeeHistory',
        'currentClassName' => 'App\\Models\\EmployeeHistory',
        'aliasName' => NULL,
      ),
      'offense' => 
      array (
        'name' => 'offense',
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
        'docComment' => '/** @return BelongsTo<HrOffense, $this> */',
        'startLine' => 62,
        'endLine' => 65,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeeHistory',
        'implementingClassName' => 'App\\Models\\EmployeeHistory',
        'currentClassName' => 'App\\Models\\EmployeeHistory',
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