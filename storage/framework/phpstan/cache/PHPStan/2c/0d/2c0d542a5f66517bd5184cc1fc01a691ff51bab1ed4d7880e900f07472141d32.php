<?php declare(strict_types = 1);

// osfsl-C:/xampp/htdocs/esgroup.version.2/vendor/composer/../laracasts/flash/src/Laracasts/Flash/FlashNotifier.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Laracasts\Flash\FlashNotifier
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-3da223ca3ad53d5001998975998dd508ab8c36f3ce3db0bd4dd8ebb54f60b10b-8.2.12-6.70.0.3',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Laracasts\\Flash\\FlashNotifier',
        'filename' => 'C:/xampp/htdocs/esgroup.version.2/vendor/composer/../laracasts/flash/src/Laracasts/Flash/FlashNotifier.php',
      ),
    ),
    'namespace' => 'Laracasts\\Flash',
    'name' => 'Laracasts\\Flash\\FlashNotifier',
    'shortName' => 'FlashNotifier',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 7,
    'endLine' => 166,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Support\\Traits\\Macroable',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'session' => 
      array (
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'name' => 'session',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The session store.
 *
 * @var SessionStore
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 16,
        'endLine' => 16,
        'startColumn' => 5,
        'endColumn' => 23,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'messages' => 
      array (
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'name' => 'messages',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The messages collection.
 *
 * @var \\Illuminate\\Support\\Collection
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 23,
        'endLine' => 23,
        'startColumn' => 5,
        'endColumn' => 21,
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
          'session' => 
          array (
            'name' => 'session',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Laracasts\\Flash\\SessionStore',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 30,
            'endLine' => 30,
            'startColumn' => 26,
            'endColumn' => 46,
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
 * Create a new FlashNotifier instance.
 *
 * @param SessionStore $session
 */',
        'startLine' => 30,
        'endLine' => 34,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'info' => 
      array (
        'name' => 'info',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 42,
                'endLine' => 42,
                'startTokenPos' => 84,
                'startFilePos' => 728,
                'endTokenPos' => 84,
                'endFilePos' => 731,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 42,
            'endLine' => 42,
            'startColumn' => 26,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Flash an information message.
 *
 * @param  string|null $message
 * @return $this
 */',
        'startLine' => 42,
        'endLine' => 45,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'success' => 
      array (
        'name' => 'success',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 53,
                'endLine' => 53,
                'startTokenPos' => 116,
                'startFilePos' => 947,
                'endTokenPos' => 116,
                'endFilePos' => 950,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 53,
            'endLine' => 53,
            'startColumn' => 29,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Flash a success message.
 *
 * @param  string|null $message
 * @return $this
 */',
        'startLine' => 53,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'error' => 
      array (
        'name' => 'error',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 64,
                'endLine' => 64,
                'startTokenPos' => 148,
                'startFilePos' => 1166,
                'endTokenPos' => 148,
                'endFilePos' => 1169,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 64,
            'endLine' => 64,
            'startColumn' => 27,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Flash an error message.
 *
 * @param  string|null $message
 * @return $this
 */',
        'startLine' => 64,
        'endLine' => 67,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'warning' => 
      array (
        'name' => 'warning',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 75,
                'endLine' => 75,
                'startTokenPos' => 180,
                'startFilePos' => 1387,
                'endTokenPos' => 180,
                'endFilePos' => 1390,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 75,
            'endLine' => 75,
            'startColumn' => 29,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Flash a warning message.
 *
 * @param  string|null $message
 * @return $this
 */',
        'startLine' => 75,
        'endLine' => 78,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'message' => 
      array (
        'name' => 'message',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 87,
                'endLine' => 87,
                'startTokenPos' => 212,
                'startFilePos' => 1643,
                'endTokenPos' => 212,
                'endFilePos' => 1646,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 87,
            'endLine' => 87,
            'startColumn' => 29,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'level' => 
          array (
            'name' => 'level',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 87,
                'endLine' => 87,
                'startTokenPos' => 219,
                'startFilePos' => 1658,
                'endTokenPos' => 219,
                'endFilePos' => 1661,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 87,
            'endLine' => 87,
            'startColumn' => 46,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Flash a general message.
 *
 * @param  string|null $message
 * @param  string|null $level
 * @return $this
 */',
        'startLine' => 87,
        'endLine' => 102,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'updateLastMessage' => 
      array (
        'name' => 'updateLastMessage',
        'parameters' => 
        array (
          'overrides' => 
          array (
            'name' => 'overrides',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 110,
                'endLine' => 110,
                'startTokenPos' => 320,
                'startFilePos' => 2248,
                'endTokenPos' => 321,
                'endFilePos' => 2249,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 110,
            'endLine' => 110,
            'startColumn' => 42,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Modify the most recently added message.
 *
 * @param  array $overrides
 * @return $this
 */',
        'startLine' => 110,
        'endLine' => 115,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'overlay' => 
      array (
        'name' => 'overlay',
        'parameters' => 
        array (
          'message' => 
          array (
            'name' => 'message',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 124,
                'endLine' => 124,
                'startTokenPos' => 359,
                'startFilePos' => 2525,
                'endTokenPos' => 359,
                'endFilePos' => 2528,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 124,
            'endLine' => 124,
            'startColumn' => 29,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'title' => 
          array (
            'name' => 'title',
            'default' => 
            array (
              'code' => '\'Notice\'',
              'attributes' => 
              array (
                'startLine' => 124,
                'endLine' => 124,
                'startTokenPos' => 366,
                'startFilePos' => 2540,
                'endTokenPos' => 366,
                'endFilePos' => 2547,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 124,
            'endLine' => 124,
            'startColumn' => 46,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Flash an overlay modal.
 *
 * @param  string|null $message
 * @param  string      $title
 * @return $this
 */',
        'startLine' => 124,
        'endLine' => 133,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'important' => 
      array (
        'name' => 'important',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Add an "important" flash to the session.
 *
 * @return $this
 */',
        'startLine' => 140,
        'endLine' => 143,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'clear' => 
      array (
        'name' => 'clear',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Clear all registered messages.
 *
 * @return $this
 */',
        'startLine' => 150,
        'endLine' => 155,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'aliasName' => NULL,
      ),
      'flash' => 
      array (
        'name' => 'flash',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Flash all messages to the session.
 */',
        'startLine' => 160,
        'endLine' => 165,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Laracasts\\Flash',
        'declaringClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'implementingClassName' => 'Laracasts\\Flash\\FlashNotifier',
        'currentClassName' => 'Laracasts\\Flash\\FlashNotifier',
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