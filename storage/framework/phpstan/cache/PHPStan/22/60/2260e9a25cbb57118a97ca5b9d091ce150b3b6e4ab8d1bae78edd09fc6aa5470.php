<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/StockMovement.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\StockMovement
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-303e85ad2e4d99c71bd1a44d9a29998b34bffd891050ff6dc1a915218d7e7189',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\StockMovement',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/StockMovement.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\StockMovement',
    'shortName' => 'StockMovement',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $product_id
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $movement_type
 * @property string|null $qty
 * @property string|null $stock_before
 * @property string|null $stock_after
 * @property string|null $transaction_date
 * @property string|null $remarks
 * @property string|null $created_by
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 22,
    'endLine' => 42,
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
        'declaringClassName' => 'App\\Models\\StockMovement',
        'implementingClassName' => 'App\\Models\\StockMovement',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'product_id\', \'reference_type\', \'reference_id\', \'movement_type\', \'qty\', \'stock_before\', \'stock_after\', \'transaction_date\', \'remarks\', \'created_by\']',
          'attributes' => 
          array (
            'startLine' => 24,
            'endLine' => 35,
            'startTokenPos' => 43,
            'startFilePos' => 599,
            'endTokenPos' => 75,
            'endFilePos' => 833,
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
    ),
    'immediateMethods' => 
    array (
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
        'startLine' => 38,
        'endLine' => 41,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\StockMovement',
        'implementingClassName' => 'App\\Models\\StockMovement',
        'currentClassName' => 'App\\Models\\StockMovement',
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