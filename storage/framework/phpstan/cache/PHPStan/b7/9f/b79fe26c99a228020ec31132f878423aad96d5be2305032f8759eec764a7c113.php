<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\OdometerSubmission.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\OdometerSubmission
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-cc72efdfab066b918a06da800e2c7389422884c7582711bdd5e6252af7317f43',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\OdometerSubmission',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/OdometerSubmission.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\OdometerSubmission',
    'shortName' => 'OdometerSubmission',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $user_id
 * @property string|null $bus_detail_id
 * @property string|null $new_odometer
 * @property string|null $driver_name
 * @property string|null $diesel_consumption
 * @property string|null $date_bus_deployed
 * @property string|null $date
 * @property string|null $time
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 20,
    'endLine' => 38,
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
        'declaringClassName' => 'App\\Models\\OdometerSubmission',
        'implementingClassName' => 'App\\Models\\OdometerSubmission',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'user_id\', \'bus_detail_id\', \'new_odometer\', \'driver_name\', \'diesel_consumption\', \'date_bus_deployed\', \'date\', \'time\']',
          'attributes' => 
          array (
            'startLine' => 22,
            'endLine' => 31,
            'startTokenPos' => 43,
            'startFilePos' => 528,
            'endTokenPos' => 69,
            'endFilePos' => 716,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 22,
        'endLine' => 31,
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
      'busDetail' => 
      array (
        'name' => 'busDetail',
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
        'docComment' => '/** @return BelongsTo<BusDetail, $this> */',
        'startLine' => 34,
        'endLine' => 37,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\OdometerSubmission',
        'implementingClassName' => 'App\\Models\\OdometerSubmission',
        'currentClassName' => 'App\\Models\\OdometerSubmission',
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