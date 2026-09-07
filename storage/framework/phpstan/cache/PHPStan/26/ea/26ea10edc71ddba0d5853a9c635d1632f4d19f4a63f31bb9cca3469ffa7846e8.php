<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\JobOrderMaintenanceStatusPeriod.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\JobOrderMaintenanceStatusPeriod
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-4ed5de576bdd2fdd242cf5d4b06c37c4ad9a7afdeca343be5f47d9798bb9e0a4',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/JobOrderMaintenanceStatusPeriod.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
    'shortName' => 'JobOrderMaintenanceStatusPeriod',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property \\App\\Enums\\JobOrderStatus $status
 * @property \\Carbon\\CarbonInterface $started_at
 * @property \\Carbon\\CarbonInterface|null $ended_at
 * @property-read int $duration_minutes
 * @property-read string $duration_label
 * @property string|null $job_order_maintenance_id
 * @property int|null $changed_by
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 21,
    'endLine' => 65,
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
        'declaringClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'implementingClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'job_order_maintenance_id\', \'status\', \'started_at\', \'ended_at\', \'changed_by\']',
          'attributes' => 
          array (
            'startLine' => 23,
            'endLine' => 29,
            'startTokenPos' => 48,
            'startFilePos' => 584,
            'endTokenPos' => 65,
            'endFilePos' => 708,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 23,
        'endLine' => 29,
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
      'casts' => 
      array (
        'name' => 'casts',
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
        'startLine' => 31,
        'endLine' => 39,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'implementingClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'currentClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'aliasName' => NULL,
      ),
      'jobOrder' => 
      array (
        'name' => 'jobOrder',
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
        'docComment' => '/** @return BelongsTo<JobOrderMaintenance, $this> */',
        'startLine' => 42,
        'endLine' => 45,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'implementingClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'currentClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'aliasName' => NULL,
      ),
      'changedBy' => 
      array (
        'name' => 'changedBy',
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
        'startLine' => 48,
        'endLine' => 51,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'implementingClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'currentClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'aliasName' => NULL,
      ),
      'getDurationMinutesAttribute' => 
      array (
        'name' => 'getDurationMinutesAttribute',
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
        'startLine' => 53,
        'endLine' => 59,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'implementingClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'currentClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'aliasName' => NULL,
      ),
      'getDurationLabelAttribute' => 
      array (
        'name' => 'getDurationLabelAttribute',
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
        'startLine' => 61,
        'endLine' => 64,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'implementingClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
        'currentClassName' => 'App\\Models\\JobOrderMaintenanceStatusPeriod',
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