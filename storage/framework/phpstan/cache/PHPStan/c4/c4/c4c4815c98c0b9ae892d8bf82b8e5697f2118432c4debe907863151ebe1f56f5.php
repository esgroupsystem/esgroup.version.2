<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Services\Payroll\PayrollEmployeeRosterService.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Services\Payroll\PayrollEmployeeRosterService
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-5ad55bc959178447f21232837b08fc724aade18f77d65d8cd8b1600dfdb81ddc',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Services\\Payroll\\PayrollEmployeeRosterService',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Services/Payroll/PayrollEmployeeRosterService.php',
      ),
    ),
    'namespace' => 'App\\Services\\Payroll',
    'name' => 'App\\Services\\Payroll\\PayrollEmployeeRosterService',
    'shortName' => 'PayrollEmployeeRosterService',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 11,
    'endLine' => 39,
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
    ),
    'immediateMethods' => 
    array (
      'queryForGroup' => 
      array (
        'name' => 'queryForGroup',
        'parameters' => 
        array (
          'groupName' => 
          array (
            'name' => 'groupName',
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
                      'name' => 'int',
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
            'startLine' => 26,
            'endLine' => 26,
            'startColumn' => 35,
            'endColumn' => 55,
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
            'name' => 'Illuminate\\Database\\Eloquent\\Builder',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Canonical payroll roster.
 *
 * An employee is eligible only when:
 * - employment status is Active (legacy NULL is treated as Active),
 * - Payroll Inclusion is ON (legacy NULL is treated as ON), and
 * - the employee belongs to the selected payroll group.
 *
 * Attendance summaries, plotting schedules, biometric logs and salary
 * profiles are NOT roster sources. They are downstream payroll data. This
 * prevents an otherwise eligible employee from disappearing from payroll
 * simply because one of those downstream records is missing.
 */',
        'startLine' => 26,
        'endLine' => 33,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Services\\Payroll',
        'declaringClassName' => 'App\\Services\\Payroll\\PayrollEmployeeRosterService',
        'implementingClassName' => 'App\\Services\\Payroll\\PayrollEmployeeRosterService',
        'currentClassName' => 'App\\Services\\Payroll\\PayrollEmployeeRosterService',
        'aliasName' => NULL,
      ),
      'forGroup' => 
      array (
        'name' => 'forGroup',
        'parameters' => 
        array (
          'groupName' => 
          array (
            'name' => 'groupName',
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
                      'name' => 'int',
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
            'startLine' => 35,
            'endLine' => 35,
            'startColumn' => 30,
            'endColumn' => 50,
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
            'name' => 'Illuminate\\Support\\Collection',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 35,
        'endLine' => 38,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Services\\Payroll',
        'declaringClassName' => 'App\\Services\\Payroll\\PayrollEmployeeRosterService',
        'implementingClassName' => 'App\\Services\\Payroll\\PayrollEmployeeRosterService',
        'currentClassName' => 'App\\Services\\Payroll\\PayrollEmployeeRosterService',
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