<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\PurchaseOrderItem.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PurchaseOrderItem
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-f7987265c2343737d7b20f68076b38a3da7f449b750cc4178f7a47c6eb476a84',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PurchaseOrderItem',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/PurchaseOrderItem.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PurchaseOrderItem',
    'shortName' => 'PurchaseOrderItem',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $purchase_order_id
 * @property string|null $product_id
 * @property string|null $qty
 * @property string|null $purchased_qty
 * @property string|null $received_qty
 * @property string|null $store_name
 * @property string|null $removed
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 20,
    'endLine' => 49,
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
        'declaringClassName' => 'App\\Models\\PurchaseOrderItem',
        'implementingClassName' => 'App\\Models\\PurchaseOrderItem',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'purchase_order_id\', \'product_id\', \'qty\', \'purchased_qty\', \'received_qty\', \'store_name\', \'removed\']',
          'attributes' => 
          array (
            'startLine' => 22,
            'endLine' => 30,
            'startTokenPos' => 48,
            'startFilePos' => 538,
            'endTokenPos' => 71,
            'endFilePos' => 700,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 22,
        'endLine' => 30,
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
      'purchaseOrder' => 
      array (
        'name' => 'purchaseOrder',
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
        'docComment' => '/** @return BelongsTo<PurchaseOrder, $this> */',
        'startLine' => 33,
        'endLine' => 36,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PurchaseOrderItem',
        'implementingClassName' => 'App\\Models\\PurchaseOrderItem',
        'currentClassName' => 'App\\Models\\PurchaseOrderItem',
        'aliasName' => NULL,
      ),
      'product' => 
      array (
        'name' => 'product',
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
        'docComment' => '/** @return BelongsTo<Product, $this> */',
        'startLine' => 39,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PurchaseOrderItem',
        'implementingClassName' => 'App\\Models\\PurchaseOrderItem',
        'currentClassName' => 'App\\Models\\PurchaseOrderItem',
        'aliasName' => NULL,
      ),
      'receives' => 
      array (
        'name' => 'receives',
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
        'docComment' => '/** @return HasMany<PurchaseReceive, $this> */',
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
        'declaringClassName' => 'App\\Models\\PurchaseOrderItem',
        'implementingClassName' => 'App\\Models\\PurchaseOrderItem',
        'currentClassName' => 'App\\Models\\PurchaseOrderItem',
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