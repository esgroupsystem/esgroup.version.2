<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\DriverLeave.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\DriverLeave
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-bb102b776df99e5dd54231b0ec2fbbc137a22e3dc0c548106c3ae83fa70b5dc8',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\DriverLeave',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/DriverLeave.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\DriverLeave',
    'shortName' => 'DriverLeave',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $employee_id
 * @property string|null $leave_type
 * @property \\Carbon\\CarbonInterface|null $start_date
 * @property \\Carbon\\CarbonInterface|null $end_date
 * @property int|null $days
 * @property string|null $reason
 * @property int|null $offense_level
 * @property \\Carbon\\CarbonInterface|null $first_notice_sent_at
 * @property string|null $first_notice_proof
 * @property \\Carbon\\CarbonInterface|null $second_notice_sent_at
 * @property string|null $second_notice_proof
 * @property \\Carbon\\CarbonInterface|null $final_notice_sent_at
 * @property string|null $final_notice_proof
 * @property string|null $status
 * @property string|null $last_action_note
 * @property \\Carbon\\CarbonInterface|null $ready_for_duty_notified_at
 * @property string|null $record_status_badge
 * @property string|null $remaining_status
 * @property string|null $level_label
 * @property string|null $status_label
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 33,
    'endLine' => 83,
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
      'table' => 
      array (
        'declaringClassName' => 'App\\Models\\DriverLeave',
        'implementingClassName' => 'App\\Models\\DriverLeave',
        'name' => 'table',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'driver_leaves\'',
          'attributes' => 
          array (
            'startLine' => 35,
            'endLine' => 35,
            'startTokenPos' => 48,
            'startFilePos' => 1163,
            'endTokenPos' => 48,
            'endFilePos' => 1177,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 35,
        'endLine' => 35,
        'startColumn' => 5,
        'endColumn' => 39,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\DriverLeave',
        'implementingClassName' => 'App\\Models\\DriverLeave',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_id\', \'leave_type\', \'start_date\', \'end_date\', \'days\', \'reason\', \'offense_level\', \'first_notice_sent_at\', \'first_notice_proof\', \'second_notice_sent_at\', \'second_notice_proof\', \'final_notice_sent_at\', \'final_notice_proof\', \'status\', \'last_action_note\', \'ready_for_duty_notified_at\']',
          'attributes' => 
          array (
            'startLine' => 37,
            'endLine' => 58,
            'startTokenPos' => 57,
            'startFilePos' => 1207,
            'endTokenPos' => 107,
            'endFilePos' => 1635,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 37,
        'endLine' => 58,
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
        'declaringClassName' => 'App\\Models\\DriverLeave',
        'implementingClassName' => 'App\\Models\\DriverLeave',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'first_notice_sent_at\' => \'datetime\', \'second_notice_sent_at\' => \'datetime\', \'final_notice_sent_at\' => \'datetime\', \'ready_for_duty_notified_at\' => \'datetime\', \'start_date\' => \'date\', \'end_date\' => \'date\', \'days\' => \'integer\', \'offense_level\' => \'integer\']',
          'attributes' => 
          array (
            'startLine' => 60,
            'endLine' => 69,
            'startTokenPos' => 116,
            'startFilePos' => 1662,
            'endTokenPos' => 174,
            'endFilePos' => 1988,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 60,
        'endLine' => 69,
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
        'startLine' => 72,
        'endLine' => 75,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\DriverLeave',
        'implementingClassName' => 'App\\Models\\DriverLeave',
        'currentClassName' => 'App\\Models\\DriverLeave',
        'aliasName' => NULL,
      ),
      'isClosed' => 
      array (
        'name' => 'isClosed',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 77,
        'endLine' => 82,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\DriverLeave',
        'implementingClassName' => 'App\\Models\\DriverLeave',
        'currentClassName' => 'App\\Models\\DriverLeave',
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