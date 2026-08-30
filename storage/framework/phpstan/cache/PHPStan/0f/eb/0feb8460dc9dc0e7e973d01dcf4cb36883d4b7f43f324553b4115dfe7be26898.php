<?php declare(strict_types = 1);

// osfsl-/home/lenberd/Documents/esgroup.version.2/vendor/composer/../spatie/laravel-permission/src/Commands/CreateRole.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Spatie\Permission\Commands\CreateRole
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-92760139c211e2a1b7595a887cff989fdd28ea529d20d4605efce12d518329c9-8.3.6-6.70.0.3',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Spatie\\Permission\\Commands\\CreateRole',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/vendor/composer/../spatie/laravel-permission/src/Commands/CreateRole.php',
      ),
    ),
    'namespace' => 'Spatie\\Permission\\Commands',
    'name' => 'Spatie\\Permission\\Commands\\CreateRole',
    'shortName' => 'CreateRole',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 10,
    'endLine' => 67,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Console\\Command',
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
      'signature' => 
      array (
        'declaringClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
        'implementingClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
        'name' => 'signature',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'permission:create-role
        {name : The name of the role}
        {guard? : The name of the guard}
        {permissions? : A list of permissions to assign to the role, separated by | }
        {--team-id=}\'',
          'attributes' => 
          array (
            'startLine' => 12,
            'endLine' => 16,
            'startTokenPos' => 51,
            'startFilePos' => 304,
            'endTokenPos' => 51,
            'endFilePos' => 513,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 12,
        'endLine' => 16,
        'startColumn' => 5,
        'endColumn' => 22,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'description' => 
      array (
        'declaringClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
        'implementingClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
        'name' => 'description',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'Create a role\'',
          'attributes' => 
          array (
            'startLine' => 18,
            'endLine' => 18,
            'startTokenPos' => 60,
            'startFilePos' => 546,
            'endTokenPos' => 60,
            'endFilePos' => 560,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 18,
        'endLine' => 18,
        'startColumn' => 5,
        'endColumn' => 45,
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
          'permissionRegistrar' => 
          array (
            'name' => 'permissionRegistrar',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Spatie\\Permission\\PermissionRegistrar',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 20,
            'endLine' => 20,
            'startColumn' => 28,
            'endColumn' => 67,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 20,
        'endLine' => 44,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Spatie\\Permission\\Commands',
        'declaringClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
        'implementingClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
        'currentClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
        'aliasName' => NULL,
      ),
      'makePermissions' => 
      array (
        'name' => 'makePermissions',
        'parameters' => 
        array (
          'string' => 
          array (
            'name' => 'string',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 49,
                'endLine' => 49,
                'startTokenPos' => 295,
                'startFilePos' => 1782,
                'endTokenPos' => 295,
                'endFilePos' => 1785,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 49,
            'endLine' => 49,
            'startColumn' => 40,
            'endColumn' => 53,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  array|null|string  $string
 */',
        'startLine' => 49,
        'endLine' => 66,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Spatie\\Permission\\Commands',
        'declaringClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
        'implementingClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
        'currentClassName' => 'Spatie\\Permission\\Commands\\CreateRole',
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