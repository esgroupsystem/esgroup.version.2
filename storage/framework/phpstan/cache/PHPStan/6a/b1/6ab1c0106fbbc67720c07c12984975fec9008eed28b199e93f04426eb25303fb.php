<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\Holiday.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Holiday
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-bb4aac0b5dce21800a0a197c15d4c5e7b9f9ac6d66f5a9eb5b5baabdacc46fb3',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Holiday',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/Holiday.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Holiday',
    'shortName' => 'Holiday',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $name
 * @property \\Carbon\\CarbonInterface|null $actual_date
 * @property \\Carbon\\CarbonInterface|null $observed_date
 * @property string|null $holiday_type
 * @property bool|null $is_moved
 * @property string|float|int $not_worked_multiplier
 * @property string|float|int $worked_multiplier
 * @property string|null $source_proclamation
 * @property string|null $notes
 * @property bool|null $is_active
 * @property-read mixed $type_badge_class
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 23,
    'endLine' => 86,
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
      'TYPE_REGULAR' => 
      array (
        'declaringClassName' => 'App\\Models\\Holiday',
        'implementingClassName' => 'App\\Models\\Holiday',
        'name' => 'TYPE_REGULAR',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'regular\'',
          'attributes' => 
          array (
            'startLine' => 25,
            'endLine' => 25,
            'startTokenPos' => 45,
            'startFilePos' => 657,
            'endTokenPos' => 45,
            'endFilePos' => 665,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 25,
        'endLine' => 25,
        'startColumn' => 5,
        'endColumn' => 42,
      ),
      'TYPE_SPECIAL' => 
      array (
        'declaringClassName' => 'App\\Models\\Holiday',
        'implementingClassName' => 'App\\Models\\Holiday',
        'name' => 'TYPE_SPECIAL',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'special\'',
          'attributes' => 
          array (
            'startLine' => 27,
            'endLine' => 27,
            'startTokenPos' => 56,
            'startFilePos' => 701,
            'endTokenPos' => 56,
            'endFilePos' => 709,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 27,
        'endLine' => 27,
        'startColumn' => 5,
        'endColumn' => 42,
      ),
      'STANDARD_MULTIPLIERS' => 
      array (
        'declaringClassName' => 'App\\Models\\Holiday',
        'implementingClassName' => 'App\\Models\\Holiday',
        'name' => 'STANDARD_MULTIPLIERS',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[self::TYPE_REGULAR => [\'not_worked_multiplier\' => 1.0, \'worked_multiplier\' => 2.0], self::TYPE_SPECIAL => [\'not_worked_multiplier\' => 0.0, \'worked_multiplier\' => 1.3]]',
          'attributes' => 
          array (
            'startLine' => 29,
            'endLine' => 38,
            'startTokenPos' => 67,
            'startFilePos' => 753,
            'endTokenPos' => 119,
            'endFilePos' => 1017,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 29,
        'endLine' => 38,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\Holiday',
        'implementingClassName' => 'App\\Models\\Holiday',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'name\', \'actual_date\', \'observed_date\', \'holiday_type\', \'is_moved\', \'not_worked_multiplier\', \'worked_multiplier\', \'source_proclamation\', \'notes\', \'is_active\']',
          'attributes' => 
          array (
            'startLine' => 40,
            'endLine' => 51,
            'startTokenPos' => 128,
            'startFilePos' => 1047,
            'endTokenPos' => 160,
            'endFilePos' => 1292,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 40,
        'endLine' => 51,
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
        'declaringClassName' => 'App\\Models\\Holiday',
        'implementingClassName' => 'App\\Models\\Holiday',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'actual_date\' => \'date\', \'observed_date\' => \'date\', \'is_moved\' => \'boolean\', \'is_active\' => \'boolean\', \'not_worked_multiplier\' => \'decimal:2\', \'worked_multiplier\' => \'decimal:2\']',
          'attributes' => 
          array (
            'startLine' => 53,
            'endLine' => 60,
            'startTokenPos' => 169,
            'startFilePos' => 1319,
            'endTokenPos' => 213,
            'endFilePos' => 1552,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 53,
        'endLine' => 60,
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
      'scopeActive' => 
      array (
        'name' => 'scopeActive',
        'parameters' => 
        array (
          'query' => 
          array (
            'name' => 'query',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 62,
            'endLine' => 62,
            'startColumn' => 33,
            'endColumn' => 38,
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
        'startLine' => 62,
        'endLine' => 65,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Holiday',
        'implementingClassName' => 'App\\Models\\Holiday',
        'currentClassName' => 'App\\Models\\Holiday',
        'aliasName' => NULL,
      ),
      'scopeOnDate' => 
      array (
        'name' => 'scopeOnDate',
        'parameters' => 
        array (
          'query' => 
          array (
            'name' => 'query',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 67,
            'endLine' => 67,
            'startColumn' => 33,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'date' => 
          array (
            'name' => 'date',
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
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'Carbon\\Carbon',
                      'isIdentifier' => false,
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
            'startLine' => 67,
            'endLine' => 67,
            'startColumn' => 41,
            'endColumn' => 59,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 67,
        'endLine' => 72,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Holiday',
        'implementingClassName' => 'App\\Models\\Holiday',
        'currentClassName' => 'App\\Models\\Holiday',
        'aliasName' => NULL,
      ),
      'standardMultipliers' => 
      array (
        'name' => 'standardMultipliers',
        'parameters' => 
        array (
          'type' => 
          array (
            'name' => 'type',
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
            'startLine' => 74,
            'endLine' => 74,
            'startColumn' => 48,
            'endColumn' => 59,
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
        'startLine' => 74,
        'endLine' => 78,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Holiday',
        'implementingClassName' => 'App\\Models\\Holiday',
        'currentClassName' => 'App\\Models\\Holiday',
        'aliasName' => NULL,
      ),
      'getTypeBadgeClassAttribute' => 
      array (
        'name' => 'getTypeBadgeClassAttribute',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 80,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Holiday',
        'implementingClassName' => 'App\\Models\\Holiday',
        'currentClassName' => 'App\\Models\\Holiday',
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