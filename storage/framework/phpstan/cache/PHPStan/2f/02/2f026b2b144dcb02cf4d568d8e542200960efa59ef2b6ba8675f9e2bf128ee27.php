<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionClass-domdocument
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-dev-master@709e512-8.2.12',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\InternalLocatedSource',
      'data' => 
      array (
        'name' => 'DOMDocument',
        'filename' => 'phpstorm-stubs:dom/dom_c.stub',
        'extensionName' => 'dom',
        'aliasName' => NULL,
      ),
    ),
    'namespace' => NULL,
    'name' => 'DOMDocument',
    'shortName' => 'DOMDocument',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * The DOMDocument class represents an entire HTML or XML
 * document; serves as the root of the document tree.
 * @link https://php.net/manual/en/class.domdocument.php
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 9,
    'endLine' => 779,
    'startColumn' => 5,
    'endColumn' => 5,
    'parentClassName' => 'DOMNode',
    'implementsClassNames' => 
    array (
      0 => 'DOMParentNode',
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'actualEncoding' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'actualEncoding',
        'modifiers' => 1,
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
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var string|null
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.actualencoding
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Deprecated',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '"The property is deprecated"',
                'attributes' => 
                array (
                  'startLine' => 15,
                  'endLine' => 15,
                  'startTokenPos' => 27,
                  'startFilePos' => 478,
                  'endTokenPos' => 27,
                  'endFilePos' => 505,
                ),
              ),
              'since' => 
              array (
                'code' => '"8.4"',
                'attributes' => 
                array (
                  'startLine' => 15,
                  'endLine' => 15,
                  'startTokenPos' => 33,
                  'startFilePos' => 515,
                  'endTokenPos' => 33,
                  'endFilePos' => 519,
                ),
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'string|null\']',
                'attributes' => 
                array (
                  'startLine' => 16,
                  'endLine' => 16,
                  'startTokenPos' => 40,
                  'startFilePos' => 585,
                  'endTokenPos' => 46,
                  'endFilePos' => 608,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 16,
                  'endLine' => 16,
                  'startTokenPos' => 52,
                  'startFilePos' => 620,
                  'endTokenPos' => 52,
                  'endFilePos' => 621,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 15,
        'endLine' => 17,
        'startColumn' => 9,
        'endColumn' => 43,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'config' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'config',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var DOMConfiguration
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.config
 * @see DOMDocument::normalizeDocument()
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Deprecated',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '"The property is deprecated"',
                'attributes' => 
                array (
                  'startLine' => 23,
                  'endLine' => 23,
                  'startTokenPos' => 70,
                  'startFilePos' => 906,
                  'endTokenPos' => 70,
                  'endFilePos' => 933,
                ),
              ),
              'since' => 
              array (
                'code' => '"8.4"',
                'attributes' => 
                array (
                  'startLine' => 23,
                  'endLine' => 23,
                  'startTokenPos' => 76,
                  'startFilePos' => 943,
                  'endTokenPos' => 76,
                  'endFilePos' => 947,
                ),
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'mixed\']',
                'attributes' => 
                array (
                  'startLine' => 24,
                  'endLine' => 24,
                  'startTokenPos' => 83,
                  'startFilePos' => 1013,
                  'endTokenPos' => 89,
                  'endFilePos' => 1030,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 24,
                  'endLine' => 24,
                  'startTokenPos' => 95,
                  'startFilePos' => 1042,
                  'endTokenPos' => 95,
                  'endFilePos' => 1043,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 23,
        'endLine' => 25,
        'startColumn' => 9,
        'endColumn' => 29,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'doctype' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'doctype',
        'modifiers' => 1,
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
                  'name' => 'DOMDocumentType',
                  'isIdentifier' => false,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var DOMDocumentType
 * The Document Type Declaration associated with this document.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.doctype
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'DOMDocumentType|null\']',
                'attributes' => 
                array (
                  'startLine' => 31,
                  'endLine' => 31,
                  'startTokenPos' => 111,
                  'startFilePos' => 1358,
                  'endTokenPos' => 117,
                  'endFilePos' => 1390,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 31,
                  'endLine' => 31,
                  'startTokenPos' => 123,
                  'startFilePos' => 1402,
                  'endTokenPos' => 123,
                  'endFilePos' => 1403,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 31,
        'endLine' => 32,
        'startColumn' => 9,
        'endColumn' => 45,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'documentElement' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'documentElement',
        'modifiers' => 1,
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
                  'name' => 'DOMElement',
                  'isIdentifier' => false,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var DOMElement
 * This is a convenience attribute that allows direct access to the child node
 * that is the document element of the document.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.documentelement
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'DOMElement|null\']',
                'attributes' => 
                array (
                  'startLine' => 39,
                  'endLine' => 39,
                  'startTokenPos' => 141,
                  'startFilePos' => 1809,
                  'endTokenPos' => 147,
                  'endFilePos' => 1836,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 39,
                  'endLine' => 39,
                  'startTokenPos' => 153,
                  'startFilePos' => 1848,
                  'endTokenPos' => 153,
                  'endFilePos' => 1849,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 39,
        'endLine' => 40,
        'startColumn' => 9,
        'endColumn' => 48,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'documentURI' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'documentURI',
        'modifiers' => 1,
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
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var string|null
 * The location of the document or NULL if undefined.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.documenturi
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'string|null\']',
                'attributes' => 
                array (
                  'startLine' => 46,
                  'endLine' => 46,
                  'startTokenPos' => 171,
                  'startFilePos' => 2173,
                  'endTokenPos' => 177,
                  'endFilePos' => 2196,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 46,
                  'endLine' => 46,
                  'startTokenPos' => 183,
                  'startFilePos' => 2208,
                  'endTokenPos' => 183,
                  'endFilePos' => 2209,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 46,
        'endLine' => 47,
        'startColumn' => 9,
        'endColumn' => 40,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'encoding' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'encoding',
        'modifiers' => 1,
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
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var string|null
 * Encoding of the document, as specified by the XML declaration. This attribute is not present
 * in the final DOM Level 3 specification, but is the only way of manipulating XML document
 * encoding in this implementation.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.encoding
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'string|null\']',
                'attributes' => 
                array (
                  'startLine' => 55,
                  'endLine' => 55,
                  'startTokenPos' => 201,
                  'startFilePos' => 2708,
                  'endTokenPos' => 207,
                  'endFilePos' => 2731,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 55,
                  'endLine' => 55,
                  'startTokenPos' => 213,
                  'startFilePos' => 2743,
                  'endTokenPos' => 213,
                  'endFilePos' => 2744,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 55,
        'endLine' => 56,
        'startColumn' => 9,
        'endColumn' => 37,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'formatOutput' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'formatOutput',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var bool
 * Nicely formats output with indentation and extra space.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.formatoutput
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 62,
                  'endLine' => 62,
                  'startTokenPos' => 231,
                  'startFilePos' => 3056,
                  'endTokenPos' => 237,
                  'endFilePos' => 3072,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 62,
                  'endLine' => 62,
                  'startTokenPos' => 243,
                  'startFilePos' => 3084,
                  'endTokenPos' => 243,
                  'endFilePos' => 3085,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 62,
        'endLine' => 63,
        'startColumn' => 9,
        'endColumn' => 34,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'implementation' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'implementation',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'DOMImplementation',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var DOMImplementation
 * The <classname>DOMImplementation</classname> object that handles this document.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.implementation
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'DOMImplementation\']',
                'attributes' => 
                array (
                  'startLine' => 69,
                  'endLine' => 69,
                  'startTokenPos' => 259,
                  'startFilePos' => 3433,
                  'endTokenPos' => 265,
                  'endFilePos' => 3462,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 69,
                  'endLine' => 69,
                  'startTokenPos' => 271,
                  'startFilePos' => 3474,
                  'endTokenPos' => 271,
                  'endFilePos' => 3475,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 69,
        'endLine' => 70,
        'startColumn' => 9,
        'endColumn' => 49,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'preserveWhiteSpace' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'preserveWhiteSpace',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '\\true',
          'attributes' => 
          array (
            'startLine' => 77,
            'endLine' => 77,
            'startTokenPos' => 311,
            'startFilePos' => 3878,
            'endTokenPos' => 311,
            'endFilePos' => 3882,
          ),
        ),
        'docComment' => '/**
 * @var bool
 * Do not remove redundant white space. Default to TRUE.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.preservewhitespace
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 76,
                  'endLine' => 76,
                  'startTokenPos' => 287,
                  'startFilePos' => 3803,
                  'endTokenPos' => 293,
                  'endFilePos' => 3819,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 76,
                  'endLine' => 76,
                  'startTokenPos' => 299,
                  'startFilePos' => 3831,
                  'endTokenPos' => 299,
                  'endFilePos' => 3832,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 76,
        'endLine' => 77,
        'startColumn' => 9,
        'endColumn' => 48,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'recover' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'recover',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var bool
 * Proprietary. Enables recovery mode, i.e. trying to parse non-well formed documents.
 * This attribute is not part of the DOM specification and is specific to libxml.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.recover
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 84,
                  'endLine' => 84,
                  'startTokenPos' => 319,
                  'startFilePos' => 4268,
                  'endTokenPos' => 325,
                  'endFilePos' => 4284,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 84,
                  'endLine' => 84,
                  'startTokenPos' => 331,
                  'startFilePos' => 4296,
                  'endTokenPos' => 331,
                  'endFilePos' => 4297,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 84,
        'endLine' => 85,
        'startColumn' => 9,
        'endColumn' => 29,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'resolveExternals' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'resolveExternals',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var bool
 * Set it to TRUE to load external entities from a doctype declaration. This is useful for
 * including character entities in your XML document.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.resolveexternals
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 92,
                  'endLine' => 92,
                  'startTokenPos' => 347,
                  'startFilePos' => 4699,
                  'endTokenPos' => 353,
                  'endFilePos' => 4715,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 92,
                  'endLine' => 92,
                  'startTokenPos' => 359,
                  'startFilePos' => 4727,
                  'endTokenPos' => 359,
                  'endFilePos' => 4728,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 92,
        'endLine' => 93,
        'startColumn' => 9,
        'endColumn' => 38,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'standalone' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'standalone',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var bool
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.standalone
 * @deprecated
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Deprecated',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '"Whether or not the document is standalone, as specified by the XML declaration, corresponds to xmlStandalone."',
                'attributes' => 
                array (
                  'startLine' => 99,
                  'endLine' => 99,
                  'startTokenPos' => 375,
                  'startFilePos' => 4974,
                  'endTokenPos' => 375,
                  'endFilePos' => 5084,
                ),
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 100,
                  'endLine' => 100,
                  'startTokenPos' => 382,
                  'startFilePos' => 5150,
                  'endTokenPos' => 388,
                  'endFilePos' => 5166,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 100,
                  'endLine' => 100,
                  'startTokenPos' => 394,
                  'startFilePos' => 5178,
                  'endTokenPos' => 394,
                  'endFilePos' => 5179,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 99,
        'endLine' => 101,
        'startColumn' => 9,
        'endColumn' => 32,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'strictErrorChecking' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'strictErrorChecking',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '\\true',
          'attributes' => 
          array (
            'startLine' => 108,
            'endLine' => 108,
            'startTokenPos' => 434,
            'startFilePos' => 5584,
            'endTokenPos' => 434,
            'endFilePos' => 5588,
          ),
        ),
        'docComment' => '/**
 * @var bool
 * Throws <classname>DOMException</classname> on errors. Default to TRUE.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.stricterrorchecking
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 107,
                  'endLine' => 107,
                  'startTokenPos' => 410,
                  'startFilePos' => 5508,
                  'endTokenPos' => 416,
                  'endFilePos' => 5524,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 107,
                  'endLine' => 107,
                  'startTokenPos' => 422,
                  'startFilePos' => 5536,
                  'endTokenPos' => 422,
                  'endFilePos' => 5537,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 107,
        'endLine' => 108,
        'startColumn' => 9,
        'endColumn' => 49,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'substituteEntities' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'substituteEntities',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var bool
 * Proprietary. Whether or not to substitute entities. This attribute is not part of the DOM
 * specification and is specific to libxml.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.substituteentities
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 115,
                  'endLine' => 115,
                  'startTokenPos' => 442,
                  'startFilePos' => 5953,
                  'endTokenPos' => 448,
                  'endFilePos' => 5969,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 115,
                  'endLine' => 115,
                  'startTokenPos' => 454,
                  'startFilePos' => 5981,
                  'endTokenPos' => 454,
                  'endFilePos' => 5982,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 115,
        'endLine' => 116,
        'startColumn' => 9,
        'endColumn' => 40,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'validateOnParse' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'validateOnParse',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '\\false',
          'attributes' => 
          array (
            'startLine' => 123,
            'endLine' => 123,
            'startTokenPos' => 494,
            'startFilePos' => 6371,
            'endTokenPos' => 494,
            'endFilePos' => 6376,
          ),
        ),
        'docComment' => '/**
 * @var bool
 * Loads and validates against the DTD. Default to FALSE.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.validateonparse
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 122,
                  'endLine' => 122,
                  'startTokenPos' => 470,
                  'startFilePos' => 6299,
                  'endTokenPos' => 476,
                  'endFilePos' => 6315,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 122,
                  'endLine' => 122,
                  'startTokenPos' => 482,
                  'startFilePos' => 6327,
                  'endTokenPos' => 482,
                  'endFilePos' => 6328,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 122,
        'endLine' => 123,
        'startColumn' => 9,
        'endColumn' => 46,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'version' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'version',
        'modifiers' => 1,
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
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var string
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.version
 * @deprecated
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Deprecated',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '\'Version of XML, corresponds to xmlVersion\'',
                'attributes' => 
                array (
                  'startLine' => 129,
                  'endLine' => 129,
                  'startTokenPos' => 502,
                  'startFilePos' => 6581,
                  'endTokenPos' => 502,
                  'endFilePos' => 6623,
                ),
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'string|null\']',
                'attributes' => 
                array (
                  'startLine' => 130,
                  'endLine' => 130,
                  'startTokenPos' => 509,
                  'startFilePos' => 6689,
                  'endTokenPos' => 515,
                  'endFilePos' => 6712,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 130,
                  'endLine' => 130,
                  'startTokenPos' => 521,
                  'startFilePos' => 6724,
                  'endTokenPos' => 521,
                  'endFilePos' => 6725,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 129,
        'endLine' => 131,
        'startColumn' => 9,
        'endColumn' => 36,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'xmlEncoding' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'xmlEncoding',
        'modifiers' => 1,
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
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var string|null
 * An attribute specifying, as part of the XML declaration, the encoding of this document. This is NULL when
 * unspecified or when it is not known, such as when the Document was created in memory.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.xmlencoding
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'string|null\']',
                'attributes' => 
                array (
                  'startLine' => 138,
                  'endLine' => 138,
                  'startTokenPos' => 539,
                  'startFilePos' => 7189,
                  'endTokenPos' => 545,
                  'endFilePos' => 7212,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 138,
                  'endLine' => 138,
                  'startTokenPos' => 551,
                  'startFilePos' => 7224,
                  'endTokenPos' => 551,
                  'endFilePos' => 7225,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 138,
        'endLine' => 139,
        'startColumn' => 9,
        'endColumn' => 40,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'xmlStandalone' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'xmlStandalone',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var bool
 * An attribute specifying, as part of the XML declaration, whether this document is standalone.
 * This is FALSE when unspecified.
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.xmlstandalone
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 146,
                  'endLine' => 146,
                  'startTokenPos' => 569,
                  'startFilePos' => 7622,
                  'endTokenPos' => 575,
                  'endFilePos' => 7638,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 146,
                  'endLine' => 146,
                  'startTokenPos' => 581,
                  'startFilePos' => 7650,
                  'endTokenPos' => 581,
                  'endFilePos' => 7651,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 146,
        'endLine' => 147,
        'startColumn' => 9,
        'endColumn' => 35,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'xmlVersion' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'xmlVersion',
        'modifiers' => 1,
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
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var string|null
 * An attribute specifying, as part of the XML declaration, the version number of this document. If there is no
 * declaration and if this document supports the "XML" feature, the value is "1.0".
 * @link https://php.net/manual/en/class.domdocument.php#domdocument.props.xmlversion
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'string|null\']',
                'attributes' => 
                array (
                  'startLine' => 154,
                  'endLine' => 154,
                  'startTokenPos' => 597,
                  'startFilePos' => 8111,
                  'endTokenPos' => 603,
                  'endFilePos' => 8134,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 154,
                  'endLine' => 154,
                  'startTokenPos' => 609,
                  'startFilePos' => 8146,
                  'endTokenPos' => 609,
                  'endFilePos' => 8147,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 154,
        'endLine' => 155,
        'startColumn' => 9,
        'endColumn' => 39,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'childElementCount' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'childElementCount',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'int\']',
                'attributes' => 
                array (
                  'startLine' => 156,
                  'endLine' => 156,
                  'startTokenPos' => 625,
                  'startFilePos' => 8253,
                  'endTokenPos' => 631,
                  'endFilePos' => 8268,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 156,
                  'endLine' => 156,
                  'startTokenPos' => 637,
                  'startFilePos' => 8280,
                  'endTokenPos' => 637,
                  'endFilePos' => 8281,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 156,
        'endLine' => 157,
        'startColumn' => 9,
        'endColumn' => 38,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'lastElementChild' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'lastElementChild',
        'modifiers' => 1,
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
                  'name' => 'DOMElement',
                  'isIdentifier' => false,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'DOMElement|null\']',
                'attributes' => 
                array (
                  'startLine' => 158,
                  'endLine' => 158,
                  'startTokenPos' => 651,
                  'startFilePos' => 8386,
                  'endTokenPos' => 657,
                  'endFilePos' => 8413,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 158,
                  'endLine' => 158,
                  'startTokenPos' => 663,
                  'startFilePos' => 8425,
                  'endTokenPos' => 663,
                  'endFilePos' => 8426,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 158,
        'endLine' => 159,
        'startColumn' => 9,
        'endColumn' => 49,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'firstElementChild' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'firstElementChild',
        'modifiers' => 1,
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
                  'name' => 'DOMElement',
                  'isIdentifier' => false,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'DOMElement|null\']',
                'attributes' => 
                array (
                  'startLine' => 160,
                  'endLine' => 160,
                  'startTokenPos' => 679,
                  'startFilePos' => 8542,
                  'endTokenPos' => 685,
                  'endFilePos' => 8569,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 160,
                  'endLine' => 160,
                  'startTokenPos' => 691,
                  'startFilePos' => 8581,
                  'endTokenPos' => 691,
                  'endFilePos' => 8582,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 160,
        'endLine' => 161,
        'startColumn' => 9,
        'endColumn' => 50,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'ownerDocument' => 
      array (
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'name' => 'ownerDocument',
        'modifiers' => 1,
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
                  'name' => 'DOMDocument',
                  'isIdentifier' => false,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var null
 * The <classname>DOMDocument</classname> object associated with this node, or NULL if this node is a <classname>DOMDocument</classname>.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.ownerdocument
 */',
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.1\' => \'DOMDocument|null\']',
                'attributes' => 
                array (
                  'startLine' => 167,
                  'endLine' => 167,
                  'startTokenPos' => 709,
                  'startFilePos' => 8979,
                  'endTokenPos' => 715,
                  'endFilePos' => 9007,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 167,
                  'endLine' => 167,
                  'startTokenPos' => 721,
                  'startFilePos' => 9019,
                  'endTokenPos' => 721,
                  'endFilePos' => 9020,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 167,
        'endLine' => 168,
        'startColumn' => 9,
        'endColumn' => 47,
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
      'createElement' => 
      array (
        'name' => 'createElement',
        'parameters' => 
        array (
          'localName' => 
          array (
            'name' => 'localName',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 184,
                      'endLine' => 184,
                      'startTokenPos' => 746,
                      'startFilePos' => 9790,
                      'endTokenPos' => 752,
                      'endFilePos' => 9808,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 184,
                      'endLine' => 184,
                      'startTokenPos' => 758,
                      'startFilePos' => 9820,
                      'endTokenPos' => 758,
                      'endFilePos' => 9821,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 184,
            'endLine' => 185,
            'startColumn' => 13,
            'endColumn' => 29,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => 
            array (
              'code' => '\'\'',
              'attributes' => 
              array (
                'startLine' => 187,
                'endLine' => 187,
                'startTokenPos' => 792,
                'startFilePos' => 9985,
                'endTokenPos' => 792,
                'endFilePos' => 9986,
              ),
            ),
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 186,
                      'endLine' => 186,
                      'startTokenPos' => 770,
                      'startFilePos' => 9922,
                      'endTokenPos' => 776,
                      'endFilePos' => 9940,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 186,
                      'endLine' => 186,
                      'startTokenPos' => 782,
                      'startFilePos' => 9952,
                      'endTokenPos' => 782,
                      'endFilePos' => 9953,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 186,
            'endLine' => 187,
            'startColumn' => 13,
            'endColumn' => 30,
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
 * Create new element node
 * @link https://php.net/manual/en/domdocument.createelement.php
 * @param string $localName <p>
 * The tag name of the element.
 * </p>
 * @param string $value [optional] <p>
 * The value of the element. By default, an empty element will be created.
 * You can also set the value later with DOMElement->nodeValue.
 * </p>
 * @return DOMElement|false A new instance of class DOMElement or false
 * if an error occurred.
 * @throws DOMException If invalid $localName
 */',
        'startLine' => 183,
        'endLine' => 190,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'createDocumentFragment' => 
      array (
        'name' => 'createDocumentFragment',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'DOMDocumentFragment',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Create new document fragment
 * @link https://php.net/manual/en/domdocument.createdocumentfragment.php
 * @return DOMDocumentFragment|false The new DOMDocumentFragment or false if an error occurred.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 197,
        'endLine' => 200,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'createTextNode' => 
      array (
        'name' => 'createTextNode',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 212,
                      'endLine' => 212,
                      'startTokenPos' => 837,
                      'startFilePos' => 10966,
                      'endTokenPos' => 843,
                      'endFilePos' => 10984,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 212,
                      'endLine' => 212,
                      'startTokenPos' => 849,
                      'startFilePos' => 10996,
                      'endTokenPos' => 849,
                      'endFilePos' => 10997,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 212,
            'endLine' => 213,
            'startColumn' => 13,
            'endColumn' => 24,
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
            'name' => 'DOMText',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Create new text node
 * @link https://php.net/manual/en/domdocument.createtextnode.php
 * @param string $data <p>
 * The content of the text.
 * </p>
 * @return DOMText|false The new DOMText or false if an error occurred.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 210,
        'endLine' => 216,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'createComment' => 
      array (
        'name' => 'createComment',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 228,
                      'endLine' => 228,
                      'startTokenPos' => 882,
                      'startFilePos' => 11581,
                      'endTokenPos' => 888,
                      'endFilePos' => 11599,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 228,
                      'endLine' => 228,
                      'startTokenPos' => 894,
                      'startFilePos' => 11611,
                      'endTokenPos' => 894,
                      'endFilePos' => 11612,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 228,
            'endLine' => 229,
            'startColumn' => 13,
            'endColumn' => 24,
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
            'name' => 'DOMComment',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Create new comment node
 * @link https://php.net/manual/en/domdocument.createcomment.php
 * @param string $data <p>
 * The content of the comment.
 * </p>
 * @return DOMComment|false The new DOMComment or false if an error occurred.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 226,
        'endLine' => 232,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'createCDATASection' => 
      array (
        'name' => 'createCDATASection',
        'parameters' => 
        array (
          'data' => 
          array (
            'name' => 'data',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 242,
                      'endLine' => 242,
                      'startTokenPos' => 923,
                      'startFilePos' => 12113,
                      'endTokenPos' => 929,
                      'endFilePos' => 12131,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 242,
                      'endLine' => 242,
                      'startTokenPos' => 935,
                      'startFilePos' => 12143,
                      'endTokenPos' => 935,
                      'endFilePos' => 12144,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 242,
            'endLine' => 243,
            'startColumn' => 13,
            'endColumn' => 24,
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
 * Create new cdata node
 * @link https://php.net/manual/en/domdocument.createcdatasection.php
 * @param string $data <p>
 * The content of the cdata.
 * </p>
 * @return DOMCDATASection|false The new DOMCDATASection or false if an error occurred.
 */',
        'startLine' => 241,
        'endLine' => 246,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'createProcessingInstruction' => 
      array (
        'name' => 'createProcessingInstruction',
        'parameters' => 
        array (
          'target' => 
          array (
            'name' => 'target',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 259,
                      'endLine' => 259,
                      'startTokenPos' => 961,
                      'startFilePos' => 12789,
                      'endTokenPos' => 967,
                      'endFilePos' => 12807,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 259,
                      'endLine' => 259,
                      'startTokenPos' => 973,
                      'startFilePos' => 12819,
                      'endTokenPos' => 973,
                      'endFilePos' => 12820,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 259,
            'endLine' => 260,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'data' => 
          array (
            'name' => 'data',
            'default' => 
            array (
              'code' => '\'\'',
              'attributes' => 
              array (
                'startLine' => 263,
                'endLine' => 263,
                'startTokenPos' => 1017,
                'startFilePos' => 13067,
                'endTokenPos' => 1017,
                'endFilePos' => 13068,
              ),
            ),
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
                'isRepeated' => false,
                'arguments' => 
                array (
                  'from' => 
                  array (
                    'code' => '\'7.4\'',
                    'attributes' => 
                    array (
                      'startLine' => 261,
                      'endLine' => 261,
                      'startTokenPos' => 988,
                      'startFilePos' => 12931,
                      'endTokenPos' => 988,
                      'endFilePos' => 12935,
                    ),
                  ),
                ),
              ),
              1 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 262,
                      'endLine' => 262,
                      'startTokenPos' => 995,
                      'startFilePos' => 13005,
                      'endTokenPos' => 1001,
                      'endFilePos' => 13023,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 262,
                      'endLine' => 262,
                      'startTokenPos' => 1007,
                      'startFilePos' => 13035,
                      'endTokenPos' => 1007,
                      'endFilePos' => 13036,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 261,
            'endLine' => 263,
            'startColumn' => 13,
            'endColumn' => 29,
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
 * Creates new PI node
 * @link https://php.net/manual/en/domdocument.createprocessinginstruction.php
 * @param string $target <p>
 * The target of the processing instruction.
 * </p>
 * @param string $data <p>
 * The content of the processing instruction.
 * </p>
 * @return DOMProcessingInstruction|false The new DOMProcessingInstruction or false if an error occurred.
 */',
        'startLine' => 258,
        'endLine' => 266,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'createAttribute' => 
      array (
        'name' => 'createAttribute',
        'parameters' => 
        array (
          'localName' => 
          array (
            'name' => 'localName',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 277,
                      'endLine' => 277,
                      'startTokenPos' => 1037,
                      'startFilePos' => 13566,
                      'endTokenPos' => 1043,
                      'endFilePos' => 13584,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 277,
                      'endLine' => 277,
                      'startTokenPos' => 1049,
                      'startFilePos' => 13596,
                      'endTokenPos' => 1049,
                      'endFilePos' => 13597,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 277,
            'endLine' => 278,
            'startColumn' => 13,
            'endColumn' => 29,
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
 * Create new attribute
 * @link https://php.net/manual/en/domdocument.createattribute.php
 * @param string $localName <p>
 * The name of the attribute.
 * </p>
 * @return DOMAttr|false The new DOMAttr or false if an error occurred.
 * @throws DOMException If invalid $localName
 */',
        'startLine' => 276,
        'endLine' => 281,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'createEntityReference' => 
      array (
        'name' => 'createEntityReference',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 294,
                      'endLine' => 294,
                      'startTokenPos' => 1075,
                      'startFilePos' => 14238,
                      'endTokenPos' => 1081,
                      'endFilePos' => 14256,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 294,
                      'endLine' => 294,
                      'startTokenPos' => 1087,
                      'startFilePos' => 14268,
                      'endTokenPos' => 1087,
                      'endFilePos' => 14269,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 294,
            'endLine' => 295,
            'startColumn' => 13,
            'endColumn' => 24,
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
 * Create new entity reference node
 * @link https://php.net/manual/en/domdocument.createentityreference.php
 * @param string $name <p>
 * The content of the entity reference, e.g. the entity reference minus
 * the leading &amp; and the trailing
 * ; characters.
 * </p>
 * @return DOMEntityReference|false The new DOMEntityReference or false if an error
 * occurred.
 */',
        'startLine' => 293,
        'endLine' => 298,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'getElementsByTagName' => 
      array (
        'name' => 'getElementsByTagName',
        'parameters' => 
        array (
          'qualifiedName' => 
          array (
            'name' => 'qualifiedName',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 312,
                      'endLine' => 312,
                      'startTokenPos' => 1117,
                      'startFilePos' => 14972,
                      'endTokenPos' => 1123,
                      'endFilePos' => 14990,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 312,
                      'endLine' => 312,
                      'startTokenPos' => 1129,
                      'startFilePos' => 15002,
                      'endTokenPos' => 1129,
                      'endFilePos' => 15003,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 312,
            'endLine' => 313,
            'startColumn' => 13,
            'endColumn' => 33,
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
            'name' => 'DOMNodeList',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Searches for all elements with given tag name
 * @link https://php.net/manual/en/domdocument.getelementsbytagname.php
 * @param string $qualifiedName <p>
 * The name of the tag to match on. The special value *
 * matches all tags.
 * </p>
 * @return DOMNodeList<DOMElement> A new DOMNodeList object containing all the matched
 * elements.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 310,
        'endLine' => 316,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'importNode' => 
      array (
        'name' => 'importNode',
        'parameters' => 
        array (
          'node' => 
          array (
            'name' => 'node',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DOMNode',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 334,
            'endLine' => 334,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'deep' => 
          array (
            'name' => 'deep',
            'default' => 
            array (
              'code' => '\\false',
              'attributes' => 
              array (
                'startLine' => 337,
                'endLine' => 337,
                'startTokenPos' => 1195,
                'startFilePos' => 15941,
                'endTokenPos' => 1195,
                'endFilePos' => 15945,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
                'isRepeated' => false,
                'arguments' => 
                array (
                  'from' => 
                  array (
                    'code' => '\'7.4\'',
                    'attributes' => 
                    array (
                      'startLine' => 335,
                      'endLine' => 335,
                      'startTokenPos' => 1166,
                      'startFilePos' => 15809,
                      'endTokenPos' => 1166,
                      'endFilePos' => 15813,
                    ),
                  ),
                ),
              ),
              1 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 336,
                      'endLine' => 336,
                      'startTokenPos' => 1173,
                      'startFilePos' => 15883,
                      'endTokenPos' => 1179,
                      'endFilePos' => 15899,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 336,
                      'endLine' => 336,
                      'startTokenPos' => 1185,
                      'startFilePos' => 15911,
                      'endTokenPos' => 1185,
                      'endFilePos' => 15912,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 335,
            'endLine' => 337,
            'startColumn' => 13,
            'endColumn' => 30,
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
 * Import node into current document
 * @link https://php.net/manual/en/domdocument.importnode.php
 * @param DOMNode $node <p>
 * The node to import.
 * </p>
 * @param bool $deep <p>
 * If set to true, this method will recursively import the subtree under
 * the importedNode.
 * </p>
 * <p>
 * To copy the nodes attributes deep needs to be set to true
 * </p>
 * @return DOMNode|false The copied node or false, if it cannot be copied.
 * @meta
 */',
        'startLine' => 333,
        'endLine' => 340,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'createElementNS' => 
      array (
        'name' => 'createElementNS',
        'parameters' => 
        array (
          'namespace' => 
          array (
            'name' => 'namespace',
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
                      'name' => 'null',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 358,
                      'endLine' => 358,
                      'startTokenPos' => 1215,
                      'startFilePos' => 16846,
                      'endTokenPos' => 1221,
                      'endFilePos' => 16869,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 358,
                      'endLine' => 358,
                      'startTokenPos' => 1227,
                      'startFilePos' => 16881,
                      'endTokenPos' => 1227,
                      'endFilePos' => 16882,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 358,
            'endLine' => 359,
            'startColumn' => 13,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'qualifiedName' => 
          array (
            'name' => 'qualifiedName',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 360,
                      'endLine' => 360,
                      'startTokenPos' => 1241,
                      'startFilePos' => 16988,
                      'endTokenPos' => 1247,
                      'endFilePos' => 17006,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 360,
                      'endLine' => 360,
                      'startTokenPos' => 1253,
                      'startFilePos' => 17018,
                      'endTokenPos' => 1253,
                      'endFilePos' => 17019,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 360,
            'endLine' => 361,
            'startColumn' => 13,
            'endColumn' => 33,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => 
            array (
              'code' => '\'\'',
              'attributes' => 
              array (
                'startLine' => 363,
                'endLine' => 363,
                'startTokenPos' => 1287,
                'startFilePos' => 17187,
                'endTokenPos' => 1287,
                'endFilePos' => 17188,
              ),
            ),
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 362,
                      'endLine' => 362,
                      'startTokenPos' => 1265,
                      'startFilePos' => 17124,
                      'endTokenPos' => 1271,
                      'endFilePos' => 17142,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 362,
                      'endLine' => 362,
                      'startTokenPos' => 1277,
                      'startFilePos' => 17154,
                      'endTokenPos' => 1277,
                      'endFilePos' => 17155,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 362,
            'endLine' => 363,
            'startColumn' => 13,
            'endColumn' => 30,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create new element node with an associated namespace
 * @link https://php.net/manual/en/domdocument.createelementns.php
 * @param string|null $namespace <p>
 * The URI of the namespace.
 * </p>
 * @param string $qualifiedName <p>
 * The qualified name of the element, as prefix:tagname.
 * </p>
 * @param string $value [optional] <p>
 * The value of the element. By default, an empty element will be created.
 * You can also set the value later with DOMElement->nodeValue.
 * </p>
 * @return DOMElement|false The new DOMElement or false if an error occurred.
 * @throws DOMException If invalid $namespace or $qualifiedName
 */',
        'startLine' => 357,
        'endLine' => 366,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'createAttributeNS' => 
      array (
        'name' => 'createAttributeNS',
        'parameters' => 
        array (
          'namespace' => 
          array (
            'name' => 'namespace',
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
                      'name' => 'null',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 380,
                      'endLine' => 380,
                      'startTokenPos' => 1307,
                      'startFilePos' => 17878,
                      'endTokenPos' => 1313,
                      'endFilePos' => 17901,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 380,
                      'endLine' => 380,
                      'startTokenPos' => 1319,
                      'startFilePos' => 17913,
                      'endTokenPos' => 1319,
                      'endFilePos' => 17914,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 380,
            'endLine' => 381,
            'startColumn' => 13,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'qualifiedName' => 
          array (
            'name' => 'qualifiedName',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 382,
                      'endLine' => 382,
                      'startTokenPos' => 1333,
                      'startFilePos' => 18020,
                      'endTokenPos' => 1339,
                      'endFilePos' => 18038,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 382,
                      'endLine' => 382,
                      'startTokenPos' => 1345,
                      'startFilePos' => 18050,
                      'endTokenPos' => 1345,
                      'endFilePos' => 18051,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 382,
            'endLine' => 383,
            'startColumn' => 13,
            'endColumn' => 33,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create new attribute node with an associated namespace
 * @link https://php.net/manual/en/domdocument.createattributens.php
 * @param string|null $namespace <p>
 * The URI of the namespace.
 * </p>
 * @param string $qualifiedName <p>
 * The tag name and prefix of the attribute, as prefix:tagname.
 * </p>
 * @return DOMAttr|false The new DOMAttr or false if an error occurred.
 * @throws DOMException If invalid $namespace or $qualifiedName
 */',
        'startLine' => 379,
        'endLine' => 386,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'getElementsByTagNameNS' => 
      array (
        'name' => 'getElementsByTagNameNS',
        'parameters' => 
        array (
          'namespace' => 
          array (
            'name' => 'namespace',
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
                      'name' => 'null',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 404,
                      'endLine' => 404,
                      'startTokenPos' => 1375,
                      'startFilePos' => 18973,
                      'endTokenPos' => 1381,
                      'endFilePos' => 18996,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 404,
                      'endLine' => 404,
                      'startTokenPos' => 1387,
                      'startFilePos' => 19008,
                      'endTokenPos' => 1387,
                      'endFilePos' => 19009,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 404,
            'endLine' => 405,
            'startColumn' => 13,
            'endColumn' => 34,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'localName' => 
          array (
            'name' => 'localName',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 406,
                      'endLine' => 406,
                      'startTokenPos' => 1401,
                      'startFilePos' => 19115,
                      'endTokenPos' => 1407,
                      'endFilePos' => 19133,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 406,
                      'endLine' => 406,
                      'startTokenPos' => 1413,
                      'startFilePos' => 19145,
                      'endTokenPos' => 1413,
                      'endFilePos' => 19146,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 406,
            'endLine' => 407,
            'startColumn' => 13,
            'endColumn' => 29,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'DOMNodeList',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Searches for all elements with given tag name in specified namespace
 * @link https://php.net/manual/en/domdocument.getelementsbytagnamens.php
 * @param string $namespace <p>
 * The namespace URI of the elements to match on.
 * The special value * matches all namespaces.
 * </p>
 * @param string $localName <p>
 * The local name of the elements to match on.
 * The special value * matches all local names.
 * </p>
 * @return DOMNodeList<DOMElement> A new DOMNodeList object containing all the matched
 * elements.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 402,
        'endLine' => 410,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'getElementById' => 
      array (
        'name' => 'getElementById',
        'parameters' => 
        array (
          'elementId' => 
          array (
            'name' => 'elementId',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 423,
                      'endLine' => 423,
                      'startTokenPos' => 1446,
                      'startFilePos' => 19784,
                      'endTokenPos' => 1452,
                      'endFilePos' => 19802,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 423,
                      'endLine' => 423,
                      'startTokenPos' => 1458,
                      'startFilePos' => 19814,
                      'endTokenPos' => 1458,
                      'endFilePos' => 19815,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 423,
            'endLine' => 424,
            'startColumn' => 13,
            'endColumn' => 29,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
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
                  'name' => 'DOMElement',
                  'isIdentifier' => false,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Searches for an element with a certain id
 * @link https://php.net/manual/en/domdocument.getelementbyid.php
 * @param string $elementId <p>
 * The unique id value for an element.
 * </p>
 * @return DOMElement|null The DOMElement or null if the element is
 * not found.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 421,
        'endLine' => 427,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'adoptNode' => 
      array (
        'name' => 'adoptNode',
        'parameters' => 
        array (
          'node' => 
          array (
            'name' => 'node',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DOMNode',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 431,
            'endLine' => 431,
            'startColumn' => 35,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.3\' => \'DOMNode|false\']',
                'attributes' => 
                array (
                  'startLine' => 430,
                  'endLine' => 430,
                  'startTokenPos' => 1485,
                  'startFilePos' => 20061,
                  'endTokenPos' => 1491,
                  'endFilePos' => 20086,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 430,
                  'endLine' => 430,
                  'startTokenPos' => 1497,
                  'startFilePos' => 20098,
                  'endTokenPos' => 1497,
                  'endFilePos' => 20099,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/** @betterReflectionTentativeReturnType */',
        'startLine' => 429,
        'endLine' => 433,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'append' => 
      array (
        'name' => 'append',
        'parameters' => 
        array (
          'nodes' => 
          array (
            'name' => 'nodes',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 437,
            'endLine' => 437,
            'startColumn' => 32,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * {@inheritDoc}
 */',
        'startLine' => 437,
        'endLine' => 439,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'prepend' => 
      array (
        'name' => 'prepend',
        'parameters' => 
        array (
          'nodes' => 
          array (
            'name' => 'nodes',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 443,
            'endLine' => 443,
            'startColumn' => 33,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * {@inheritDoc}
 */',
        'startLine' => 443,
        'endLine' => 445,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'normalizeDocument' => 
      array (
        'name' => 'normalizeDocument',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Normalizes the document
 * @link https://php.net/manual/en/domdocument.normalizedocument.php
 * @return void
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 452,
        'endLine' => 455,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'renameNode' => 
      array (
        'name' => 'renameNode',
        'parameters' => 
        array (
          'node' => 
          array (
            'name' => 'node',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'DOMNode',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 461,
            'endLine' => 461,
            'startColumn' => 36,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'namespace' => 
          array (
            'name' => 'namespace',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 461,
            'endLine' => 461,
            'startColumn' => 52,
            'endColumn' => 61,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'qualifiedName' => 
          array (
            'name' => 'qualifiedName',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 461,
            'endLine' => 461,
            'startColumn' => 64,
            'endColumn' => 77,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param DOMNode $node
 * @param $namespace
 * @param $qualifiedName
 */',
        'startLine' => 461,
        'endLine' => 463,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'load' => 
      array (
        'name' => 'load',
        'parameters' => 
        array (
          'filename' => 
          array (
            'name' => 'filename',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 482,
                      'endLine' => 482,
                      'startTokenPos' => 1633,
                      'startFilePos' => 21794,
                      'endTokenPos' => 1639,
                      'endFilePos' => 21812,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 482,
                      'endLine' => 482,
                      'startTokenPos' => 1645,
                      'startFilePos' => 21824,
                      'endTokenPos' => 1645,
                      'endFilePos' => 21825,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 482,
            'endLine' => 483,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 485,
                'endLine' => 485,
                'startTokenPos' => 1679,
                'startFilePos' => 21984,
                'endTokenPos' => 1679,
                'endFilePos' => 21984,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 484,
                      'endLine' => 484,
                      'startTokenPos' => 1657,
                      'startFilePos' => 21925,
                      'endTokenPos' => 1663,
                      'endFilePos' => 21940,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 484,
                      'endLine' => 484,
                      'startTokenPos' => 1669,
                      'startFilePos' => 21952,
                      'endTokenPos' => 1669,
                      'endFilePos' => 21953,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 484,
            'endLine' => 485,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.3\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 480,
                  'endLine' => 480,
                  'startTokenPos' => 1607,
                  'startFilePos' => 21649,
                  'endTokenPos' => 1613,
                  'endFilePos' => 21665,
                ),
              ),
              'default' => 
              array (
                'code' => '\'DOMDocument|bool\'',
                'attributes' => 
                array (
                  'startLine' => 480,
                  'endLine' => 480,
                  'startTokenPos' => 1619,
                  'startFilePos' => 21677,
                  'endTokenPos' => 1619,
                  'endFilePos' => 21694,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Load XML from a file
 * @link https://php.net/manual/en/domdocument.load.php
 * @param string $filename <p>
 * The path to the XML document.
 * </p>
 * @param int $options [optional] <p>
 * Bitwise OR
 * of the libxml option constants.
 * </p>
 * @return DOMDocument|bool true on success or false on failure. Prior to PHP 8.3 if called statically, returns a
 * DOMDocument and issues E_STRICT
 * warning.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 479,
        'endLine' => 488,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'save' => 
      array (
        'name' => 'save',
        'parameters' => 
        array (
          'filename' => 
          array (
            'name' => 'filename',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 503,
                      'endLine' => 503,
                      'startTokenPos' => 1703,
                      'startFilePos' => 22688,
                      'endTokenPos' => 1709,
                      'endFilePos' => 22706,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 503,
                      'endLine' => 503,
                      'startTokenPos' => 1715,
                      'startFilePos' => 22718,
                      'endTokenPos' => 1715,
                      'endFilePos' => 22719,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 503,
            'endLine' => 504,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 506,
                'endLine' => 506,
                'startTokenPos' => 1749,
                'startFilePos' => 22878,
                'endTokenPos' => 1749,
                'endFilePos' => 22881,
              ),
            ),
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
                      'name' => 'null',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 505,
                      'endLine' => 505,
                      'startTokenPos' => 1727,
                      'startFilePos' => 22819,
                      'endTokenPos' => 1733,
                      'endFilePos' => 22834,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 505,
                      'endLine' => 505,
                      'startTokenPos' => 1739,
                      'startFilePos' => 22846,
                      'endTokenPos' => 1739,
                      'endFilePos' => 22847,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 505,
            'endLine' => 506,
            'startColumn' => 13,
            'endColumn' => 31,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
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
                  'name' => 'false',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Dumps the internal XML tree back into a file
 * @link https://php.net/manual/en/domdocument.save.php
 * @param string $filename <p>
 * The path to the saved XML document.
 * </p>
 * @param int $options [optional] <p>
 * Additional Options. Currently only LIBXML_NOEMPTYTAG is supported.
 * </p>
 * @return int|false the number of bytes written or false if an error occurred.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 501,
        'endLine' => 509,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'loadXML' => 
      array (
        'name' => 'loadXML',
        'parameters' => 
        array (
          'source' => 
          array (
            'name' => 'source',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 528,
                      'endLine' => 528,
                      'startTokenPos' => 1797,
                      'startFilePos' => 23768,
                      'endTokenPos' => 1803,
                      'endFilePos' => 23786,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 528,
                      'endLine' => 528,
                      'startTokenPos' => 1809,
                      'startFilePos' => 23798,
                      'endTokenPos' => 1809,
                      'endFilePos' => 23799,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 528,
            'endLine' => 529,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 531,
                'endLine' => 531,
                'startTokenPos' => 1843,
                'startFilePos' => 23956,
                'endTokenPos' => 1843,
                'endFilePos' => 23956,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 530,
                      'endLine' => 530,
                      'startTokenPos' => 1821,
                      'startFilePos' => 23897,
                      'endTokenPos' => 1827,
                      'endFilePos' => 23912,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 530,
                      'endLine' => 530,
                      'startTokenPos' => 1833,
                      'startFilePos' => 23924,
                      'endTokenPos' => 1833,
                      'endFilePos' => 23925,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 530,
            'endLine' => 531,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.3\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 526,
                  'endLine' => 526,
                  'startTokenPos' => 1771,
                  'startFilePos' => 23620,
                  'endTokenPos' => 1777,
                  'endFilePos' => 23636,
                ),
              ),
              'default' => 
              array (
                'code' => '\'DOMDocument|bool\'',
                'attributes' => 
                array (
                  'startLine' => 526,
                  'endLine' => 526,
                  'startTokenPos' => 1783,
                  'startFilePos' => 23648,
                  'endTokenPos' => 1783,
                  'endFilePos' => 23665,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Load XML from a string
 * @link https://php.net/manual/en/domdocument.loadxml.php
 * @param string $source <p>
 * The string containing the XML.
 * </p>
 * @param int $options [optional] <p>
 * Bitwise OR
 * of the libxml option constants.
 * </p>
 * @return DOMDocument|bool true on success or false on failure. Prior to PHP 8.3 if called statically, returns a
 * DOMDocument and issues E_STRICT
 * warning.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 525,
        'endLine' => 534,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'saveXML' => 
      array (
        'name' => 'saveXML',
        'parameters' => 
        array (
          'node' => 
          array (
            'name' => 'node',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 551,
                'endLine' => 551,
                'startTokenPos' => 1891,
                'startFilePos' => 24821,
                'endTokenPos' => 1891,
                'endFilePos' => 24824,
              ),
            ),
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
                      'name' => 'DOMNode',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'7.1\' => \'DOMNode|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 550,
                      'endLine' => 550,
                      'startTokenPos' => 1867,
                      'startFilePos' => 24747,
                      'endTokenPos' => 1873,
                      'endFilePos' => 24771,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 550,
                      'endLine' => 550,
                      'startTokenPos' => 1879,
                      'startFilePos' => 24783,
                      'endTokenPos' => 1879,
                      'endFilePos' => 24784,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 550,
            'endLine' => 551,
            'startColumn' => 13,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 554,
                'endLine' => 554,
                'startTokenPos' => 1929,
                'startFilePos' => 25039,
                'endTokenPos' => 1929,
                'endFilePos' => 25039,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
                'isRepeated' => false,
                'arguments' => 
                array (
                  'from' => 
                  array (
                    'code' => '\'7.0\'',
                    'attributes' => 
                    array (
                      'startLine' => 552,
                      'endLine' => 552,
                      'startTokenPos' => 1900,
                      'startFilePos' => 24906,
                      'endTokenPos' => 1900,
                      'endFilePos' => 24910,
                    ),
                  ),
                ),
              ),
              1 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 553,
                      'endLine' => 553,
                      'startTokenPos' => 1907,
                      'startFilePos' => 24980,
                      'endTokenPos' => 1913,
                      'endFilePos' => 24995,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 553,
                      'endLine' => 553,
                      'startTokenPos' => 1919,
                      'startFilePos' => 25007,
                      'endTokenPos' => 1919,
                      'endFilePos' => 25008,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 552,
            'endLine' => 554,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
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
                  'name' => 'false',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Dumps the internal XML tree back into a string
 * @link https://php.net/manual/en/domdocument.savexml.php
 * @param null|DOMNode $node [optional] <p>
 * Use this parameter to output only a specific node without XML declaration
 * rather than the entire document.
 * </p>
 * @param int $options [optional] <p>
 * Additional Options. Currently only LIBXML_NOEMPTYTAG is supported.
 * </p>
 * @return string|false the XML, or false if an error occurred.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 548,
        'endLine' => 557,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'version' => 
          array (
            'name' => 'version',
            'default' => 
            array (
              'code' => '\'1.0\'',
              'attributes' => 
              array (
                'startLine' => 566,
                'endLine' => 566,
                'startTokenPos' => 1976,
                'startFilePos' => 25609,
                'endTokenPos' => 1976,
                'endFilePos' => 25613,
              ),
            ),
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 565,
                      'endLine' => 565,
                      'startTokenPos' => 1954,
                      'startFilePos' => 25544,
                      'endTokenPos' => 1960,
                      'endFilePos' => 25562,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 565,
                      'endLine' => 565,
                      'startTokenPos' => 1966,
                      'startFilePos' => 25574,
                      'endTokenPos' => 1966,
                      'endFilePos' => 25575,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 565,
            'endLine' => 566,
            'startColumn' => 13,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'encoding' => 
          array (
            'name' => 'encoding',
            'default' => 
            array (
              'code' => '\'\'',
              'attributes' => 
              array (
                'startLine' => 568,
                'endLine' => 568,
                'startTokenPos' => 2004,
                'startFilePos' => 25748,
                'endTokenPos' => 2004,
                'endFilePos' => 25749,
              ),
            ),
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 567,
                      'endLine' => 567,
                      'startTokenPos' => 1982,
                      'startFilePos' => 25682,
                      'endTokenPos' => 1988,
                      'endFilePos' => 25700,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 567,
                      'endLine' => 567,
                      'startTokenPos' => 1994,
                      'startFilePos' => 25712,
                      'endTokenPos' => 1994,
                      'endFilePos' => 25713,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 567,
            'endLine' => 568,
            'startColumn' => 13,
            'endColumn' => 33,
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
 * Creates a new DOMDocument object
 * @link https://php.net/manual/en/domdocument.construct.php
 * @param string $version [optional] The version number of the document as part of the XML declaration.
 * @param string $encoding [optional] The encoding of the document as part of the XML declaration.
 */',
        'startLine' => 564,
        'endLine' => 571,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'validate' => 
      array (
        'name' => 'validate',
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Validates the document based on its DTD
 * @link https://php.net/manual/en/domdocument.validate.php
 * @return bool true on success or false on failure.
 * If the document have no DTD attached, this method will return false.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 579,
        'endLine' => 582,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'xinclude' => 
      array (
        'name' => 'xinclude',
        'parameters' => 
        array (
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 596,
                'endLine' => 596,
                'startTokenPos' => 2071,
                'startFilePos' => 26855,
                'endTokenPos' => 2071,
                'endFilePos' => 26855,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 595,
                      'endLine' => 595,
                      'startTokenPos' => 2049,
                      'startFilePos' => 26796,
                      'endTokenPos' => 2055,
                      'endFilePos' => 26811,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 595,
                      'endLine' => 595,
                      'startTokenPos' => 2061,
                      'startFilePos' => 26823,
                      'endTokenPos' => 2061,
                      'endFilePos' => 26824,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 595,
            'endLine' => 596,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
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
                  'name' => 'false',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Substitutes XIncludes in a DOMDocument Object
 * @link https://php.net/manual/en/domdocument.xinclude.php
 * @param int $options [optional] <p>
 * libxml parameters. Available
 * since PHP 5.1.0 and Libxml 2.6.7.
 * </p>
 * @return int|false the number of XIncludes in the document.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 593,
        'endLine' => 599,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'loadHTML' => 
      array (
        'name' => 'loadHTML',
        'parameters' => 
        array (
          'source' => 
          array (
            'name' => 'source',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 618,
                      'endLine' => 618,
                      'startTokenPos' => 2119,
                      'startFilePos' => 27802,
                      'endTokenPos' => 2125,
                      'endFilePos' => 27820,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 618,
                      'endLine' => 618,
                      'startTokenPos' => 2131,
                      'startFilePos' => 27832,
                      'endTokenPos' => 2131,
                      'endFilePos' => 27833,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 618,
            'endLine' => 619,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 621,
                'endLine' => 621,
                'startTokenPos' => 2165,
                'startFilePos' => 27990,
                'endTokenPos' => 2165,
                'endFilePos' => 27990,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 620,
                      'endLine' => 620,
                      'startTokenPos' => 2143,
                      'startFilePos' => 27931,
                      'endTokenPos' => 2149,
                      'endFilePos' => 27946,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 620,
                      'endLine' => 620,
                      'startTokenPos' => 2155,
                      'startFilePos' => 27958,
                      'endTokenPos' => 2155,
                      'endFilePos' => 27959,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 620,
            'endLine' => 621,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.3\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 616,
                  'endLine' => 616,
                  'startTokenPos' => 2093,
                  'startFilePos' => 27653,
                  'endTokenPos' => 2099,
                  'endFilePos' => 27669,
                ),
              ),
              'default' => 
              array (
                'code' => '\'DOMDocument|bool\'',
                'attributes' => 
                array (
                  'startLine' => 616,
                  'endLine' => 616,
                  'startTokenPos' => 2105,
                  'startFilePos' => 27681,
                  'endTokenPos' => 2105,
                  'endFilePos' => 27698,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Load HTML from a string
 * @link https://php.net/manual/en/domdocument.loadhtml.php
 * @param string $source <p>
 * The HTML string.
 * </p>
 * @param int $options [optional] <p>
 * Since PHP 5.4.0 and Libxml 2.6.0, you may also
 * use the options parameter to specify additional Libxml parameters.
 * </p>
 * @return DOMDocument|bool true on success or false on failure. Prior to PHP 8.3 if called statically, returns a
 * DOMDocument and issues E_STRICT
 * warning.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 615,
        'endLine' => 624,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'loadHTMLFile' => 
      array (
        'name' => 'loadHTMLFile',
        'parameters' => 
        array (
          'filename' => 
          array (
            'name' => 'filename',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 643,
                      'endLine' => 643,
                      'startTokenPos' => 2208,
                      'startFilePos' => 28944,
                      'endTokenPos' => 2214,
                      'endFilePos' => 28962,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 643,
                      'endLine' => 643,
                      'startTokenPos' => 2220,
                      'startFilePos' => 28974,
                      'endTokenPos' => 2220,
                      'endFilePos' => 28975,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 643,
            'endLine' => 644,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 646,
                'endLine' => 646,
                'startTokenPos' => 2254,
                'startFilePos' => 29134,
                'endTokenPos' => 2254,
                'endFilePos' => 29134,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 645,
                      'endLine' => 645,
                      'startTokenPos' => 2232,
                      'startFilePos' => 29075,
                      'endTokenPos' => 2238,
                      'endFilePos' => 29090,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 645,
                      'endLine' => 645,
                      'startTokenPos' => 2244,
                      'startFilePos' => 29102,
                      'endTokenPos' => 2244,
                      'endFilePos' => 29103,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 645,
            'endLine' => 646,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.3\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 641,
                  'endLine' => 641,
                  'startTokenPos' => 2182,
                  'startFilePos' => 28791,
                  'endTokenPos' => 2188,
                  'endFilePos' => 28807,
                ),
              ),
              'default' => 
              array (
                'code' => '\'DOMDocument|bool\'',
                'attributes' => 
                array (
                  'startLine' => 641,
                  'endLine' => 641,
                  'startTokenPos' => 2194,
                  'startFilePos' => 28819,
                  'endTokenPos' => 2194,
                  'endFilePos' => 28836,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Load HTML from a file
 * @link https://php.net/manual/en/domdocument.loadhtmlfile.php
 * @param string $filename <p>
 * The path to the HTML file.
 * </p>
 * @param int $options [optional] <p>
 * Since PHP 5.4.0 and Libxml 2.6.0, you may also
 * use the options parameter to specify additional Libxml parameters.
 * </p>
 * @return DOMDocument|bool true on success or false on failure. Prior to PHP 8.3 if called statically, returns a
 * DOMDocument and issues E_STRICT
 * warning.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 640,
        'endLine' => 649,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'saveHTML' => 
      array (
        'name' => 'saveHTML',
        'parameters' => 
        array (
          'node' => 
          array (
            'name' => 'node',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 660,
                'endLine' => 660,
                'startTokenPos' => 2302,
                'startFilePos' => 29776,
                'endTokenPos' => 2302,
                'endFilePos' => 29779,
              ),
            ),
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
                      'name' => 'DOMNode',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'DOMNode|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 659,
                      'endLine' => 659,
                      'startTokenPos' => 2278,
                      'startFilePos' => 29702,
                      'endTokenPos' => 2284,
                      'endFilePos' => 29726,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 659,
                      'endLine' => 659,
                      'startTokenPos' => 2290,
                      'startFilePos' => 29738,
                      'endTokenPos' => 2290,
                      'endFilePos' => 29739,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 659,
            'endLine' => 660,
            'startColumn' => 13,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
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
                  'name' => 'false',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Dumps the internal document into a string using HTML formatting
 * @link https://php.net/manual/en/domdocument.savehtml.php
 * @param null|DOMNode $node [optional] parameter to output a subset of the document.
 * @return string|false The HTML, or false if an error occurred.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 657,
        'endLine' => 663,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'saveHTMLFile' => 
      array (
        'name' => 'saveHTMLFile',
        'parameters' => 
        array (
          'filename' => 
          array (
            'name' => 'filename',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 675,
                      'endLine' => 675,
                      'startTokenPos' => 2331,
                      'startFilePos' => 30391,
                      'endTokenPos' => 2337,
                      'endFilePos' => 30409,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 675,
                      'endLine' => 675,
                      'startTokenPos' => 2343,
                      'startFilePos' => 30421,
                      'endTokenPos' => 2343,
                      'endFilePos' => 30422,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 675,
            'endLine' => 676,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
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
                  'name' => 'false',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Dumps the internal document into a file using HTML formatting
 * @link https://php.net/manual/en/domdocument.savehtmlfile.php
 * @param string $filename <p>
 * The path to the saved HTML document.
 * </p>
 * @return int|false the number of bytes written or false if an error occurred.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 673,
        'endLine' => 679,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'schemaValidate' => 
      array (
        'name' => 'schemaValidate',
        'parameters' => 
        array (
          'filename' => 
          array (
            'name' => 'filename',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 695,
                      'endLine' => 695,
                      'startTokenPos' => 2378,
                      'startFilePos' => 31130,
                      'endTokenPos' => 2384,
                      'endFilePos' => 31148,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 695,
                      'endLine' => 695,
                      'startTokenPos' => 2390,
                      'startFilePos' => 31160,
                      'endTokenPos' => 2390,
                      'endFilePos' => 31161,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 695,
            'endLine' => 696,
            'startColumn' => 13,
            'endColumn' => 28,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'flags' => 
          array (
            'name' => 'flags',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 698,
                'endLine' => 698,
                'startTokenPos' => 2424,
                'startFilePos' => 31318,
                'endTokenPos' => 2424,
                'endFilePos' => 31321,
              ),
            ),
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
                      'name' => 'null',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 697,
                      'endLine' => 697,
                      'startTokenPos' => 2402,
                      'startFilePos' => 31261,
                      'endTokenPos' => 2408,
                      'endFilePos' => 31276,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 697,
                      'endLine' => 697,
                      'startTokenPos' => 2414,
                      'startFilePos' => 31288,
                      'endTokenPos' => 2414,
                      'endFilePos' => 31289,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 697,
            'endLine' => 698,
            'startColumn' => 13,
            'endColumn' => 29,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Validates a document based on a schema
 * @link https://php.net/manual/en/domdocument.schemavalidate.php
 * @param string $filename <p>
 * The path to the schema.
 * </p>
 * @param int $options [optional] <p>
 * Bitwise OR
 * of the libxml option constants.
 * </p>
 * @return bool true on success or false on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 693,
        'endLine' => 701,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'schemaValidateSource' => 
      array (
        'name' => 'schemaValidateSource',
        'parameters' => 
        array (
          'source' => 
          array (
            'name' => 'source',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 715,
                      'endLine' => 715,
                      'startTokenPos' => 2451,
                      'startFilePos' => 32099,
                      'endTokenPos' => 2457,
                      'endFilePos' => 32117,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 715,
                      'endLine' => 715,
                      'startTokenPos' => 2463,
                      'startFilePos' => 32129,
                      'endTokenPos' => 2463,
                      'endFilePos' => 32130,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 715,
            'endLine' => 716,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'flags' => 
          array (
            'name' => 'flags',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 718,
                'endLine' => 718,
                'startTokenPos' => 2497,
                'startFilePos' => 32285,
                'endTokenPos' => 2497,
                'endFilePos' => 32285,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'int\']',
                    'attributes' => 
                    array (
                      'startLine' => 717,
                      'endLine' => 717,
                      'startTokenPos' => 2475,
                      'startFilePos' => 32228,
                      'endTokenPos' => 2481,
                      'endFilePos' => 32243,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 717,
                      'endLine' => 717,
                      'startTokenPos' => 2487,
                      'startFilePos' => 32255,
                      'endTokenPos' => 2487,
                      'endFilePos' => 32256,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 717,
            'endLine' => 718,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Validates a document based on a schema
 * @link https://php.net/manual/en/domdocument.schemavalidatesource.php
 * @param string $source <p>
 * A string containing the schema.
 * </p>
 * @param int $flags [optional] <p>A bitmask of Libxml schema validation flags. Currently the only supported value is <b>LIBXML_SCHEMA_CREATE</b>.
 * Available since PHP 5.5.2 and Libxml 2.6.14.</p>
 * @return bool true on success or false on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 713,
        'endLine' => 721,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'relaxNGValidate' => 
      array (
        'name' => 'relaxNGValidate',
        'parameters' => 
        array (
          'filename' => 
          array (
            'name' => 'filename',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 733,
                      'endLine' => 733,
                      'startTokenPos' => 2524,
                      'startFilePos' => 32827,
                      'endTokenPos' => 2530,
                      'endFilePos' => 32845,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 733,
                      'endLine' => 733,
                      'startTokenPos' => 2536,
                      'startFilePos' => 32857,
                      'endTokenPos' => 2536,
                      'endFilePos' => 32858,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 733,
            'endLine' => 734,
            'startColumn' => 13,
            'endColumn' => 28,
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
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Performs relaxNG validation on the document
 * @link https://php.net/manual/en/domdocument.relaxngvalidate.php
 * @param string $filename <p>
 * The RNG file.
 * </p>
 * @return bool true on success or false on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 731,
        'endLine' => 737,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'relaxNGValidateSource' => 
      array (
        'name' => 'relaxNGValidateSource',
        'parameters' => 
        array (
          'source' => 
          array (
            'name' => 'source',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 749,
                      'endLine' => 749,
                      'startTokenPos' => 2569,
                      'startFilePos' => 33463,
                      'endTokenPos' => 2575,
                      'endFilePos' => 33481,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 749,
                      'endLine' => 749,
                      'startTokenPos' => 2581,
                      'startFilePos' => 33493,
                      'endTokenPos' => 2581,
                      'endFilePos' => 33494,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 749,
            'endLine' => 750,
            'startColumn' => 13,
            'endColumn' => 26,
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
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Performs relaxNG validation on the document
 * @link https://php.net/manual/en/domdocument.relaxngvalidatesource.php
 * @param string $source <p>
 * A string containing the RNG schema.
 * </p>
 * @return bool true on success or false on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 747,
        'endLine' => 753,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
        'aliasName' => NULL,
      ),
      'registerNodeClass' => 
      array (
        'name' => 'registerNodeClass',
        'parameters' => 
        array (
          'baseClass' => 
          array (
            'name' => 'baseClass',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string\']',
                    'attributes' => 
                    array (
                      'startLine' => 772,
                      'endLine' => 772,
                      'startTokenPos' => 2633,
                      'startFilePos' => 34490,
                      'endTokenPos' => 2639,
                      'endFilePos' => 34508,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 772,
                      'endLine' => 772,
                      'startTokenPos' => 2645,
                      'startFilePos' => 34520,
                      'endTokenPos' => 2645,
                      'endFilePos' => 34521,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 772,
            'endLine' => 773,
            'startColumn' => 13,
            'endColumn' => 29,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'extendedClass' => 
          array (
            'name' => 'extendedClass',
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
                      'name' => 'null',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'string|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 774,
                      'endLine' => 774,
                      'startTokenPos' => 2657,
                      'startFilePos' => 34622,
                      'endTokenPos' => 2663,
                      'endFilePos' => 34645,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 774,
                      'endLine' => 774,
                      'startTokenPos' => 2669,
                      'startFilePos' => 34657,
                      'endTokenPos' => 2669,
                      'endFilePos' => 34658,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 774,
            'endLine' => 775,
            'startColumn' => 13,
            'endColumn' => 38,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.4\' => \'true\']',
                'attributes' => 
                array (
                  'startLine' => 770,
                  'endLine' => 770,
                  'startTokenPos' => 2607,
                  'startFilePos' => 34344,
                  'endTokenPos' => 2613,
                  'endFilePos' => 34360,
                ),
              ),
              'default' => 
              array (
                'code' => '\'bool\'',
                'attributes' => 
                array (
                  'startLine' => 770,
                  'endLine' => 770,
                  'startTokenPos' => 2619,
                  'startFilePos' => 34372,
                  'endTokenPos' => 2619,
                  'endFilePos' => 34377,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * Register extended class used to create base node type
 * @link https://php.net/manual/en/domdocument.registernodeclass.php
 * @param string $baseClass <p>
 * The DOM class that you want to extend. You can find a list of these
 * classes in the chapter introduction.
 * </p>
 * @param string $extendedClass <p>
 * Your extended class name. If null is provided, any previously
 * registered class extending baseclass will
 * be removed.
 * </p>
 * @return bool true on success or false on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 769,
        'endLine' => 778,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMDocument',
        'implementingClassName' => 'DOMDocument',
        'currentClassName' => 'DOMDocument',
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