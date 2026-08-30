<?php declare(strict_types = 1);

// odsl-/home/lenberd/Documents/esgroup.version.2/app/Models/PayrollBenefitSettlement.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\PayrollBenefitSettlement
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.3.6-76f50215d4c3cba09f024142bc4071b9ec576532386619fead54d52a508505c8',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\PayrollBenefitSettlement',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/app/Models/PayrollBenefitSettlement.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\PayrollBenefitSettlement',
    'shortName' => 'PayrollBenefitSettlement',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property int|null $payroll_id
 * @property int|null $payroll_item_id
 * @property int|null $employee_biometric_id
 * @property string|null $mode
 * @property string|float|int $sss_employee_reimbursement
 * @property string|float|int $philhealth_employee_reimbursement
 * @property string|float|int $pagibig_employee_reimbursement
 * @property string|null $reason
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property array<string, mixed>|null $meta
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 23,
    'endLine' => 92,
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
      'MODE_AUTO_CAP' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'name' => 'MODE_AUTO_CAP',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'auto_cap\'',
          'attributes' => 
          array (
            'startLine' => 25,
            'endLine' => 25,
            'startTokenPos' => 45,
            'startFilePos' => 719,
            'endTokenPos' => 45,
            'endFilePos' => 728,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 25,
        'endLine' => 25,
        'startColumn' => 5,
        'endColumn' => 44,
      ),
      'MODE_EMPLOYER_ADVANCE' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'name' => 'MODE_EMPLOYER_ADVANCE',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'employer_advance\'',
          'attributes' => 
          array (
            'startLine' => 27,
            'endLine' => 27,
            'startTokenPos' => 56,
            'startFilePos' => 773,
            'endTokenPos' => 56,
            'endFilePos' => 790,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 27,
        'endLine' => 27,
        'startColumn' => 5,
        'endColumn' => 60,
      ),
      'MODE_COLLECT_FULL' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'name' => 'MODE_COLLECT_FULL',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'collect_full\'',
          'attributes' => 
          array (
            'startLine' => 29,
            'endLine' => 29,
            'startTokenPos' => 67,
            'startFilePos' => 831,
            'endTokenPos' => 67,
            'endFilePos' => 844,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 29,
        'endLine' => 29,
        'startColumn' => 5,
        'endColumn' => 52,
      ),
      'MODES' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'name' => 'MODES',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '[self::MODE_AUTO_CAP, self::MODE_EMPLOYER_ADVANCE, self::MODE_COLLECT_FULL]',
          'attributes' => 
          array (
            'startLine' => 31,
            'endLine' => 35,
            'startTokenPos' => 78,
            'startFilePos' => 873,
            'endTokenPos' => 95,
            'endFilePos' => 978,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 31,
        'endLine' => 35,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
    ),
    'immediateProperties' => 
    array (
      'fillable' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'payroll_id\', \'payroll_item_id\', \'employee_biometric_id\', \'mode\', \'sss_employee_reimbursement\', \'philhealth_employee_reimbursement\', \'pagibig_employee_reimbursement\', \'reason\', \'created_by\', \'updated_by\', \'meta\']',
          'attributes' => 
          array (
            'startLine' => 37,
            'endLine' => 49,
            'startTokenPos' => 104,
            'startFilePos' => 1008,
            'endTokenPos' => 139,
            'endFilePos' => 1315,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 37,
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
      'casts' => 
      array (
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'payroll_id\' => \'integer\', \'payroll_item_id\' => \'integer\', \'employee_biometric_id\' => \'integer\', \'sss_employee_reimbursement\' => \'decimal:2\', \'philhealth_employee_reimbursement\' => \'decimal:2\', \'pagibig_employee_reimbursement\' => \'decimal:2\', \'created_by\' => \'integer\', \'updated_by\' => \'integer\', \'meta\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 51,
            'endLine' => 61,
            'startTokenPos' => 148,
            'startFilePos' => 1342,
            'endTokenPos' => 213,
            'endFilePos' => 1736,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 51,
        'endLine' => 61,
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
      'payroll' => 
      array (
        'name' => 'payroll',
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
        'docComment' => '/** @return BelongsTo<Payroll, $this> */',
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
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'currentClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'aliasName' => NULL,
      ),
      'payrollItem' => 
      array (
        'name' => 'payrollItem',
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
        'docComment' => '/** @return BelongsTo<PayrollItem, $this> */',
        'startLine' => 70,
        'endLine' => 73,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'currentClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'aliasName' => NULL,
      ),
      'employeeBiometric' => 
      array (
        'name' => 'employeeBiometric',
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
        'docComment' => '/** @return BelongsTo<EmployeeBiometric, $this> */',
        'startLine' => 76,
        'endLine' => 79,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'currentClassName' => 'App\\Models\\PayrollBenefitSettlement',
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
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'currentClassName' => 'App\\Models\\PayrollBenefitSettlement',
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
        'declaringClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'implementingClassName' => 'App\\Models\\PayrollBenefitSettlement',
        'currentClassName' => 'App\\Models\\PayrollBenefitSettlement',
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