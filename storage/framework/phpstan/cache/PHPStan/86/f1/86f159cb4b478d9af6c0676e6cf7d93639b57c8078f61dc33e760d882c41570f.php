<?php declare(strict_types = 1);

// osfsl-/home/lenberd/Documents/esgroup.version.2/vendor/composer/../laravel/breeze/src/Console/InstallCommand.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Laravel\Breeze\Console\InstallCommand
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-b867613f2701d2d51b2897f89da2a07e9b6c0f2da8aec90813fbf7f9f837580c-8.3.6-6.70.0.3',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/vendor/composer/../laravel/breeze/src/Console/InstallCommand.php',
      ),
    ),
    'namespace' => 'Laravel\\Breeze\\Console',
    'name' => 'Laravel\\Breeze\\Console\\InstallCommand',
    'shortName' => 'InstallCommand',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
      0 => 
      array (
        'name' => 'Symfony\\Component\\Console\\Attribute\\AsCommand',
        'isRepeated' => false,
        'arguments' => 
        array (
          'name' => 
          array (
            'code' => '\'breeze:install\'',
            'attributes' => 
            array (
              'startLine' => 22,
              'endLine' => 22,
              'startTokenPos' => 94,
              'startFilePos' => 668,
              'endTokenPos' => 94,
              'endFilePos' => 683,
            ),
          ),
        ),
      ),
    ),
    'startLine' => 22,
    'endLine' => 447,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Console\\Command',
    'implementsClassNames' => 
    array (
      0 => 'Illuminate\\Contracts\\Console\\PromptsForMissingInput',
    ),
    'traitClassNames' => 
    array (
      0 => 'Laravel\\Breeze\\Console\\InstallsApiStack',
      1 => 'Laravel\\Breeze\\Console\\InstallsBladeStack',
      2 => 'Laravel\\Breeze\\Console\\InstallsInertiaStacks',
      3 => 'Laravel\\Breeze\\Console\\InstallsLivewireStack',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'signature' => 
      array (
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'name' => 'signature',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'breeze:install {stack : The development stack that should be installed (blade,livewire,livewire-functional,react,vue,api)}
                            {--dark : Indicate that dark mode support should be installed}
                            {--pest : Indicate that Pest should be installed}
                            {--ssr : Indicates if Inertia SSR support should be installed}
                            {--typescript : Indicates if TypeScript is preferred for the Inertia stack}
                            {--eslint : Indicates if ESLint with Prettier should be installed}
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}\'',
          'attributes' => 
          array (
            'startLine' => 32,
            'endLine' => 38,
            'startTokenPos' => 134,
            'startFilePos' => 976,
            'endTokenPos' => 134,
            'endFilePos' => 1686,
          ),
        ),
        'docComment' => '/**
 * The name and signature of the console command.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 32,
        'endLine' => 38,
        'startColumn' => 5,
        'endColumn' => 129,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'description' => 
      array (
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'name' => 'description',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'Install the Breeze controllers and resources\'',
          'attributes' => 
          array (
            'startLine' => 45,
            'endLine' => 45,
            'startTokenPos' => 145,
            'startFilePos' => 1801,
            'endTokenPos' => 145,
            'endFilePos' => 1846,
          ),
        ),
        'docComment' => '/**
 * The console command description.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 45,
        'endLine' => 45,
        'startColumn' => 5,
        'endColumn' => 76,
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
      'handle' => 
      array (
        'name' => 'handle',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Execute the console command.
 *
 * @return int|null
 */',
        'startLine' => 52,
        'endLine' => 71,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'installTests' => 
      array (
        'name' => 'installTests',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Install Breeze\'s tests.
 *
 * @return bool
 */',
        'startLine' => 78,
        'endLine' => 106,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'installMiddleware' => 
      array (
        'name' => 'installMiddleware',
        'parameters' => 
        array (
          'names' => 
          array (
            'name' => 'names',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 116,
            'endLine' => 116,
            'startColumn' => 42,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'group' => 
          array (
            'name' => 'group',
            'default' => 
            array (
              'code' => '\'web\'',
              'attributes' => 
              array (
                'startLine' => 116,
                'endLine' => 116,
                'startTokenPos' => 626,
                'startFilePos' => 4424,
                'endTokenPos' => 626,
                'endFilePos' => 4428,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 116,
            'endLine' => 116,
            'startColumn' => 50,
            'endColumn' => 63,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'modifier' => 
          array (
            'name' => 'modifier',
            'default' => 
            array (
              'code' => '\'append\'',
              'attributes' => 
              array (
                'startLine' => 116,
                'endLine' => 116,
                'startTokenPos' => 633,
                'startFilePos' => 4443,
                'endTokenPos' => 633,
                'endFilePos' => 4450,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 116,
            'endLine' => 116,
            'startColumn' => 66,
            'endColumn' => 85,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Install the given middleware names into the application.
 *
 * @param  array|string  $name
 * @param  string  $group
 * @param  string  $modifier
 * @return void
 */',
        'startLine' => 116,
        'endLine' => 143,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'installMiddlewareAliases' => 
      array (
        'name' => 'installMiddlewareAliases',
        'parameters' => 
        array (
          'aliases' => 
          array (
            'name' => 'aliases',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 151,
            'endLine' => 151,
            'startColumn' => 49,
            'endColumn' => 56,
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
 * Install the given middleware aliases into the application.
 *
 * @param  array  $aliases
 * @return void
 */',
        'startLine' => 151,
        'endLine' => 178,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'hasComposerPackage' => 
      array (
        'name' => 'hasComposerPackage',
        'parameters' => 
        array (
          'package' => 
          array (
            'name' => 'package',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 186,
            'endLine' => 186,
            'startColumn' => 43,
            'endColumn' => 50,
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
 * Determine if the given Composer package is installed.
 *
 * @param  string  $package
 * @return bool
 */',
        'startLine' => 186,
        'endLine' => 192,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'requireComposerPackages' => 
      array (
        'name' => 'requireComposerPackages',
        'parameters' => 
        array (
          'packages' => 
          array (
            'name' => 'packages',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 200,
            'endLine' => 200,
            'startColumn' => 48,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'asDev' => 
          array (
            'name' => 'asDev',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 200,
                'endLine' => 200,
                'startTokenPos' => 1140,
                'startFilePos' => 7549,
                'endTokenPos' => 1140,
                'endFilePos' => 7553,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 200,
            'endLine' => 200,
            'startColumn' => 65,
            'endColumn' => 78,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Installs the given Composer Packages into the application.
 *
 * @param  bool  $asDev
 * @return bool
 */',
        'startLine' => 200,
        'endLine' => 219,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'removeComposerPackages' => 
      array (
        'name' => 'removeComposerPackages',
        'parameters' => 
        array (
          'packages' => 
          array (
            'name' => 'packages',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 227,
            'endLine' => 227,
            'startColumn' => 47,
            'endColumn' => 61,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'asDev' => 
          array (
            'name' => 'asDev',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 227,
                'endLine' => 227,
                'startTokenPos' => 1307,
                'startFilePos' => 8314,
                'endTokenPos' => 1307,
                'endFilePos' => 8318,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 227,
            'endLine' => 227,
            'startColumn' => 64,
            'endColumn' => 77,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Removes the given Composer Packages from the application.
 *
 * @param  bool  $asDev
 * @return bool
 */',
        'startLine' => 227,
        'endLine' => 246,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'updateNodePackages' => 
      array (
        'name' => 'updateNodePackages',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 254,
            'endLine' => 254,
            'startColumn' => 50,
            'endColumn' => 67,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'dev' => 
          array (
            'name' => 'dev',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 254,
                'endLine' => 254,
                'startTokenPos' => 1476,
                'startFilePos' => 9073,
                'endTokenPos' => 1476,
                'endFilePos' => 9076,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 254,
            'endLine' => 254,
            'startColumn' => 70,
            'endColumn' => 80,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Update the dependencies in the "package.json" file.
 *
 * @param  bool  $dev
 * @return void
 */',
        'startLine' => 254,
        'endLine' => 275,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 18,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'updateNodeScripts' => 
      array (
        'name' => 'updateNodeScripts',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 282,
            'endLine' => 282,
            'startColumn' => 49,
            'endColumn' => 66,
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
 * Update the scripts in the "package.json" file.
 *
 * @return void
 */',
        'startLine' => 282,
        'endLine' => 298,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 18,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'flushNodeModules' => 
      array (
        'name' => 'flushNodeModules',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Delete the "node_modules" directory and remove the associated lock files.
 *
 * @return void
 */',
        'startLine' => 305,
        'endLine' => 317,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 18,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'replaceInFile' => 
      array (
        'name' => 'replaceInFile',
        'parameters' => 
        array (
          'search' => 
          array (
            'name' => 'search',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 327,
            'endLine' => 327,
            'startColumn' => 38,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'replace' => 
          array (
            'name' => 'replace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 327,
            'endLine' => 327,
            'startColumn' => 47,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 327,
            'endLine' => 327,
            'startColumn' => 57,
            'endColumn' => 61,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace a given string within a given file.
 *
 * @param  string  $search
 * @param  string  $replace
 * @param  string  $path
 * @return void
 */',
        'startLine' => 327,
        'endLine' => 330,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'phpBinary' => 
      array (
        'name' => 'phpBinary',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the path to the appropriate PHP binary.
 *
 * @return string
 */',
        'startLine' => 337,
        'endLine' => 344,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'runCommands' => 
      array (
        'name' => 'runCommands',
        'parameters' => 
        array (
          'commands' => 
          array (
            'name' => 'commands',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 352,
            'endLine' => 352,
            'startColumn' => 36,
            'endColumn' => 44,
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
 * Run the given commands.
 *
 * @param  array  $commands
 * @return void
 */',
        'startLine' => 352,
        'endLine' => 367,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'removeDarkClasses' => 
      array (
        'name' => 'removeDarkClasses',
        'parameters' => 
        array (
          'finder' => 
          array (
            'name' => 'finder',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Symfony\\Component\\Finder\\Finder',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 374,
            'endLine' => 374,
            'startColumn' => 42,
            'endColumn' => 55,
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
 * Remove Tailwind dark classes from the given files.
 *
 * @return void
 */',
        'startLine' => 374,
        'endLine' => 379,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'promptForMissingArgumentsUsing' => 
      array (
        'name' => 'promptForMissingArgumentsUsing',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Prompt for missing input arguments using the returned questions.
 *
 * @return array
 */',
        'startLine' => 386,
        'endLine' => 402,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'afterPromptingForMissingArguments' => 
      array (
        'name' => 'afterPromptingForMissingArguments',
        'parameters' => 
        array (
          'input' => 
          array (
            'name' => 'input',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Symfony\\Component\\Console\\Input\\InputInterface',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 409,
            'endLine' => 409,
            'startColumn' => 58,
            'endColumn' => 78,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'output' => 
          array (
            'name' => 'output',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Symfony\\Component\\Console\\Output\\OutputInterface',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 409,
            'endLine' => 409,
            'startColumn' => 81,
            'endColumn' => 103,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Interact further with the user if they were prompted for missing arguments.
 *
 * @return void
 */',
        'startLine' => 409,
        'endLine' => 436,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'aliasName' => NULL,
      ),
      'isUsingPest' => 
      array (
        'name' => 'isUsingPest',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine whether the project is already using Pest.
 *
 * @return bool
 */',
        'startLine' => 443,
        'endLine' => 446,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Breeze\\Console',
        'declaringClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'implementingClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
        'currentClassName' => 'Laravel\\Breeze\\Console\\InstallCommand',
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