<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\DieselStock.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\DieselStock
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-2add0af9be86ee50bc9119ad1d22351226d2cf31c5bf4ab81ab71f329baceb1d',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\DieselStock',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/DieselStock.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\DieselStock',
    'shortName' => 'DieselStock',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property \\Carbon\\CarbonInterface|null $date
 * @property string|null $type
 * @property string|float|int $liters
 * @property string|float|int $unit_cost
 * @property string|float|int $total_cost
 * @property string|null $bus_detail_id
 * @property string|null $odometer_submission_id
 * @property string|null $reference_no
 * @property string|null $remarks
 * @property string|null $encoded_by
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 22,
    'endLine' => 61,
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
        'declaringClassName' => 'App\\Models\\DieselStock',
        'implementingClassName' => 'App\\Models\\DieselStock',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'date\', \'type\', \'liters\', \'unit_cost\', \'total_cost\', \'bus_detail_id\', \'odometer_submission_id\', \'reference_no\', \'remarks\', \'encoded_by\']',
          'attributes' => 
          array (
            'startLine' => 24,
            'endLine' => 35,
            'startTokenPos' => 43,
            'startFilePos' => 618,
            'endTokenPos' => 75,
            'endFilePos' => 841,
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
        'declaringClassName' => 'App\\Models\\DieselStock',
        'implementingClassName' => 'App\\Models\\DieselStock',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'date\' => \'date\', \'liters\' => \'decimal:2\', \'unit_cost\' => \'decimal:2\', \'total_cost\' => \'decimal:2\']',
          'attributes' => 
          array (
            'startLine' => 37,
            'endLine' => 42,
            'startTokenPos' => 84,
            'startFilePos' => 868,
            'endTokenPos' => 114,
            'endFilePos' => 1006,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 37,
        'endLine' => 42,
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
      'bus' => 
      array (
        'name' => 'bus',
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
        'docComment' => '/** @return BelongsTo<BusDetail, $this> */',
        'startLine' => 45,
        'endLine' => 48,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\DieselStock',
        'implementingClassName' => 'App\\Models\\DieselStock',
        'currentClassName' => 'App\\Models\\DieselStock',
        'aliasName' => NULL,
      ),
      'odometerSubmission' => 
      array (
        'name' => 'odometerSubmission',
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
        'docComment' => '/** @return BelongsTo<OdometerSubmission, $this> */',
        'startLine' => 51,
        'endLine' => 54,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\DieselStock',
        'implementingClassName' => 'App\\Models\\DieselStock',
        'currentClassName' => 'App\\Models\\DieselStock',
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
        'startLine' => 57,
        'endLine' => 60,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\DieselStock',
        'implementingClassName' => 'App\\Models\\DieselStock',
        'currentClassName' => 'App\\Models\\DieselStock',
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