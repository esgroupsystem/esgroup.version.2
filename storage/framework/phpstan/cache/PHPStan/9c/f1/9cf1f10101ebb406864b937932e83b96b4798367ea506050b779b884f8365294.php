<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\Employee.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Employee
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-64e234f8c13f86f3710a7bb6d092d041e6b5daaa8e466f7db860dfefb9d502eb',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Employee',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/Employee.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Employee',
    'shortName' => 'Employee',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int $id
 * @property string $full_name
 * @property-read EmployeeAsset|null $asset
 * @property-read \\Illuminate\\Database\\Eloquent\\Collection<int, EmployeeHistory> $histories
 * @property-read \\Illuminate\\Database\\Eloquent\\Collection<int, EmployeeAttachment> $attachments
 * @property-read EmployeeHistory|null $latestHistory
 * @property string|null $employee_id_permanent
 * @property string|null $employee_id
 * @property string|null $department_id
 * @property string|null $position_id
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $company
 * @property string|null $status
 * @property \\Carbon\\CarbonInterface|null $date_hired
 * @property string|null $garage
 * @property \\Carbon\\CarbonInterface|null $date_of_birth
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $emergency_name
 * @property string|null $emergency_contact
 * @property \\Carbon\\CarbonInterface|null $date_resigned
 * @property string|null $type_of_status
 * @property \\Carbon\\CarbonInterface|null $last_duty
 * @property \\Carbon\\CarbonInterface|null $clearance_date
 * @property string|null $last_pay_status
 * @property \\Carbon\\CarbonInterface|null $last_pay_date
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 43,
    'endLine' => 138,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Database\\Eloquent\\Model',
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_id_permanent\', \'employee_id\', \'full_name\', \'department_id\', \'position_id\', \'email\', \'phone_number\', \'company\', \'status\', \'date_hired\', \'garage\', \'date_of_birth\', \'address_1\', \'address_2\', \'emergency_name\', \'emergency_contact\', \'date_resigned\', \'type_of_status\', \'last_duty\', \'clearance_date\', \'last_pay_status\', \'last_pay_date\']',
          'attributes' => 
          array (
            'startLine' => 47,
            'endLine' => 70,
            'startTokenPos' => 63,
            'startFilePos' => 1646,
            'endTokenPos' => 131,
            'endFilePos' => 2167,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 47,
        'endLine' => 70,
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
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'date_hired\' => \'date\', \'date_of_birth\' => \'date\', \'date_resigned\' => \'date\', \'last_duty\' => \'date\', \'clearance_date\' => \'date\', \'last_pay_date\' => \'date\']',
          'attributes' => 
          array (
            'startLine' => 72,
            'endLine' => 79,
            'startTokenPos' => 140,
            'startFilePos' => 2194,
            'endTokenPos' => 184,
            'endFilePos' => 2404,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 72,
        'endLine' => 79,
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
      'department' => 
      array (
        'name' => 'department',
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
        'docComment' => '/** @return BelongsTo<Department, $this> */',
        'startLine' => 82,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
        'aliasName' => NULL,
      ),
      'position' => 
      array (
        'name' => 'position',
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
        'docComment' => '/** @return BelongsTo<Position, $this> */',
        'startLine' => 88,
        'endLine' => 91,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
        'aliasName' => NULL,
      ),
      'asset' => 
      array (
        'name' => 'asset',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return HasOne<EmployeeAsset, $this> */',
        'startLine' => 94,
        'endLine' => 97,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
        'aliasName' => NULL,
      ),
      'histories' => 
      array (
        'name' => 'histories',
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
        'docComment' => '/** @return HasMany<EmployeeHistory, $this> */',
        'startLine' => 100,
        'endLine' => 103,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
        'aliasName' => NULL,
      ),
      'attachments' => 
      array (
        'name' => 'attachments',
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
        'docComment' => '/** @return HasMany<EmployeeAttachment, $this> */',
        'startLine' => 106,
        'endLine' => 109,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
        'aliasName' => NULL,
      ),
      'driverLeaves' => 
      array (
        'name' => 'driverLeaves',
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
        'docComment' => '/** @return HasMany<DriverLeave, $this> */',
        'startLine' => 112,
        'endLine' => 115,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
        'aliasName' => NULL,
      ),
      'logs' => 
      array (
        'name' => 'logs',
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
        'docComment' => NULL,
        'startLine' => 117,
        'endLine' => 120,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
        'aliasName' => NULL,
      ),
      'claims' => 
      array (
        'name' => 'claims',
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
        'docComment' => NULL,
        'startLine' => 122,
        'endLine' => 125,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
        'aliasName' => NULL,
      ),
      'employeeLeaves' => 
      array (
        'name' => 'employeeLeaves',
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
        'docComment' => '/** @return HasMany<EmployeeLeave, $this> */',
        'startLine' => 128,
        'endLine' => 131,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
        'aliasName' => NULL,
      ),
      'latestHistory' => 
      array (
        'name' => 'latestHistory',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Relations\\HasOne',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** @return HasOne<EmployeeHistory, $this> */',
        'startLine' => 134,
        'endLine' => 137,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Employee',
        'implementingClassName' => 'App\\Models\\Employee',
        'currentClassName' => 'App\\Models\\Employee',
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