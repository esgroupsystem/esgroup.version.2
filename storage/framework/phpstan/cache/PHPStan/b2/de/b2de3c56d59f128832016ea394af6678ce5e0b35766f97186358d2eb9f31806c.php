<?php declare(strict_types = 1);

// osfsl-/home/lenberd/Documents/esgroup.version.2/vendor/composer/../laravel/sail/src/Console/Concerns/InteractsWithDockerComposeServices.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Laravel\Sail\Console\Concerns\InteractsWithDockerComposeServices
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-97b826de45403f90fdea505c7b7760cbfb9af25ea98c40a7d4d7e883236f4b8a-8.3.6-6.70.0.3',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'filename' => '/home/lenberd/Documents/esgroup.version.2/vendor/composer/../laravel/sail/src/Console/Concerns/InteractsWithDockerComposeServices.php',
      ),
    ),
    'namespace' => 'Laravel\\Sail\\Console\\Concerns',
    'name' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
    'shortName' => 'InteractsWithDockerComposeServices',
    'isInterface' => false,
    'isTrait' => true,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 8,
    'endLine' => 342,
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
      'composePaths' => 
      array (
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'name' => 'composePaths',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'compose.yaml\', \'compose.yml\', \'docker-compose.yaml\', \'docker-compose.yml\']',
          'attributes' => 
          array (
            'startLine' => 15,
            'endLine' => 20,
            'startTokenPos' => 31,
            'startFilePos' => 310,
            'endTokenPos' => 45,
            'endFilePos' => 424,
          ),
        ),
        'docComment' => '/**
 * Possible names for the compose file according to the spec.
 *
 * @var array<string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 15,
        'endLine' => 20,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'services' => 
      array (
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'name' => 'services',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'mysql\', \'pgsql\', \'mariadb\', \'mongodb\', \'redis\', \'valkey\', \'memcached\', \'meilisearch\', \'typesense\', \'minio\', \'mailpit\', \'rabbitmq\', \'selenium\', \'soketi\']',
          'attributes' => 
          array (
            'startLine' => 27,
            'endLine' => 42,
            'startTokenPos' => 56,
            'startFilePos' => 556,
            'endTokenPos' => 100,
            'endFilePos' => 828,
          ),
        ),
        'docComment' => '/**
 * The available services that may be installed.
 *
 * @var array<string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 27,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 6,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'defaultServices' => 
      array (
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'name' => 'defaultServices',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[\'mysql\', \'redis\', \'selenium\', \'mailpit\']',
          'attributes' => 
          array (
            'startLine' => 49,
            'endLine' => 49,
            'startTokenPos' => 111,
            'startFilePos' => 986,
            'endTokenPos' => 122,
            'endFilePos' => 1026,
          ),
        ),
        'docComment' => '/**
 * The default services used when the user chooses non-interactive mode.
 *
 * @var string[]
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 49,
        'endLine' => 49,
        'startColumn' => 5,
        'endColumn' => 75,
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
      'gatherServicesInteractively' => 
      array (
        'name' => 'gatherServicesInteractively',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Gather the desired Sail services using an interactive prompt.
 *
 * @return array
 */',
        'startLine' => 56,
        'endLine' => 67,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Sail\\Console\\Concerns',
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'currentClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'aliasName' => NULL,
      ),
      'buildDockerCompose' => 
      array (
        'name' => 'buildDockerCompose',
        'parameters' => 
        array (
          'services' => 
          array (
            'name' => 'services',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 75,
            'endLine' => 75,
            'startColumn' => 43,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Build the Docker Compose file.
 *
 * @param  array  $services
 * @return void
 */',
        'startLine' => 75,
        'endLine' => 127,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Sail\\Console\\Concerns',
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'currentClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'aliasName' => NULL,
      ),
      'replaceEnvVariables' => 
      array (
        'name' => 'replaceEnvVariables',
        'parameters' => 
        array (
          'services' => 
          array (
            'name' => 'services',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 135,
            'endLine' => 135,
            'startColumn' => 44,
            'endColumn' => 58,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Replace the Host environment variables in the app\'s .env file.
 *
 * @param  array  $services
 * @return void
 */',
        'startLine' => 135,
        'endLine' => 228,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Sail\\Console\\Concerns',
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'currentClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'aliasName' => NULL,
      ),
      'configurePhpUnit' => 
      array (
        'name' => 'configurePhpUnit',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Configure PHPUnit to use the dedicated testing database.
 *
 * @return void
 */',
        'startLine' => 235,
        'endLine' => 258,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Sail\\Console\\Concerns',
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'currentClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'aliasName' => NULL,
      ),
      'installDevContainer' => 
      array (
        'name' => 'installDevContainer',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Install the devcontainer.json configuration file.
 *
 * @return void
 */',
        'startLine' => 265,
        'endLine' => 282,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Sail\\Console\\Concerns',
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'currentClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'aliasName' => NULL,
      ),
      'prepareInstallation' => 
      array (
        'name' => 'prepareInstallation',
        'parameters' => 
        array (
          'services' => 
          array (
            'name' => 'services',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 290,
            'endLine' => 290,
            'startColumn' => 44,
            'endColumn' => 52,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Prepare the installation by pulling and building any necessary images.
 *
 * @param  array  $services
 * @return void
 */',
        'startLine' => 290,
        'endLine' => 306,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Sail\\Console\\Concerns',
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'currentClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'aliasName' => NULL,
      ),
      'runCommands' => 
      array (
        'name' => 'runCommands',
        'parameters' => 
        array (
          'commands' => 
          array (
            'name' => 'commands',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 314,
            'endLine' => 314,
            'startColumn' => 36,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Run the given commands.
 *
 * @param  array  $commands
 * @return int
 */',
        'startLine' => 314,
        'endLine' => 329,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Sail\\Console\\Concerns',
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'currentClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'aliasName' => NULL,
      ),
      'composePath' => 
      array (
        'name' => 'composePath',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the path to an existing Compose file or fall back to a default of `compose.yaml`.
 *
 * @return string
 */',
        'startLine' => 336,
        'endLine' => 341,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laravel\\Sail\\Console\\Concerns',
        'declaringClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'implementingClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
        'currentClassName' => 'Laravel\\Sail\\Console\\Concerns\\InteractsWithDockerComposeServices',
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