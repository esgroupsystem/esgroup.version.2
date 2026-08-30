<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Services/Permissions/RoutePermissionSyncService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Services\Permissions\RoutePermissionSyncService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-d9aa9ed21b21b2d1ddbd46e004eff1575e9a3744691ebe7b274e83546378d936',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Services/Permissions/RoutePermissionSyncService.php',
      ),
    ),
    'namespace' => 'App\\Services\\Permissions',
    'name' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
    'shortName' => 'RoutePermissionSyncService',
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
    'endLine' => 174,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
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
      'systemPermissions' => 
      array (
        'declaringClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'implementingClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'name' => 'systemPermissions',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '[
    /*
    |--------------------------------------------------------------------------
    | Payroll Salary Security
    |--------------------------------------------------------------------------
    */
    \'payroll.all-access\',
    \'payroll.mirasol\',
    \'payroll.gonzales\',
]',
          'attributes' => 
          array (
            'startLine' => 20,
            'endLine' => 40,
            'startTokenPos' => 56,
            'startFilePos' => 466,
            'endTokenPos' => 71,
            'endFilePos' => 1024,
          ),
        ),
        'docComment' => '/**
 * System permissions not directly attached to routes.
 *
 * These are used for data-level security.
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 20,
        'endLine' => 40,
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
      'scan' => 
      array (
        'name' => 'scan',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Support\\Collection',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 42,
        'endLine' => 61,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Services\\Permissions',
        'declaringClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'implementingClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'currentClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'aliasName' => NULL,
      ),
      'sync' => 
      array (
        'name' => 'sync',
        'parameters' => 
        array (
          'guardName' => 
          array (
            'name' => 'guardName',
            'default' => 
            array (
              'code' => '\'web\'',
              'attributes' => 
              array (
                'startLine' => 63,
                'endLine' => 63,
                'startTokenPos' => 222,
                'startFilePos' => 1750,
                'endTokenPos' => 222,
                'endFilePos' => 1754,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 63,
            'endLine' => 63,
            'startColumn' => 26,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
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
        'startLine' => 63,
        'endLine' => 149,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Services\\Permissions',
        'declaringClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'implementingClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'currentClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'aliasName' => NULL,
      ),
      'extractPermissions' => 
      array (
        'name' => 'extractPermissions',
        'parameters' => 
        array (
          'middleware' => 
          array (
            'name' => 'middleware',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 152,
            'endLine' => 152,
            'startColumn' => 9,
            'endColumn' => 26,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
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
        'startLine' => 151,
        'endLine' => 173,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'App\\Services\\Permissions',
        'declaringClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'implementingClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
        'currentClassName' => 'App\\Services\\Permissions\\RoutePermissionSyncService',
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