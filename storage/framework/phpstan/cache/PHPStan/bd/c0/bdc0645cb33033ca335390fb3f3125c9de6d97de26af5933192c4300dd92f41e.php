<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/Bus.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Bus
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-b9cc88cc845b47025a93daf08510276c470532527ac999566859649921515b48',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Bus',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/Bus.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Bus',
    'shortName' => 'Bus',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $bus_no
 * @property string|null $plate_no
 * @property string|null $company
 * @property string|null $garage
 * @property string $operational_status
 * @property string $sale_status
 * @property int|null $not_for_sale
 * @property int|null $mechanical_breakdown
 * @property int|null $accident_related
 * @property int|null $on_hold
 * @property int|null $for_sale
 * @property int|null $total_units
 * @property string|null $group_name
 * @property-read string $operational_status_label
 * @property-read string $sale_status_label
 * @property string|null $chassis_number
 * @property string|null $engine_number
 * @property string|null $case_number
 * @property string|null $monitoring_remarks
 * @property \\Carbon\\CarbonInterface|null $status_updated_at
 * @property-read mixed $operational_status_badge_class
 * @property-read mixed $sale_status_badge_class
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 36,
    'endLine' => 161,
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
      'STATUS_ACTIVE' => 
      array (
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'STATUS_ACTIVE',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'active\'',
          'attributes' => 
          array (
            'startLine' => 38,
            'endLine' => 38,
            'startTokenPos' => 50,
            'startFilePos' => 1156,
            'endTokenPos' => 50,
            'endFilePos' => 1163,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 38,
        'endLine' => 38,
        'startColumn' => 5,
        'endColumn' => 42,
      ),
      'STATUS_MECHANICAL_BREAKDOWN' => 
      array (
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'STATUS_MECHANICAL_BREAKDOWN',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'mechanical_breakdown\'',
          'attributes' => 
          array (
            'startLine' => 40,
            'endLine' => 40,
            'startTokenPos' => 61,
            'startFilePos' => 1214,
            'endTokenPos' => 61,
            'endFilePos' => 1235,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 40,
        'endLine' => 40,
        'startColumn' => 5,
        'endColumn' => 70,
      ),
      'STATUS_ACCIDENT_RELATED_BREAKDOWN' => 
      array (
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'STATUS_ACCIDENT_RELATED_BREAKDOWN',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'accident_related_breakdown\'',
          'attributes' => 
          array (
            'startLine' => 42,
            'endLine' => 42,
            'startTokenPos' => 72,
            'startFilePos' => 1292,
            'endTokenPos' => 72,
            'endFilePos' => 1319,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 42,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 82,
      ),
      'STATUS_ON_HOLD_PLATE_REGISTRATION' => 
      array (
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'STATUS_ON_HOLD_PLATE_REGISTRATION',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'on_hold_plate_registration\'',
          'attributes' => 
          array (
            'startLine' => 44,
            'endLine' => 44,
            'startTokenPos' => 83,
            'startFilePos' => 1376,
            'endTokenPos' => 83,
            'endFilePos' => 1403,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 44,
        'endLine' => 44,
        'startColumn' => 5,
        'endColumn' => 82,
      ),
      'STATUS_FOR_RENTAL_CHARTER' => 
      array (
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'STATUS_FOR_RENTAL_CHARTER',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'for_rental_charter\'',
          'attributes' => 
          array (
            'startLine' => 47,
            'endLine' => 47,
            'startTokenPos' => 96,
            'startFilePos' => 1463,
            'endTokenPos' => 96,
            'endFilePos' => 1482,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 47,
        'endLine' => 47,
        'startColumn' => 5,
        'endColumn' => 66,
      ),
      'STATUS_INACTIVE' => 
      array (
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'STATUS_INACTIVE',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'inactive\'',
          'attributes' => 
          array (
            'startLine' => 49,
            'endLine' => 49,
            'startTokenPos' => 107,
            'startFilePos' => 1521,
            'endTokenPos' => 107,
            'endFilePos' => 1530,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 49,
        'endLine' => 49,
        'startColumn' => 5,
        'endColumn' => 46,
      ),
      'SALE_NOT_FOR_SALE' => 
      array (
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'SALE_NOT_FOR_SALE',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'not_for_sale\'',
          'attributes' => 
          array (
            'startLine' => 51,
            'endLine' => 51,
            'startTokenPos' => 118,
            'startFilePos' => 1571,
            'endTokenPos' => 118,
            'endFilePos' => 1584,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 51,
        'endLine' => 51,
        'startColumn' => 5,
        'endColumn' => 52,
      ),
      'SALE_FOR_SALE' => 
      array (
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'SALE_FOR_SALE',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'for_sale\'',
          'attributes' => 
          array (
            'startLine' => 53,
            'endLine' => 53,
            'startTokenPos' => 129,
            'startFilePos' => 1621,
            'endTokenPos' => 129,
            'endFilePos' => 1630,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 53,
        'endLine' => 53,
        'startColumn' => 5,
        'endColumn' => 44,
      ),
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'bus_no\', \'plate_no\', \'company\', \'garage\', \'chassis_number\', \'engine_number\', \'case_number\', \'operational_status\', \'sale_status\', \'monitoring_remarks\', \'status_updated_at\']',
          'attributes' => 
          array (
            'startLine' => 55,
            'endLine' => 67,
            'startTokenPos' => 138,
            'startFilePos' => 1660,
            'endTokenPos' => 173,
            'endFilePos' => 1927,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 55,
        'endLine' => 67,
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
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'status_updated_at\' => \'datetime\']',
          'attributes' => 
          array (
            'startLine' => 69,
            'endLine' => 71,
            'startTokenPos' => 182,
            'startFilePos' => 1954,
            'endTokenPos' => 191,
            'endFilePos' => 2003,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 69,
        'endLine' => 71,
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
      'operationalStatusOptions' => 
      array (
        'name' => 'operationalStatusOptions',
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
        'startLine' => 73,
        'endLine' => 83,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'saleStatusOptions' => 
      array (
        'name' => 'saleStatusOptions',
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
        'startLine' => 85,
        'endLine' => 91,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'forSaleRecords' => 
      array (
        'name' => 'forSaleRecords',
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
        'docComment' => '/** @return HasMany<BusForSaleRecord, $this> */',
        'startLine' => 94,
        'endLine' => 97,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'forSaleRecord' => 
      array (
        'name' => 'forSaleRecord',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return HasOne<BusForSaleRecord, $this> */',
        'startLine' => 100,
        'endLine' => 103,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'currentForSaleRecord' => 
      array (
        'name' => 'currentForSaleRecord',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return HasOne<BusForSaleRecord, $this> */',
        'startLine' => 106,
        'endLine' => 109,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'getOperationalStatusLabelAttribute' => 
      array (
        'name' => 'getOperationalStatusLabelAttribute',
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
        'startLine' => 111,
        'endLine' => 114,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'getSaleStatusLabelAttribute' => 
      array (
        'name' => 'getSaleStatusLabelAttribute',
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
        'startLine' => 116,
        'endLine' => 119,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'getOperationalStatusBadgeClassAttribute' => 
      array (
        'name' => 'getOperationalStatusBadgeClassAttribute',
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
        'startLine' => 121,
        'endLine' => 132,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'getSaleStatusBadgeClassAttribute' => 
      array (
        'name' => 'getSaleStatusBadgeClassAttribute',
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
        'startLine' => 134,
        'endLine' => 140,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'jobOrderMaintenances' => 
      array (
        'name' => 'jobOrderMaintenances',
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
        'docComment' => '/** @return HasMany<JobOrderMaintenance, $this> */',
        'startLine' => 143,
        'endLine' => 146,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'latestJobOrderMaintenance' => 
      array (
        'name' => 'latestJobOrderMaintenance',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return HasOne<JobOrderMaintenance, $this> */',
        'startLine' => 149,
        'endLine' => 152,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
        'aliasName' => NULL,
      ),
      'latestJobOrderMaintenanceWithOdometer' => 
      array (
        'name' => 'latestJobOrderMaintenanceWithOdometer',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return HasOne<JobOrderMaintenance, $this> */',
        'startLine' => 155,
        'endLine' => 160,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Bus',
        'implementingClassName' => 'App\\Models\\Bus',
        'currentClassName' => 'App\\Models\\Bus',
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