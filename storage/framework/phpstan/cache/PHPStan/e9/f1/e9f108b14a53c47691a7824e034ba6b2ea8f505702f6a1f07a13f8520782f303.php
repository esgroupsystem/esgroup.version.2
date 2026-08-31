<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/StockTransfer.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\StockTransfer
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-f34fbd3fa0336b4d0e0e99a39973740fa5e1cc38db3b29480db10d85874e4694',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\StockTransfer',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/StockTransfer.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\StockTransfer',
    'shortName' => 'StockTransfer',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $transfer_number
 * @property string|null $from_location_id
 * @property string|null $to_location_id
 * @property \\Carbon\\CarbonInterface|null $transfer_date
 * @property string|null $requested_by
 * @property string|null $received_by
 * @property string|null $remarks
 * @property string|null $status
 * @property \\Carbon\\CarbonInterface|null $rolled_back_at
 * @property string|null $rolled_back_by
 * @property string|null $rollback_reason
 * @property string|null $created_by
 * @property-read \\Illuminate\\Database\\Eloquent\\Collection<int, StockTransferItem> $items
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 27,
    'endLine' => 83,
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
        'declaringClassName' => 'App\\Models\\StockTransfer',
        'implementingClassName' => 'App\\Models\\StockTransfer',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'transfer_number\', \'from_location_id\', \'to_location_id\', \'transfer_date\', \'requested_by\', \'received_by\', \'remarks\', \'status\', \'rolled_back_at\', \'rolled_back_by\', \'rollback_reason\', \'created_by\']',
          'attributes' => 
          array (
            'startLine' => 29,
            'endLine' => 42,
            'startTokenPos' => 53,
            'startFilePos' => 910,
            'endTokenPos' => 91,
            'endFilePos' => 1207,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 29,
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
      'casts' => 
      array (
        'declaringClassName' => 'App\\Models\\StockTransfer',
        'implementingClassName' => 'App\\Models\\StockTransfer',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'transfer_date\' => \'date\', \'rolled_back_at\' => \'datetime\']',
          'attributes' => 
          array (
            'startLine' => 44,
            'endLine' => 47,
            'startTokenPos' => 100,
            'startFilePos' => 1234,
            'endTokenPos' => 116,
            'endFilePos' => 1315,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 44,
        'endLine' => 47,
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
      'fromLocation' => 
      array (
        'name' => 'fromLocation',
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
        'docComment' => '/** @return BelongsTo<Location, $this> */',
        'startLine' => 50,
        'endLine' => 53,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StockTransfer',
        'implementingClassName' => 'App\\Models\\StockTransfer',
        'currentClassName' => 'App\\Models\\StockTransfer',
        'aliasName' => NULL,
      ),
      'toLocation' => 
      array (
        'name' => 'toLocation',
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
        'docComment' => '/** @return BelongsTo<Location, $this> */',
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
        'declaringClassName' => 'App\\Models\\StockTransfer',
        'implementingClassName' => 'App\\Models\\StockTransfer',
        'currentClassName' => 'App\\Models\\StockTransfer',
        'aliasName' => NULL,
      ),
      'items' => 
      array (
        'name' => 'items',
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
        'docComment' => '/** @return HasMany<StockTransferItem, $this> */',
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
        'declaringClassName' => 'App\\Models\\StockTransfer',
        'implementingClassName' => 'App\\Models\\StockTransfer',
        'currentClassName' => 'App\\Models\\StockTransfer',
        'aliasName' => NULL,
      ),
      'creator' => 
      array (
        'name' => 'creator',
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
        'declaringClassName' => 'App\\Models\\StockTransfer',
        'implementingClassName' => 'App\\Models\\StockTransfer',
        'currentClassName' => 'App\\Models\\StockTransfer',
        'aliasName' => NULL,
      ),
      'rollbackUser' => 
      array (
        'name' => 'rollbackUser',
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
        'declaringClassName' => 'App\\Models\\StockTransfer',
        'implementingClassName' => 'App\\Models\\StockTransfer',
        'currentClassName' => 'App\\Models\\StockTransfer',
        'aliasName' => NULL,
      ),
      'isRolledBack' => 
      array (
        'name' => 'isRolledBack',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 79,
        'endLine' => 82,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StockTransfer',
        'implementingClassName' => 'App\\Models\\StockTransfer',
        'currentClassName' => 'App\\Models\\StockTransfer',
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