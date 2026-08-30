<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Providers/EventServiceProvider.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Providers\EventServiceProvider
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-1bec68df1357683854f862217b64b3dcd846925236c8787824227615b85429a0',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Providers\\EventServiceProvider',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Providers/EventServiceProvider.php',
      ),
    ),
    'namespace' => 'App\\Providers',
    'name' => 'App\\Providers\\EventServiceProvider',
    'shortName' => 'EventServiceProvider',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 9,
    'endLine' => 36,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
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
      'listen' => 
      array (
        'declaringClassName' => 'App\\Providers\\EventServiceProvider',
        'implementingClassName' => 'App\\Providers\\EventServiceProvider',
        'name' => 'listen',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\\Illuminate\\Auth\\Events\\Login::class => [\\App\\Listeners\\LogSuccessfulLogin::class], \\Illuminate\\Auth\\Events\\Failed::class => [\\App\\Listeners\\LogFailedLogin::class], \\Illuminate\\Auth\\Events\\Logout::class => [\\App\\Listeners\\LogLogout::class], \\Illuminate\\Auth\\Events\\Lockout::class => [\\App\\Listeners\\LogLockout::class], \\App\\Events\\JobOrderCreated::class => [\\App\\Listeners\\SendJobOrderNotification::class], \\App\\Events\\POCreated::class => [\\App\\Listeners\\SendPOCreatedNotification::class]]',
          'attributes' => 
          array (
            'startLine' => 11,
            'endLine' => 30,
            'startTokenPos' => 40,
            'startFilePos' => 222,
            'endTokenPos' => 138,
            'endFilePos' => 904,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 11,
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
      'boot' => 
      array (
        'name' => 'boot',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 32,
        'endLine' => 35,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Providers',
        'declaringClassName' => 'App\\Providers\\EventServiceProvider',
        'implementingClassName' => 'App\\Providers\\EventServiceProvider',
        'currentClassName' => 'App\\Providers\\EventServiceProvider',
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