<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Models\BenefitContributionRecord.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Models\BenefitContributionRecord
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-239eaf95927f77f8390f25ee68114c8f7bd6eead0d86eb9aa3cd42c2f20cfbc9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Models\\BenefitContributionRecord',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Models/BenefitContributionRecord.php',
      ),
    ),
    'namespace' => 'App\\Models',
    'name' => 'App\\Models\\BenefitContributionRecord',
    'shortName' => 'BenefitContributionRecord',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property string|null $monthly_key
 * @property int|null $payroll_id
 * @property int|null $payroll_item_id
 * @property int|null $employee_biometric_id
 * @property int|null $employee_id
 * @property int|null $payroll_employee_salary_id
 * @property int|null $posted_by
 * @property int|null $garage_group
 * @property int|null $contribution_month
 * @property int|null $contribution_year
 * @property \\Carbon\\CarbonInterface|null $period_start
 * @property \\Carbon\\CarbonInterface|null $period_end
 * @property string|null $payroll_number
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string|null $company_name
 * @property string|null $sss_number
 * @property string|null $philhealth_number
 * @property string|null $pagibig_number
 * @property string|float|int $gross_compensation
 * @property string|float|int $business_first_cutoff_gross
 * @property string|float|int $business_second_cutoff_gross
 * @property string|float|int $monthly_basic_salary
 * @property string|float|int $sss_compensation_basis
 * @property string|float|int $sss_compensation_range_minimum
 * @property string|float|int $sss_compensation_range_maximum
 * @property string|float|int $sss_msc
 * @property string|float|int $sss_regular_ss_msc
 * @property string|float|int $sss_mpf_msc
 * @property string|float|int $sss_employee_regular_ss
 * @property string|float|int $sss_employee_mpf
 * @property string|float|int $sss_employee_total
 * @property string|float|int $sss_employee_collected
 * @property string|float|int $sss_employer_regular_ss
 * @property string|float|int $sss_employer_mpf
 * @property string|float|int $sss_employer_ec
 * @property string|float|int $sss_employer_total
 * @property string|float|int $sss_total_contribution
 * @property string|float|int $philhealth_basis
 * @property string|float|int $philhealth_salary_base
 * @property string|float|int $philhealth_premium_rate
 * @property string|float|int $philhealth_employee
 * @property string|float|int $philhealth_employee_collected
 * @property string|float|int $philhealth_employer
 * @property string|float|int $philhealth_total
 * @property string|float|int $pagibig_basis
 * @property string|float|int $pagibig_fund_salary
 * @property string|float|int $pagibig_employee_rate
 * @property string|float|int $pagibig_employer_rate
 * @property string|float|int $pagibig_employee
 * @property string|float|int $pagibig_employee_collected
 * @property string|float|int $pagibig_employer
 * @property string|float|int $pagibig_total
 * @property string|float|int $employee_total
 * @property string|float|int $employer_total
 * @property string|float|int $grand_total
 * @property string|float|int $employee_share_unrecovered
 * @property string|null $settlement_status
 * @property array<string, mixed>|null $settlement_meta
 * @property \\Carbon\\CarbonInterface|null $posted_at
 * @property array<string, mixed>|null $meta
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 73,
    'endLine' => 229,
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
        'declaringClassName' => 'App\\Models\\BenefitContributionRecord',
        'implementingClassName' => 'App\\Models\\BenefitContributionRecord',
        'name' => 'fillable',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'monthly_key\', \'payroll_id\', \'payroll_item_id\', \'employee_biometric_id\', \'employee_id\', \'payroll_employee_salary_id\', \'posted_by\', \'garage_group\', \'contribution_month\', \'contribution_year\', \'period_start\', \'period_end\', \'payroll_number\', \'employee_no\', \'employee_name\', \'company_name\', \'sss_number\', \'philhealth_number\', \'pagibig_number\', \'gross_compensation\', \'business_first_cutoff_gross\', \'business_second_cutoff_gross\', \'monthly_basic_salary\', \'sss_compensation_basis\', \'sss_compensation_range_minimum\', \'sss_compensation_range_maximum\', \'sss_msc\', \'sss_regular_ss_msc\', \'sss_mpf_msc\', \'sss_employee_regular_ss\', \'sss_employee_mpf\', \'sss_employee_total\', \'sss_employee_collected\', \'sss_employer_regular_ss\', \'sss_employer_mpf\', \'sss_employer_ec\', \'sss_employer_total\', \'sss_total_contribution\', \'philhealth_basis\', \'philhealth_salary_base\', \'philhealth_premium_rate\', \'philhealth_employee\', \'philhealth_employee_collected\', \'philhealth_employer\', \'philhealth_total\', \'pagibig_basis\', \'pagibig_fund_salary\', \'pagibig_employee_rate\', \'pagibig_employer_rate\', \'pagibig_employee\', \'pagibig_employee_collected\', \'pagibig_employer\', \'pagibig_total\', \'employee_total\', \'employer_total\', \'grand_total\', \'employee_share_unrecovered\', \'settlement_status\', \'settlement_meta\', \'posted_at\', \'meta\']',
          'attributes' => 
          array (
            'startLine' => 75,
            'endLine' => 137,
            'startTokenPos' => 43,
            'startFilePos' => 3168,
            'endTokenPos' => 228,
            'endFilePos' => 4952,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 75,
        'endLine' => 137,
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
        'declaringClassName' => 'App\\Models\\BenefitContributionRecord',
        'implementingClassName' => 'App\\Models\\BenefitContributionRecord',
        'name' => 'casts',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'payroll_id\' => \'integer\', \'payroll_item_id\' => \'integer\', \'employee_biometric_id\' => \'integer\', \'employee_id\' => \'integer\', \'payroll_employee_salary_id\' => \'integer\', \'posted_by\' => \'integer\', \'garage_group\' => \'integer\', \'contribution_month\' => \'integer\', \'contribution_year\' => \'integer\', \'period_start\' => \'date\', \'period_end\' => \'date\', \'gross_compensation\' => \'decimal:2\', \'business_first_cutoff_gross\' => \'decimal:2\', \'business_second_cutoff_gross\' => \'decimal:2\', \'monthly_basic_salary\' => \'decimal:2\', \'sss_compensation_basis\' => \'decimal:2\', \'sss_compensation_range_minimum\' => \'decimal:2\', \'sss_compensation_range_maximum\' => \'decimal:2\', \'sss_msc\' => \'decimal:2\', \'sss_regular_ss_msc\' => \'decimal:2\', \'sss_mpf_msc\' => \'decimal:2\', \'sss_employee_regular_ss\' => \'decimal:2\', \'sss_employee_mpf\' => \'decimal:2\', \'sss_employee_total\' => \'decimal:2\', \'sss_employee_collected\' => \'decimal:2\', \'sss_employer_regular_ss\' => \'decimal:2\', \'sss_employer_mpf\' => \'decimal:2\', \'sss_employer_ec\' => \'decimal:2\', \'sss_employer_total\' => \'decimal:2\', \'sss_total_contribution\' => \'decimal:2\', \'philhealth_basis\' => \'decimal:2\', \'philhealth_salary_base\' => \'decimal:2\', \'philhealth_premium_rate\' => \'decimal:6\', \'philhealth_employee\' => \'decimal:2\', \'philhealth_employee_collected\' => \'decimal:2\', \'philhealth_employer\' => \'decimal:2\', \'philhealth_total\' => \'decimal:2\', \'pagibig_basis\' => \'decimal:2\', \'pagibig_fund_salary\' => \'decimal:2\', \'pagibig_employee_rate\' => \'decimal:6\', \'pagibig_employer_rate\' => \'decimal:6\', \'pagibig_employee\' => \'decimal:2\', \'pagibig_employee_collected\' => \'decimal:2\', \'pagibig_employer\' => \'decimal:2\', \'pagibig_total\' => \'decimal:2\', \'employee_total\' => \'decimal:2\', \'employer_total\' => \'decimal:2\', \'grand_total\' => \'decimal:2\', \'employee_share_unrecovered\' => \'decimal:2\', \'settlement_meta\' => \'array\', \'posted_at\' => \'datetime\', \'meta\' => \'array\']',
          'attributes' => 
          array (
            'startLine' => 139,
            'endLine' => 192,
            'startTokenPos' => 237,
            'startFilePos' => 4979,
            'endTokenPos' => 603,
            'endFilePos' => 7279,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 139,
        'endLine' => 192,
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
        'startLine' => 195,
        'endLine' => 198,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BenefitContributionRecord',
        'implementingClassName' => 'App\\Models\\BenefitContributionRecord',
        'currentClassName' => 'App\\Models\\BenefitContributionRecord',
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
        'startLine' => 201,
        'endLine' => 204,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BenefitContributionRecord',
        'implementingClassName' => 'App\\Models\\BenefitContributionRecord',
        'currentClassName' => 'App\\Models\\BenefitContributionRecord',
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
        'startLine' => 207,
        'endLine' => 210,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BenefitContributionRecord',
        'implementingClassName' => 'App\\Models\\BenefitContributionRecord',
        'currentClassName' => 'App\\Models\\BenefitContributionRecord',
        'aliasName' => NULL,
      ),
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
        'startLine' => 213,
        'endLine' => 216,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BenefitContributionRecord',
        'implementingClassName' => 'App\\Models\\BenefitContributionRecord',
        'currentClassName' => 'App\\Models\\BenefitContributionRecord',
        'aliasName' => NULL,
      ),
      'salaryProfile' => 
      array (
        'name' => 'salaryProfile',
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
        'docComment' => '/** @return BelongsTo<PayrollEmployeeSalary, $this> */',
        'startLine' => 219,
        'endLine' => 222,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BenefitContributionRecord',
        'implementingClassName' => 'App\\Models\\BenefitContributionRecord',
        'currentClassName' => 'App\\Models\\BenefitContributionRecord',
        'aliasName' => NULL,
      ),
      'postedBy' => 
      array (
        'name' => 'postedBy',
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
        'startLine' => 225,
        'endLine' => 228,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Models',
        'declaringClassName' => 'App\\Models\\BenefitContributionRecord',
        'implementingClassName' => 'App\\Models\\BenefitContributionRecord',
        'currentClassName' => 'App\\Models\\BenefitContributionRecord',
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