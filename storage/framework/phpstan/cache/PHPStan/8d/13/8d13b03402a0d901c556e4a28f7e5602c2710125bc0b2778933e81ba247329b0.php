<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/EmployeeLeave.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\EmployeeLeave
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-0d4c6a98d7f292ec8fe6db8986070878e81db3c715858af3249baa66835fdbb6',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\EmployeeLeave',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/EmployeeLeave.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\EmployeeLeave',
    'shortName' => 'EmployeeLeave',
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
    'startLine' => 32,
    'endLine' => 80,
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
        'declaringClassName' => 'App\\Models\\EmployeeLeave',
        'implementingClassName' => 'App\\Models\\EmployeeLeave',
        'name' => 'table',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'employee_leaves\'',
          'attributes' => 
          array (
            'startLine' => 34,
            'endLine' => 34,
            'startTokenPos' => 43,
            'startFilePos' => 1138,
            'endTokenPos' => 43,
            'endFilePos' => 1154,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 34,
        'endLine' => 34,
        'startColumn' => 5,
        'endColumn' => 41,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\EmployeeLeave',
        'implementingClassName' => 'App\\Models\\EmployeeLeave',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'employee_id\', \'leave_type\', \'start_date\', \'end_date\', \'days\', \'reason\', \'offense_level\', \'first_notice_sent_at\', \'first_notice_proof\', \'second_notice_sent_at\', \'second_notice_proof\', \'final_notice_sent_at\', \'final_notice_proof\', \'status\', \'last_action_note\', \'ready_for_duty_notified_at\']',
          'attributes' => 
          array (
            'startLine' => 36,
            'endLine' => 53,
            'startTokenPos' => 52,
            'startFilePos' => 1184,
            'endTokenPos' => 102,
            'endFilePos' => 1608,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 36,
        'endLine' => 53,
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
        'declaringClassName' => 'App\\Models\\EmployeeLeave',
        'implementingClassName' => 'App\\Models\\EmployeeLeave',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'first_notice_sent_at\' => \'datetime\', \'second_notice_sent_at\' => \'datetime\', \'final_notice_sent_at\' => \'datetime\', \'ready_for_duty_notified_at\' => \'datetime\', \'start_date\' => \'date\', \'end_date\' => \'date\', \'days\' => \'integer\', \'offense_level\' => \'integer\']',
          'attributes' => 
          array (
            'startLine' => 55,
            'endLine' => 64,
            'startTokenPos' => 111,
            'startFilePos' => 1635,
            'endTokenPos' => 169,
            'endFilePos' => 1961,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 55,
        'endLine' => 64,
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
        'startLine' => 67,
        'endLine' => 70,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeeLeave',
        'implementingClassName' => 'App\\Models\\EmployeeLeave',
        'currentClassName' => 'App\\Models\\EmployeeLeave',
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
        'startLine' => 72,
        'endLine' => 79,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\EmployeeLeave',
        'implementingClassName' => 'App\\Models\\EmployeeLeave',
        'currentClassName' => 'App\\Models\\EmployeeLeave',
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