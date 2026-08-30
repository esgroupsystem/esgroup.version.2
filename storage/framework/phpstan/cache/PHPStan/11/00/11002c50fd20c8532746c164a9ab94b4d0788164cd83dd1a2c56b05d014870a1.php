<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/BusForSaleRecord.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\BusForSaleRecord
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-f524fa7450d1bca9f395933ba7e7e5e7e699af54e947f46a7e8faabbcb4f1d7e',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\BusForSaleRecord',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/BusForSaleRecord.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\BusForSaleRecord',
    'shortName' => 'BusForSaleRecord',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $company
 * @property int|null $days_in_breakdown
 * @property int|null $total
 * @property-read string $status_label
 * @property-read string $status_badge_class
 * @property-read int $live_days_in_breakdown
 * @property string|null $company_name
 * @property int|float|string|null $total
 * @property string|null $bus_id
 * @property string|null $bus_no
 * @property string|null $plate_no
 * @property string|null $garage
 * @property string|null $status
 * @property string|null $storage_area
 * @property \\Carbon\\CarbonInterface|null $breakdown_start_date
 * @property \\Carbon\\CarbonInterface|null $breakdown_end_date
 * @property string|null $column_11
 * @property string|null $unit_location
 * @property string|null $progress
 * @property string|null $remarks
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 33,
    'endLine' => 116,
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
        'declaringClassName' => 'App\\Models\\BusForSaleRecord',
        'implementingClassName' => 'App\\Models\\BusForSaleRecord',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'bus_id\', \'bus_no\', \'plate_no\', \'company\', \'garage\', \'status\', \'storage_area\', \'breakdown_start_date\', \'breakdown_end_date\', \'column_11\', \'days_in_breakdown\', \'unit_location\', \'progress\', \'remarks\']',
          'attributes' => 
          array (
            'startLine' => 35,
            'endLine' => 50,
            'startTokenPos' => 48,
            'startFilePos' => 1035,
            'endTokenPos' => 92,
            'endFilePos' => 1352,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 35,
        'endLine' => 50,
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
        'declaringClassName' => 'App\\Models\\BusForSaleRecord',
        'implementingClassName' => 'App\\Models\\BusForSaleRecord',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'breakdown_start_date\' => \'date\', \'breakdown_end_date\' => \'date\', \'days_in_breakdown\' => \'integer\']',
          'attributes' => 
          array (
            'startLine' => 52,
            'endLine' => 56,
            'startTokenPos' => 101,
            'startFilePos' => 1379,
            'endTokenPos' => 124,
            'endFilePos' => 1509,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 52,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'appends' => 
      array (
        'declaringClassName' => 'App\\Models\\BusForSaleRecord',
        'implementingClassName' => 'App\\Models\\BusForSaleRecord',
        'name' => 'appends',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'status_label\', \'status_badge_class\', \'live_days_in_breakdown\']',
          'attributes' => 
          array (
            'startLine' => 58,
            'endLine' => 62,
            'startTokenPos' => 133,
            'startFilePos' => 1538,
            'endTokenPos' => 144,
            'endFilePos' => 1632,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 58,
        'endLine' => 62,
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
      'statusOptions' => 
      array (
        'name' => 'statusOptions',
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
        'startLine' => 64,
        'endLine' => 74,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BusForSaleRecord',
        'implementingClassName' => 'App\\Models\\BusForSaleRecord',
        'currentClassName' => 'App\\Models\\BusForSaleRecord',
        'aliasName' => NULL,
      ),
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
        'docComment' => '/** @return BelongsTo<Bus, $this> */',
        'startLine' => 77,
        'endLine' => 80,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BusForSaleRecord',
        'implementingClassName' => 'App\\Models\\BusForSaleRecord',
        'currentClassName' => 'App\\Models\\BusForSaleRecord',
        'aliasName' => NULL,
      ),
      'getStatusLabelAttribute' => 
      array (
        'name' => 'getStatusLabelAttribute',
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
        'startLine' => 82,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BusForSaleRecord',
        'implementingClassName' => 'App\\Models\\BusForSaleRecord',
        'currentClassName' => 'App\\Models\\BusForSaleRecord',
        'aliasName' => NULL,
      ),
      'getStatusBadgeClassAttribute' => 
      array (
        'name' => 'getStatusBadgeClassAttribute',
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
        'startLine' => 87,
        'endLine' => 96,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BusForSaleRecord',
        'implementingClassName' => 'App\\Models\\BusForSaleRecord',
        'currentClassName' => 'App\\Models\\BusForSaleRecord',
        'aliasName' => NULL,
      ),
      'getLiveDaysInBreakdownAttribute' => 
      array (
        'name' => 'getLiveDaysInBreakdownAttribute',
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
        'startLine' => 98,
        'endLine' => 115,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BusForSaleRecord',
        'implementingClassName' => 'App\\Models\\BusForSaleRecord',
        'currentClassName' => 'App\\Models\\BusForSaleRecord',
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