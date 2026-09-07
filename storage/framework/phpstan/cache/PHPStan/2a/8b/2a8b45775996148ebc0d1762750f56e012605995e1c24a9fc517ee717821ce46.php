<?php declare(strict_types = 1);

// ftm-C:\xampp\htdocs\esgroup.version.2\vendor\spatie\laravel-permission\src\Models\Permission.php
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v6-2.3.3',
   'data' => 
  array (
    0 => 
    array (
      '75064e6d76fce2aae9e17f90c88d3077' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '7d89a417ece29f83ac9ca72413e95508' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '7f7341b8ed09f702f25e068c3d2bf645' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '434d3b1cfaa41ffb4588ab97b3e2863b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'bootHasPermissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '9e308e14571986314fd5ae6aefadd8a7' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getPermissionClass',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '56bd9b29a7c0567c39b1837c7656ec48' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getWildcardClass',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'aadfde69f67037705ee6090c201bddb6' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'permissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '2c8ae40e3651473701d99d16a15fdd48' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'scopePermission',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '31da2a532091758328946417fbfa5e87' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'scopeWithoutPermission',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '651913fed4c6d88e3943e1f4fb492b93' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'convertToPermissionModels',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'c39792f4ad04dc4d6c38eed6dfcdcc15' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'filterPermission',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '627de98f0dfde6eed8a728d046690193' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasPermissionTo',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '62f7380d3fa7cf22c093df57c3f7d8a1' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasWildcardPermission',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'f1514c0ff2de9f26539993cf431cc49d' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'checkPermissionTo',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '78472a7c69d73896e03f073e053f2c06' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasAnyPermission',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '7ba0d5ff55503bcc32cf289911422a8c' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasAllPermissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'df0a746437bab5c355ca94fbef68bd6d' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasPermissionViaRole',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '34732d0f9aa5ecc87121400ef2e152db' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasDirectPermission',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'adef6820bf080b2981ffd5c2e98766c6' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getPermissionsViaRoles',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '890b4571979a881cc44c5eb95648c787' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getAllPermissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '995cc0fae7e7ff3d53e1e7000635246b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'collectPermissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'c35b6406a0ea54391c81b9170aebdbf0' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'givePermissionTo',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '441c6dd5efbe0cc4bbc3015b35a2773d' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'forgetWildcardPermissionIndex',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '2fbcdddb6ce09b7c9bc1f2224a22d352' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'syncPermissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '884ea03cdb21039db05cf4a4339da219' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'revokePermissionTo',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '9b7c6c73818d1f5468f19939e21fdfd1' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getPermissionNames',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '643bf668ae63a874c53918f921bc606f' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getStoredPermission',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '786e396523c2c59eead20aaf0bbff091' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'ensureModelSharesGuard',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '5b273f8235362e73bd4fb117c6ff04ca' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getGuardNames',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'e27d56ec6e81019be28e878ce33f2ab0' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getDefaultGuardName',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      '91d2bee278a9acfa6efbb57876618af8' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'forgetCachedPermissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'd95a043aebda2c13b76f76fa7a7f65a9' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasAllDirectPermissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'd8aa892958b60032be06cf5fb639a3f8' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'wildcard' => 'Spatie\\Permission\\Contracts\\Wildcard',
          'permissionattached' => 'Spatie\\Permission\\Events\\PermissionAttached',
          'permissiondetached' => 'Spatie\\Permission\\Events\\PermissionDetached',
          'guarddoesnotmatch' => 'Spatie\\Permission\\Exceptions\\GuardDoesNotMatch',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'wildcardpermissioninvalidargument' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionInvalidArgument',
          'wildcardpermissionnotimplementscontract' => 'Spatie\\Permission\\Exceptions\\WildcardPermissionNotImplementsContract',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'wildcardpermission' => 'Spatie\\Permission\\WildcardPermission',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasAnyDirectPermission',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasPermissions',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasPermissions',
          3 => 'Spatie\\Permission\\Traits\\HasRoles',
          4 => NULL,
        ),
      )),
      'f1a9d0dcb41e0b099c365154b2323674' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'bootHasRoles',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'cc4a6a21425055713406c1bd617bfc70' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getRoleClass',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'cfcbcdeabed8b3b99a7d57afb46d1a75' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'roles',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '058c4c9ced565a4402bb766040b1090c' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'scopeRole',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '57ab1d251cfadb76f205cff43df94ac8' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'scopeWithoutRole',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'd98bae4f347f01ad78c0be06d05a8fc9' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'collectRoles',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '6fc4c4c7ebeb5b6833c180d107fd63a7' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'assignRole',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'c18946247580d398a8620bfd1dc47bd3' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'removeRole',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '06a4b76108613b6d6ad5ec49793cb687' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'syncRoles',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'd6bb1b114b3ded2d02f6d736358c1e2f' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasRole',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '2389ccc878dd2a4b8ba4ee2214c9d1b6' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasAnyRole',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'dd40a9d0b50c2588da663ac2d8d7f93d' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasAllRoles',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '3b47d48d79066077fb8570d8b8c1928d' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'hasExactRoles',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '39e10588f1d12036b29637d22931aa4f' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getDirectPermissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '591e5f49f7db22e32c7e180c9f6dcf61' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getRoleNames',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'ec1d3bc302d6708779fee33987150d21' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getStoredRole',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'fe5d3981b19f2dbba6a85fc4bf22e2b1' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'builder' => 'Illuminate\\Database\\Eloquent\\Builder',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'arr' => 'Illuminate\\Support\\Arr',
          'collection' => 'Illuminate\\Support\\Collection',
          'permission' => 'Spatie\\Permission\\Contracts\\Permission',
          'role' => 'Spatie\\Permission\\Contracts\\Role',
          'roleattached' => 'Spatie\\Permission\\Events\\RoleAttached',
          'roledetached' => 'Spatie\\Permission\\Events\\RoleDetached',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'convertPipeToArray',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\HasRoles',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\HasRoles',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'f5124e086ca6a92164c740c9cd47de05' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => NULL,
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          3 => NULL,
          4 => NULL,
        ),
      )),
      '7e5381730ae13600b2898d3525551246' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Traits',
         'uses' => 
        array (
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'bootRefreshesPermissionCache',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => NULL,
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
         'traitData' => 
        array (
          0 => 'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php',
          1 => 'Spatie\\Permission\\Models\\Permission',
          2 => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          3 => NULL,
          4 => NULL,
        ),
      )),
      'e06522b9a6bdfd50c5b4aa3f3f6a98ad' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => '__construct',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Spatie\\Permission\\Models',
           'uses' => 
          array (
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'model' => 'Illuminate\\Database\\Eloquent\\Model',
            'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
            'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
            'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
            'guard' => 'Spatie\\Permission\\Guard',
            'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
            'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
            'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          ),
           'className' => 'Spatie\\Permission\\Models\\Permission',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '59c103d9b5433129df7d1bc90f6eca75' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'create',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Spatie\\Permission\\Models',
           'uses' => 
          array (
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'model' => 'Illuminate\\Database\\Eloquent\\Model',
            'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
            'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
            'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
            'guard' => 'Spatie\\Permission\\Guard',
            'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
            'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
            'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          ),
           'className' => 'Spatie\\Permission\\Models\\Permission',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'b2866ee4b6d4140e08eb4e9ddfcae772' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'roles',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Spatie\\Permission\\Models',
           'uses' => 
          array (
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'model' => 'Illuminate\\Database\\Eloquent\\Model',
            'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
            'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
            'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
            'guard' => 'Spatie\\Permission\\Guard',
            'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
            'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
            'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          ),
           'className' => 'Spatie\\Permission\\Models\\Permission',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '93e927572bdd7843bf61cb28926dfc74' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'users',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Spatie\\Permission\\Models',
           'uses' => 
          array (
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'model' => 'Illuminate\\Database\\Eloquent\\Model',
            'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
            'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
            'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
            'guard' => 'Spatie\\Permission\\Guard',
            'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
            'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
            'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          ),
           'className' => 'Spatie\\Permission\\Models\\Permission',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '74559ce5146b581250e614822c670a9e' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'findByName',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Spatie\\Permission\\Models',
           'uses' => 
          array (
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'model' => 'Illuminate\\Database\\Eloquent\\Model',
            'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
            'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
            'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
            'guard' => 'Spatie\\Permission\\Guard',
            'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
            'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
            'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          ),
           'className' => 'Spatie\\Permission\\Models\\Permission',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '6dcdf7bd0d95e47ce19226cbc5b8ffe2' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'findById',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Spatie\\Permission\\Models',
           'uses' => 
          array (
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'model' => 'Illuminate\\Database\\Eloquent\\Model',
            'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
            'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
            'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
            'guard' => 'Spatie\\Permission\\Guard',
            'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
            'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
            'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          ),
           'className' => 'Spatie\\Permission\\Models\\Permission',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'c54121208338e135e51fc159032fa36e' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'findOrCreate',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Spatie\\Permission\\Models',
           'uses' => 
          array (
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'model' => 'Illuminate\\Database\\Eloquent\\Model',
            'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
            'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
            'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
            'guard' => 'Spatie\\Permission\\Guard',
            'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
            'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
            'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          ),
           'className' => 'Spatie\\Permission\\Models\\Permission',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      'a430a7c1af9a184be21565d4f179d81b' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getPermissions',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Spatie\\Permission\\Models',
           'uses' => 
          array (
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'model' => 'Illuminate\\Database\\Eloquent\\Model',
            'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
            'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
            'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
            'guard' => 'Spatie\\Permission\\Guard',
            'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
            'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
            'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          ),
           'className' => 'Spatie\\Permission\\Models\\Permission',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
      '1de9edf4aad285151ed19263fb6959b8' => 
      \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
         'namespace' => 'Spatie\\Permission\\Models',
         'uses' => 
        array (
          'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
          'model' => 'Illuminate\\Database\\Eloquent\\Model',
          'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
          'carbon' => 'Illuminate\\Support\\Carbon',
          'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
          'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
          'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
          'guard' => 'Spatie\\Permission\\Guard',
          'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
          'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
          'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
        ),
         'className' => 'Spatie\\Permission\\Models\\Permission',
         'functionName' => 'getPermission',
         'templatePhpDocNodes' => 
        array (
        ),
         'parent' => 
        \PHPStan\Analyser\IntermediaryNameScope::__set_state(array(
           'namespace' => 'Spatie\\Permission\\Models',
           'uses' => 
          array (
            'collection' => 'Illuminate\\Database\\Eloquent\\Collection',
            'model' => 'Illuminate\\Database\\Eloquent\\Model',
            'belongstomany' => 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany',
            'carbon' => 'Illuminate\\Support\\Carbon',
            'permissioncontract' => 'Spatie\\Permission\\Contracts\\Permission',
            'permissionalreadyexists' => 'Spatie\\Permission\\Exceptions\\PermissionAlreadyExists',
            'permissiondoesnotexist' => 'Spatie\\Permission\\Exceptions\\PermissionDoesNotExist',
            'guard' => 'Spatie\\Permission\\Guard',
            'permissionregistrar' => 'Spatie\\Permission\\PermissionRegistrar',
            'hasroles' => 'Spatie\\Permission\\Traits\\HasRoles',
            'refreshespermissioncache' => 'Spatie\\Permission\\Traits\\RefreshesPermissionCache',
          ),
           'className' => 'Spatie\\Permission\\Models\\Permission',
           'functionName' => NULL,
           'templatePhpDocNodes' => 
          array (
          ),
           'parent' => NULL,
           'typeAliasesMap' => 
          array (
          ),
           'bypassTypeAliases' => false,
           'constUses' => 
          array (
          ),
           'typeAliasClassName' => NULL,
           'traitData' => NULL,
        )),
         'typeAliasesMap' => 
        array (
        ),
         'bypassTypeAliases' => false,
         'constUses' => 
        array (
        ),
         'typeAliasClassName' => NULL,
         'traitData' => NULL,
      )),
    ),
    1 => 
    array (
      'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\spatie\\laravel-permission\\src\\Models\\Permission.php' => '15d7bc7455a26c3fd6a4993ab3dc77e9bff63fd12f51344e8a52cd91ac0f6357',
      'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\composer\\..\\spatie\\laravel-permission\\src\\Traits\\HasRoles.php' => '2a592993c44a108bcce617ba904f42128ceebbddf464e87b2d40f6af986fce0f',
      'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\composer\\..\\spatie\\laravel-permission\\src\\Traits\\HasPermissions.php' => '89d1dc525802930071b0d69e4541279edee0de7e82f08b06f816d87b7fe8cd55',
      'C:\\xampp\\htdocs\\esgroup.version.2\\vendor\\composer\\..\\spatie\\laravel-permission\\src\\Traits\\RefreshesPermissionCache.php' => '370881494ee529b47bbc1faed96282b4faf9b828a35fc32ff9c766f1f4c2f69f',
    ),
  ),
));