<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\Claim.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Claim
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-6d56eecae4bbac57604521d19cad5b53fe9d907cec062718ac2cc3e6c77f2de6',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Claim',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/Claim.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Claim',
    'shortName' => 'Claim',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $employee_id
 * @property string|null $claim_type
 * @property string|null $status
 * @property string|null $reference_no
 * @property \\Carbon\\CarbonInterface|null $date_of_notification
 * @property \\Carbon\\CarbonInterface|null $date_filed
 * @property \\Carbon\\CarbonInterface|null $approval_date
 * @property \\Carbon\\CarbonInterface|null $fund_request_date
 * @property \\Carbon\\CarbonInterface|null $fund_released_date
 * @property string|null $amount
 * @property string|null $remarks
 * @property string|null $created_by
 * @property string|null $updated_by
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 25,
    'endLine' => 68,
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
        'declaringClassName' => 'App\\Models\\Claim',
        'implementingClassName' => 'App\\Models\\Claim',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_id\', \'claim_type\', \'status\', \'reference_no\', \'date_of_notification\', \'date_filed\', \'approval_date\', \'fund_request_date\', \'fund_released_date\', \'amount\', \'remarks\', \'created_by\', \'updated_by\']',
          'attributes' => 
          array (
            'startLine' => 27,
            'endLine' => 41,
            'startTokenPos' => 43,
            'startFilePos' => 799,
            'endTokenPos' => 84,
            'endFilePos' => 1111,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 27,
        'endLine' => 41,
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
        'declaringClassName' => 'App\\Models\\Claim',
        'implementingClassName' => 'App\\Models\\Claim',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'date_of_notification\' => \'date\', \'date_filed\' => \'date\', \'approval_date\' => \'date\', \'fund_request_date\' => \'date\', \'fund_released_date\' => \'date\']',
          'attributes' => 
          array (
            'startLine' => 43,
            'endLine' => 49,
            'startTokenPos' => 93,
            'startFilePos' => 1138,
            'endTokenPos' => 130,
            'endFilePos' => 1332,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 43,
        'endLine' => 49,
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
      'employee' => 
      array (
        'name' => 'employee',
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
        'docComment' => '/** @return BelongsTo<Employee, $this> */',
        'startLine' => 52,
        'endLine' => 55,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Claim',
        'implementingClassName' => 'App\\Models\\Claim',
        'currentClassName' => 'App\\Models\\Claim',
        'aliasName' => NULL,
      ),
      'creator' => 
      array (
        'name' => 'creator',
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
        'docComment' => '/** @return BelongsTo<User, $this> */',
        'startLine' => 58,
        'endLine' => 61,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Claim',
        'implementingClassName' => 'App\\Models\\Claim',
        'currentClassName' => 'App\\Models\\Claim',
        'aliasName' => NULL,
      ),
      'updater' => 
      array (
        'name' => 'updater',
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
        'docComment' => '/** @return BelongsTo<User, $this> */',
        'startLine' => 64,
        'endLine' => 67,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Claim',
        'implementingClassName' => 'App\\Models\\Claim',
        'currentClassName' => 'App\\Models\\Claim',
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