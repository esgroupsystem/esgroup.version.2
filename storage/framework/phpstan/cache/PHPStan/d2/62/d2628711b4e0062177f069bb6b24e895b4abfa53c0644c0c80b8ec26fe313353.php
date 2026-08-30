<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/Payroll.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\Payroll
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-0da946fa15f02bae46e5bf4522f42ed9e67bdf1d889c241b3dddf603842551aa',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\Payroll',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/Payroll.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\Payroll',
    'shortName' => 'Payroll',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $payroll_number
 * @property int|null $cutoff_month
 * @property int|null $cutoff_year
 * @property string|null $cutoff_type
 * @property int|string|null $garage_group
 * @property int|null $contribution_month
 * @property int|null $contribution_year
 * @property \\Carbon\\CarbonInterface|null $period_start
 * @property \\Carbon\\CarbonInterface|null $period_end
 * @property string|null $status
 * @property-read int|string|null $garage_group
 * @property-read string $cutoff_label
 * @property-read string $contribution_label
 * @property-read string $garage_group_label
 * @property-read \\Illuminate\\Database\\Eloquent\\Collection<int, PayrollItem> $items
 * @property-read User|null $generator
 * @property-read User|null $finalizer
 * @property string|null $remarks
 * @property string|null $generated_by
 * @property \\Carbon\\CarbonInterface|null $generated_at
 * @property string|null $finalized_by
 * @property \\Carbon\\CarbonInterface|null $finalized_at
 * @property array<string, mixed>|null $meta
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 38,
    'endLine' => 148,
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
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'payroll_number\', \'cutoff_month\', \'cutoff_year\', \'cutoff_type\', \'garage_group\', \'contribution_month\', \'contribution_year\', \'period_start\', \'period_end\', \'remarks\', \'generated_by\', \'generated_at\', \'finalized_by\', \'finalized_at\', \'status\', \'meta\']',
          'attributes' => 
          array (
            'startLine' => 40,
            'endLine' => 57,
            'startTokenPos' => 53,
            'startFilePos' => 1345,
            'endTokenPos' => 103,
            'endFilePos' => 1725,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 40,
        'endLine' => 57,
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
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'period_start\' => \'date\', \'period_end\' => \'date\', \'generated_at\' => \'datetime\', \'finalized_at\' => \'datetime\', \'meta\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 59,
            'endLine' => 65,
            'startTokenPos' => 112,
            'startFilePos' => 1752,
            'endTokenPos' => 149,
            'endFilePos' => 1927,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 59,
        'endLine' => 65,
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
      'items' => 
      array (
        'name' => 'items',
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
        'docComment' => '/** @return HasMany<PayrollItem, $this> */',
        'startLine' => 68,
        'endLine' => 71,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'currentClassName' => 'App\\Models\\Payroll',
        'aliasName' => NULL,
      ),
      'paymentLogs' => 
      array (
        'name' => 'paymentLogs',
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
        'docComment' => '/** @return HasMany<PaymentLog, $this> */',
        'startLine' => 74,
        'endLine' => 77,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'currentClassName' => 'App\\Models\\Payroll',
        'aliasName' => NULL,
      ),
      'reportLogs' => 
      array (
        'name' => 'reportLogs',
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
        'docComment' => '/** @return HasMany<PayrollReportLog, $this> */',
        'startLine' => 80,
        'endLine' => 83,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'currentClassName' => 'App\\Models\\Payroll',
        'aliasName' => NULL,
      ),
      'benefitContributionRecords' => 
      array (
        'name' => 'benefitContributionRecords',
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
        'docComment' => '/** @return HasMany<BenefitContributionRecord, $this> */',
        'startLine' => 86,
        'endLine' => 89,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'currentClassName' => 'App\\Models\\Payroll',
        'aliasName' => NULL,
      ),
      'generator' => 
      array (
        'name' => 'generator',
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
        'startLine' => 92,
        'endLine' => 95,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'currentClassName' => 'App\\Models\\Payroll',
        'aliasName' => NULL,
      ),
      'finalizer' => 
      array (
        'name' => 'finalizer',
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
        'startLine' => 98,
        'endLine' => 101,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'currentClassName' => 'App\\Models\\Payroll',
        'aliasName' => NULL,
      ),
      'cutoffLabel' => 
      array (
        'name' => 'cutoffLabel',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Casts\\Attribute',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 103,
        'endLine' => 127,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'currentClassName' => 'App\\Models\\Payroll',
        'aliasName' => NULL,
      ),
      'contributionLabel' => 
      array (
        'name' => 'contributionLabel',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Database\\Eloquent\\Casts\\Attribute',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 129,
        'endLine' => 138,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'currentClassName' => 'App\\Models\\Payroll',
        'aliasName' => NULL,
      ),
      'getGarageGroupLabelAttribute' => 
      array (
        'name' => 'getGarageGroupLabelAttribute',
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
        'startLine' => 140,
        'endLine' => 147,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\Payroll',
        'implementingClassName' => 'App\\Models\\Payroll',
        'currentClassName' => 'App\\Models\\Payroll',
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