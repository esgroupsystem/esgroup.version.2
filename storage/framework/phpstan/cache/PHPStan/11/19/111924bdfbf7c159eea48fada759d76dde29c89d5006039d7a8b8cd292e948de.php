<?php declare(strict_types = 1);

// osfsl-/home/lenberd/Documents/esgroup.version.2/vendor/composer/../laravel/framework/src/Illuminate/Foundation/Support/Providers/EventServiceProvider.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Foundation\Support\Providers\EventServiceProvider
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-c59aa18f125c784aa0c532b86c1afb14552fc29219e67d679d45e4f8a24003d8-8.3.6-6.70.0.3',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/vendor/composer/../laravel/framework/src/Illuminate/Foundation/Support/Providers/EventServiceProvider.php',
      ),
    ),
    'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
    'name' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
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
    'startLine' => 13,
    'endLine' => 230,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Support\\ServiceProvider',
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
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'name' => 'listen',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 20,
            'endLine' => 20,
            'startTokenPos' => 60,
            'startFilePos' => 559,
            'endTokenPos' => 61,
            'endFilePos' => 560,
          ),
        ),
        'docComment' => '/**
 * The event handler mappings for the application.
 *
 * @var array<string, array<int, string>>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 20,
        'endLine' => 20,
        'startColumn' => 5,
        'endColumn' => 27,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'subscribe' => 
      array (
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'name' => 'subscribe',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 27,
            'endLine' => 27,
            'startTokenPos' => 72,
            'startFilePos' => 668,
            'endTokenPos' => 73,
            'endFilePos' => 669,
          ),
        ),
        'docComment' => '/**
 * The subscribers to register.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 27,
        'endLine' => 27,
        'startColumn' => 5,
        'endColumn' => 30,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'observers' => 
      array (
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'name' => 'observers',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 34,
            'endLine' => 34,
            'startTokenPos' => 84,
            'startFilePos' => 830,
            'endTokenPos' => 85,
            'endFilePos' => 831,
          ),
        ),
        'docComment' => '/**
 * The model observers to register.
 *
 * @var array<string, string|object|array<int, string|object>>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 34,
        'endLine' => 34,
        'startColumn' => 5,
        'endColumn' => 30,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'shouldDiscoverEvents' => 
      array (
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'name' => 'shouldDiscoverEvents',
        'modifiers' => 18,
        'type' => NULL,
        'default' => 
        array (
          'code' => 'true',
          'attributes' => 
          array (
            'startLine' => 41,
            'endLine' => 41,
            'startTokenPos' => 98,
            'startFilePos' => 969,
            'endTokenPos' => 98,
            'endFilePos' => 972,
          ),
        ),
        'docComment' => '/**
 * Indicates if events should be discovered.
 *
 * @var bool
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 41,
        'endLine' => 41,
        'startColumn' => 5,
        'endColumn' => 50,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'eventDiscoveryPaths' => 
      array (
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'name' => 'eventDiscoveryPaths',
        'modifiers' => 18,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The configured event discovery paths.
 *
 * @var iterable<int, string>|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 48,
        'endLine' => 48,
        'startColumn' => 5,
        'endColumn' => 42,
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
      'register' => 
      array (
        'name' => 'register',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Register the application\'s event listeners.
 *
 * @return void
 */',
        'startLine' => 55,
        'endLine' => 78,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'boot' => 
      array (
        'name' => 'boot',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Boot any application services.
 *
 * @return void
 */',
        'startLine' => 85,
        'endLine' => 88,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'listens' => 
      array (
        'name' => 'listens',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the events and handlers.
 *
 * @return array
 */',
        'startLine' => 95,
        'endLine' => 98,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'getEvents' => 
      array (
        'name' => 'getEvents',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the discovered events and listeners for the application.
 *
 * @return array
 */',
        'startLine' => 105,
        'endLine' => 117,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'discoveredEvents' => 
      array (
        'name' => 'discoveredEvents',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the discovered events for the application.
 *
 * @return array
 */',
        'startLine' => 124,
        'endLine' => 129,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'shouldDiscoverEvents' => 
      array (
        'name' => 'shouldDiscoverEvents',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if events and listeners should be automatically discovered.
 *
 * @return bool
 */',
        'startLine' => 136,
        'endLine' => 139,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'discoverEvents' => 
      array (
        'name' => 'discoverEvents',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Discover the events and listeners for the application.
 *
 * @return array
 */',
        'startLine' => 146,
        'endLine' => 159,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'discoverEventsWithin' => 
      array (
        'name' => 'discoverEventsWithin',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the listener directories that should be used to discover events.
 *
 * @return iterable<int, string>
 */',
        'startLine' => 166,
        'endLine' => 171,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'addEventDiscoveryPaths' => 
      array (
        'name' => 'addEventDiscoveryPaths',
        'parameters' => 
        array (
          'paths' => 
          array (
            'name' => 'paths',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'iterable',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 179,
            'endLine' => 179,
            'startColumn' => 51,
            'endColumn' => 72,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Add the given event discovery paths to the application\'s event discovery paths.
 *
 * @param  string|iterable<int, string>  $paths
 * @return void
 */',
        'startLine' => 179,
        'endLine' => 185,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'setEventDiscoveryPaths' => 
      array (
        'name' => 'setEventDiscoveryPaths',
        'parameters' => 
        array (
          'paths' => 
          array (
            'name' => 'paths',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'iterable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 193,
            'endLine' => 193,
            'startColumn' => 51,
            'endColumn' => 65,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the globally configured event discovery paths.
 *
 * @param  iterable<int, string>  $paths
 * @return void
 */',
        'startLine' => 193,
        'endLine' => 196,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'eventDiscoveryBasePath' => 
      array (
        'name' => 'eventDiscoveryBasePath',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the base path to be used during event discovery.
 *
 * @return string
 */',
        'startLine' => 203,
        'endLine' => 206,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'disableEventDiscovery' => 
      array (
        'name' => 'disableEventDiscovery',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Disable event discovery for the application.
 *
 * @return void
 */',
        'startLine' => 213,
        'endLine' => 216,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'aliasName' => NULL,
      ),
      'configureEmailVerification' => 
      array (
        'name' => 'configureEmailVerification',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Configure the proper event listeners for email verification.
 *
 * @return void
 */',
        'startLine' => 223,
        'endLine' => 229,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Foundation\\Support\\Providers',
        'declaringClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'implementingClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
        'currentClassName' => 'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider',
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