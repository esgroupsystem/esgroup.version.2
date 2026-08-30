<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/CctvConcernItem.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\CctvConcernItem
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-dcc5c7dea797e979a33bc5b54322e2a4dcc85b9d4e9294f3355943982e6f73e1',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\CctvConcernItem',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/CctvConcernItem.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\CctvConcernItem',
    'shortName' => 'CctvConcernItem',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $cctv_concern_id
 * @property string|null $it_inventory_item_id
 * @property string|null $qty_used
 * @property string|null $remarks
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 16,
    'endLine' => 36,
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
        'declaringClassName' => 'App\\Models\\CctvConcernItem',
        'implementingClassName' => 'App\\Models\\CctvConcernItem',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'cctv_concern_id\', \'it_inventory_item_id\', \'qty_used\', \'remarks\']',
          'attributes' => 
          array (
            'startLine' => 18,
            'endLine' => 23,
            'startTokenPos' => 43,
            'startFilePos' => 381,
            'endTokenPos' => 57,
            'endFilePos' => 485,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 18,
        'endLine' => 23,
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
      'concern' => 
      array (
        'name' => 'concern',
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
        'docComment' => '/** @return BelongsTo<CctvConcern, $this> */',
        'startLine' => 26,
        'endLine' => 29,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\CctvConcernItem',
        'implementingClassName' => 'App\\Models\\CctvConcernItem',
        'currentClassName' => 'App\\Models\\CctvConcernItem',
        'aliasName' => NULL,
      ),
      'inventoryItem' => 
      array (
        'name' => 'inventoryItem',
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
        'docComment' => '/** @return BelongsTo<ItInventoryItem, $this> */',
        'startLine' => 32,
        'endLine' => 35,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\CctvConcernItem',
        'implementingClassName' => 'App\\Models\\CctvConcernItem',
        'currentClassName' => 'App\\Models\\CctvConcernItem',
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