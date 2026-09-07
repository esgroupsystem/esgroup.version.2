<?php declare(strict_types = 1);

// odsl-C:\xampp\htdocs\esgroup.version.2\app\Http\Middleware\TrustProxies.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Http\Middleware\TrustProxies
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.2.12-731995b3e70f7bbd1219e66206ba020c275a7c8cf359b31ff15832708ea336b5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Http\\Middleware\\TrustProxies',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/app/Http/Middleware/TrustProxies.php',
      ),
    ),
    'namespace' => 'App\\Http\\Middleware',
    'name' => 'App\\Http\\Middleware\\TrustProxies',
    'shortName' => 'TrustProxies',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 10,
    'endLine' => 34,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Http\\Middleware\\TrustProxies',
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
      'proxies' => 
      array (
        'declaringClassName' => 'App\\Http\\Middleware\\TrustProxies',
        'implementingClassName' => 'App\\Http\\Middleware\\TrustProxies',
        'name' => 'proxies',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => 'null',
          'attributes' => 
          array (
            'startLine' => 17,
            'endLine' => 17,
            'startTokenPos' => 47,
            'startFilePos' => 334,
            'endTokenPos' => 47,
            'endFilePos' => 337,
          ),
        ),
        'docComment' => '/**
 * Trust Cloudflare / hosting proxy headers.
 *
 * @var array<int, string>|string|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 17,
        'endLine' => 17,
        'startColumn' => 5,
        'endColumn' => 30,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'headers' => 
      array (
        'declaringClassName' => 'App\\Http\\Middleware\\TrustProxies',
        'implementingClassName' => 'App\\Http\\Middleware\\TrustProxies',
        'name' => 'headers',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\\Illuminate\\Http\\Request::HEADER_X_FORWARDED_FOR | \\Illuminate\\Http\\Request::HEADER_X_FORWARDED_HOST | \\Illuminate\\Http\\Request::HEADER_X_FORWARDED_PORT | \\Illuminate\\Http\\Request::HEADER_X_FORWARDED_PROTO',
          'attributes' => 
          array (
            'startLine' => 30,
            'endLine' => 33,
            'startTokenPos' => 82,
            'startFilePos' => 587,
            'endTokenPos' => 102,
            'endFilePos' => 747,
          ),
        ),
        'docComment' => '/**
 * Headers used to detect original request data behind proxy.
 *
 * @var int
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 29,
        'endLine' => 33,
        'startColumn' => 5,
        'endColumn' => 42,
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
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 19,
        'endLine' => 22,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Http\\Middleware',
        'declaringClassName' => 'App\\Http\\Middleware\\TrustProxies',
        'implementingClassName' => 'App\\Http\\Middleware\\TrustProxies',
        'currentClassName' => 'App\\Http\\Middleware\\TrustProxies',
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