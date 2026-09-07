<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionClass-domnode
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-dev-master@709e512-8.2.12',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\InternalLocatedSource',
      'data' => 
      array (
        'name' => 'DOMNode',
        'filename' => 'phpstorm-stubs:dom/dom_c.stub',
        'extensionName' => 'dom',
        'aliasName' => NULL,
      ),
    ),
    'namespace' => NULL,
    'name' => 'DOMNode',
    'shortName' => 'DOMNode',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * The DOMNode class
 * @link https://php.net/manual/en/class.domnode.php
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 8,
    'endLine' => 415,
    'startColumn' => 5,
    'endColumn' => 5,
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
      'nodeName' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'nodeName',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var string
 * Returns the most accurate name for the current node type
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.nodename
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
                'code' => '[\'8.1\' => \'string\']',
                'attributes' => 
                array (
                  'startLine' => 15,
                  'endLine' => 15,
                  'startTokenPos' => 19,
                  'startFilePos' => 402,
                  'endTokenPos' => 25,
                  'endFilePos' => 420,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 15,
                  'endLine' => 15,
                  'startTokenPos' => 31,
                  'startFilePos' => 432,
                  'endTokenPos' => 31,
                  'endFilePos' => 433,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 15,
        'endLine' => 16,
        'startColumn' => 9,
        'endColumn' => 32,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'nodeValue' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'nodeValue',
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
 * The value of this node, depending on its type
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.nodevalue
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
                  'startLine' => 22,
                  'endLine' => 22,
                  'startTokenPos' => 47,
                  'startFilePos' => 726,
                  'endTokenPos' => 53,
                  'endFilePos' => 749,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 22,
                  'endLine' => 22,
                  'startTokenPos' => 59,
                  'startFilePos' => 761,
                  'endTokenPos' => 59,
                  'endFilePos' => 762,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 22,
        'endLine' => 23,
        'startColumn' => 9,
        'endColumn' => 38,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'nodeType' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'nodeType',
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
        'docComment' => '/**
 * @var int
 * Gets the type of the node. One of the predefined
 * <a href="https://secure.php.net/manual/en/dom.constants.php">XML_xxx_NODE</a> constants
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.nodetype
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
                'code' => '[\'8.1\' => \'int\']',
                'attributes' => 
                array (
                  'startLine' => 30,
                  'endLine' => 30,
                  'startTokenPos' => 77,
                  'startFilePos' => 1154,
                  'endTokenPos' => 83,
                  'endFilePos' => 1169,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 30,
                  'endLine' => 30,
                  'startTokenPos' => 89,
                  'startFilePos' => 1181,
                  'endTokenPos' => 89,
                  'endFilePos' => 1182,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 30,
        'endLine' => 31,
        'startColumn' => 9,
        'endColumn' => 29,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'parentNode' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'parentNode',
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
        'default' => NULL,
        'docComment' => '/**
 * @var DOMNode|null
 * The parent of this node. If there is no such node, this returns NULL.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.parentnode
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
                'code' => '[\'8.1\' => \'DOMNode|null\']',
                'attributes' => 
                array (
                  'startLine' => 37,
                  'endLine' => 37,
                  'startTokenPos' => 105,
                  'startFilePos' => 1498,
                  'endTokenPos' => 111,
                  'endFilePos' => 1522,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 37,
                  'endLine' => 37,
                  'startTokenPos' => 117,
                  'startFilePos' => 1534,
                  'endTokenPos' => 117,
                  'endFilePos' => 1535,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 37,
        'endLine' => 38,
        'startColumn' => 9,
        'endColumn' => 40,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'childNodes' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'childNodes',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'DOMNodeList',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var DOMNodeList<DOMNode>
 * A <classname>DOMNodeList</classname> that contains all children of this node. If there are no children, this is an empty <classname>DOMNodeList</classname>.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.childnodes
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
                'code' => '[\'8.1\' => \'DOMNodeList\']',
                'attributes' => 
                array (
                  'startLine' => 44,
                  'endLine' => 44,
                  'startTokenPos' => 135,
                  'startFilePos' => 1957,
                  'endTokenPos' => 141,
                  'endFilePos' => 1980,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 44,
                  'endLine' => 44,
                  'startTokenPos' => 147,
                  'startFilePos' => 1992,
                  'endTokenPos' => 147,
                  'endFilePos' => 1993,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 44,
        'endLine' => 45,
        'startColumn' => 9,
        'endColumn' => 39,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'firstChild' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'firstChild',
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
        'default' => NULL,
        'docComment' => '/**
 * @var DOMNode|null
 * The first child of this node. If there is no such node, this returns NULL.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.firstchild
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
                'code' => '[\'8.1\' => \'DOMNode|null\']',
                'attributes' => 
                array (
                  'startLine' => 51,
                  'endLine' => 51,
                  'startTokenPos' => 163,
                  'startFilePos' => 2324,
                  'endTokenPos' => 169,
                  'endFilePos' => 2348,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 51,
                  'endLine' => 51,
                  'startTokenPos' => 175,
                  'startFilePos' => 2360,
                  'endTokenPos' => 175,
                  'endFilePos' => 2361,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 51,
        'endLine' => 52,
        'startColumn' => 9,
        'endColumn' => 40,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'lastChild' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'lastChild',
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
        'default' => NULL,
        'docComment' => '/**
 * @var DOMNode|null
 * The last child of this node. If there is no such node, this returns NULL.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.lastchild
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
                'code' => '[\'8.1\' => \'DOMNode|null\']',
                'attributes' => 
                array (
                  'startLine' => 58,
                  'endLine' => 58,
                  'startTokenPos' => 193,
                  'startFilePos' => 2691,
                  'endTokenPos' => 199,
                  'endFilePos' => 2715,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 58,
                  'endLine' => 58,
                  'startTokenPos' => 205,
                  'startFilePos' => 2727,
                  'endTokenPos' => 205,
                  'endFilePos' => 2728,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 58,
        'endLine' => 59,
        'startColumn' => 9,
        'endColumn' => 39,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'previousSibling' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'previousSibling',
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
        'default' => NULL,
        'docComment' => '/**
 * @var DOMNode|null
 * The node immediately preceding this node. If there is no such node, this returns NULL.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.previoussibling
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
                'code' => '[\'8.1\' => \'DOMNode|null\']',
                'attributes' => 
                array (
                  'startLine' => 65,
                  'endLine' => 65,
                  'startTokenPos' => 223,
                  'startFilePos' => 3076,
                  'endTokenPos' => 229,
                  'endFilePos' => 3100,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 65,
                  'endLine' => 65,
                  'startTokenPos' => 235,
                  'startFilePos' => 3112,
                  'endTokenPos' => 235,
                  'endFilePos' => 3113,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 65,
        'endLine' => 66,
        'startColumn' => 9,
        'endColumn' => 45,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'nextSibling' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'nextSibling',
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
        'default' => NULL,
        'docComment' => '/**
 * @var DOMNode|null
 * The node immediately following this node. If there is no such node, this returns NULL.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.nextsibling
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
                'code' => '[\'8.1\' => \'DOMNode|null\']',
                'attributes' => 
                array (
                  'startLine' => 72,
                  'endLine' => 72,
                  'startTokenPos' => 253,
                  'startFilePos' => 3463,
                  'endTokenPos' => 259,
                  'endFilePos' => 3487,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 72,
                  'endLine' => 72,
                  'startTokenPos' => 265,
                  'startFilePos' => 3499,
                  'endTokenPos' => 265,
                  'endFilePos' => 3500,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 72,
        'endLine' => 73,
        'startColumn' => 9,
        'endColumn' => 41,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'attributes' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'attributes',
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
                  'name' => 'DOMNamedNodeMap',
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
 * @var DOMNamedNodeMap<DOMAttr>|null
 * A <classname>DOMNamedNodeMap</classname> containing the attributes of this node (if it is a <classname>DOMElement</classname>) or NULL otherwise.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.attributes
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
                'code' => '[\'8.1\' => \'DOMNamedNodeMap|null\']',
                'attributes' => 
                array (
                  'startLine' => 79,
                  'endLine' => 79,
                  'startTokenPos' => 283,
                  'startFilePos' => 3921,
                  'endTokenPos' => 289,
                  'endFilePos' => 3953,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 79,
                  'endLine' => 79,
                  'startTokenPos' => 295,
                  'startFilePos' => 3965,
                  'endTokenPos' => 295,
                  'endFilePos' => 3966,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 79,
        'endLine' => 80,
        'startColumn' => 9,
        'endColumn' => 48,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'ownerDocument' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
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
 * @var DOMDocument|null
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
                  'startLine' => 86,
                  'endLine' => 86,
                  'startTokenPos' => 313,
                  'startFilePos' => 4373,
                  'endTokenPos' => 319,
                  'endFilePos' => 4401,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 86,
                  'endLine' => 86,
                  'startTokenPos' => 325,
                  'startFilePos' => 4413,
                  'endTokenPos' => 325,
                  'endFilePos' => 4414,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 86,
        'endLine' => 87,
        'startColumn' => 9,
        'endColumn' => 47,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'namespaceURI' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'namespaceURI',
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
 * The namespace URI of this node, or NULL if it is unspecified.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.namespaceuri
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
                  'startLine' => 93,
                  'endLine' => 93,
                  'startTokenPos' => 343,
                  'startFilePos' => 4741,
                  'endTokenPos' => 349,
                  'endFilePos' => 4764,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 93,
                  'endLine' => 93,
                  'startTokenPos' => 355,
                  'startFilePos' => 4776,
                  'endTokenPos' => 355,
                  'endFilePos' => 4777,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 93,
        'endLine' => 94,
        'startColumn' => 9,
        'endColumn' => 41,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'prefix' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'prefix',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var string|null
 * The namespace prefix of this node, or NULL if it is unspecified.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.prefix
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
                'code' => '[\'8.1\' => \'string\']',
                'attributes' => 
                array (
                  'startLine' => 100,
                  'endLine' => 100,
                  'startTokenPos' => 373,
                  'startFilePos' => 5095,
                  'endTokenPos' => 379,
                  'endFilePos' => 5113,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 100,
                  'endLine' => 100,
                  'startTokenPos' => 385,
                  'startFilePos' => 5125,
                  'endTokenPos' => 385,
                  'endFilePos' => 5126,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 100,
        'endLine' => 101,
        'startColumn' => 9,
        'endColumn' => 30,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'localName' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'localName',
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
 * Returns the local part of the qualified name of this node.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.localname
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
                  'startLine' => 107,
                  'endLine' => 107,
                  'startTokenPos' => 401,
                  'startFilePos' => 5430,
                  'endTokenPos' => 407,
                  'endFilePos' => 5453,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 107,
                  'endLine' => 107,
                  'startTokenPos' => 413,
                  'startFilePos' => 5465,
                  'endTokenPos' => 413,
                  'endFilePos' => 5466,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 107,
        'endLine' => 108,
        'startColumn' => 9,
        'endColumn' => 38,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'baseURI' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'baseURI',
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
 * The absolute base URI of this node or NULL if the implementation wasn\'t able to obtain an absolute URI.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.baseuri
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
                  'startLine' => 114,
                  'endLine' => 114,
                  'startTokenPos' => 431,
                  'startFilePos' => 5821,
                  'endTokenPos' => 437,
                  'endFilePos' => 5844,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 114,
                  'endLine' => 114,
                  'startTokenPos' => 443,
                  'startFilePos' => 5856,
                  'endTokenPos' => 443,
                  'endFilePos' => 5857,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 114,
        'endLine' => 115,
        'startColumn' => 9,
        'endColumn' => 36,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'textContent' => 
      array (
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'name' => 'textContent',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => '/**
 * @var string
 * This attribute returns the text content of this node and its descendants.
 * @link https://php.net/manual/en/class.domnode.php#domnode.props.textcontent
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
                'code' => '[\'8.1\' => \'string\']',
                'attributes' => 
                array (
                  'startLine' => 121,
                  'endLine' => 121,
                  'startTokenPos' => 461,
                  'startFilePos' => 6179,
                  'endTokenPos' => 467,
                  'endFilePos' => 6197,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 121,
                  'endLine' => 121,
                  'startTokenPos' => 473,
                  'startFilePos' => 6209,
                  'endTokenPos' => 473,
                  'endFilePos' => 6210,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 121,
        'endLine' => 122,
        'startColumn' => 9,
        'endColumn' => 35,
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
      'insertBefore' => 
      array (
        'name' => 'insertBefore',
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
            'startLine' => 138,
            'endLine' => 138,
            'startColumn' => 13,
            'endColumn' => 26,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'child' => 
          array (
            'name' => 'child',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 140,
                'endLine' => 140,
                'startTokenPos' => 525,
                'startFilePos' => 6953,
                'endTokenPos' => 525,
                'endFilePos' => 6956,
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
                      'startLine' => 139,
                      'endLine' => 139,
                      'startTokenPos' => 501,
                      'startFilePos' => 6871,
                      'endTokenPos' => 507,
                      'endFilePos' => 6895,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'DOMNode\'',
                    'attributes' => 
                    array (
                      'startLine' => 139,
                      'endLine' => 139,
                      'startTokenPos' => 513,
                      'startFilePos' => 6907,
                      'endTokenPos' => 513,
                      'endFilePos' => 6915,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 139,
            'endLine' => 140,
            'startColumn' => 13,
            'endColumn' => 38,
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
 * Adds a new child before a reference node
 * @link https://php.net/manual/en/domnode.insertbefore.php
 * @template TNode of DOMNode
 * @param TNode $node <p>
 * The new node.
 * </p>
 * @param null|DOMNode $child [optional] <p>
 * The reference node. If not supplied, newnode is
 * appended to the children.
 * </p>
 * @return TNode|false The inserted node.
 * @meta
 */',
        'startLine' => 137,
        'endLine' => 143,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'replaceChild' => 
      array (
        'name' => 'replaceChild',
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
            'startLine' => 159,
            'endLine' => 159,
            'startColumn' => 38,
            'endColumn' => 51,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'child' => 
          array (
            'name' => 'child',
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
            'startLine' => 159,
            'endLine' => 159,
            'startColumn' => 54,
            'endColumn' => 68,
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
 * Replaces a child
 * @link https://php.net/manual/en/domnode.replacechild.php
 * @template TNode of DOMNode
 * @param DOMNode $node <p>
 * The new node. It must be a member of the target document, i.e.
 * created by one of the DOMDocument->createXXX() methods or imported in
 * the document by .
 * </p>
 * @param TNode $child <p>
 * The old node.
 * </p>
 * @return TNode|false The old node or false if an error occur.
 * @meta
 */',
        'startLine' => 159,
        'endLine' => 161,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'removeChild' => 
      array (
        'name' => 'removeChild',
        'parameters' => 
        array (
          'child' => 
          array (
            'name' => 'child',
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
            'startLine' => 172,
            'endLine' => 172,
            'startColumn' => 37,
            'endColumn' => 51,
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
 * Removes child from list of children
 * @link https://php.net/manual/en/domnode.removechild.php
 * @template TNode of DOMNode
 * @param TNode $child <p>
 * The removed child.
 * </p>
 * @return TNode|false If the child could be removed the functions returns the old child.
 * @meta
 */',
        'startLine' => 172,
        'endLine' => 174,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'appendChild' => 
      array (
        'name' => 'appendChild',
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
            'startLine' => 185,
            'endLine' => 185,
            'startColumn' => 37,
            'endColumn' => 50,
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
 * Adds new child at the end of the children
 * @link https://php.net/manual/en/domnode.appendchild.php
 * @template TNode of DOMNode
 * @param TNode $node <p>
 * The appended child.
 * </p>
 * @return TNode|false The node added.
 * @meta
 */',
        'startLine' => 185,
        'endLine' => 187,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'hasChildNodes' => 
      array (
        'name' => 'hasChildNodes',
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
 * Checks if node has children
 * @link https://php.net/manual/en/domnode.haschildnodes.php
 * @return bool true on success or false on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 194,
        'endLine' => 197,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'cloneNode' => 
      array (
        'name' => 'cloneNode',
        'parameters' => 
        array (
          'deep' => 
          array (
            'name' => 'deep',
            'default' => 
            array (
              'code' => '\\false',
              'attributes' => 
              array (
                'startLine' => 210,
                'endLine' => 210,
                'startTokenPos' => 654,
                'startFilePos' => 9408,
                'endTokenPos' => 654,
                'endFilePos' => 9412,
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
                    'code' => '\'7.0\'',
                    'attributes' => 
                    array (
                      'startLine' => 208,
                      'endLine' => 208,
                      'startTokenPos' => 625,
                      'startFilePos' => 9276,
                      'endTokenPos' => 625,
                      'endFilePos' => 9280,
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
                      'startLine' => 209,
                      'endLine' => 209,
                      'startTokenPos' => 632,
                      'startFilePos' => 9350,
                      'endTokenPos' => 638,
                      'endFilePos' => 9366,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 209,
                      'endLine' => 209,
                      'startTokenPos' => 644,
                      'startFilePos' => 9378,
                      'endTokenPos' => 644,
                      'endFilePos' => 9379,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 208,
            'endLine' => 210,
            'startColumn' => 13,
            'endColumn' => 30,
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
 * Clones a node
 * @link https://php.net/manual/en/domnode.clonenode.php
 * @param bool $deep <p>
 * Indicates whether to copy all descendant nodes. This parameter is
 * defaulted to false.
 * </p>
 * @return static|false The cloned node.
 */',
        'startLine' => 207,
        'endLine' => 213,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'normalize' => 
      array (
        'name' => 'normalize',
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
 * Normalizes the node
 * @link https://php.net/manual/en/domnode.normalize.php
 * @return void
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 220,
        'endLine' => 223,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'isSupported' => 
      array (
        'name' => 'isSupported',
        'parameters' => 
        array (
          'feature' => 
          array (
            'name' => 'feature',
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
                      'startLine' => 240,
                      'endLine' => 240,
                      'startTokenPos' => 699,
                      'startFilePos' => 10462,
                      'endTokenPos' => 705,
                      'endFilePos' => 10480,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 240,
                      'endLine' => 240,
                      'startTokenPos' => 711,
                      'startFilePos' => 10492,
                      'endTokenPos' => 711,
                      'endFilePos' => 10493,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 240,
            'endLine' => 241,
            'startColumn' => 13,
            'endColumn' => 27,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'version' => 
          array (
            'name' => 'version',
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
                      'startTokenPos' => 723,
                      'startFilePos' => 10592,
                      'endTokenPos' => 729,
                      'endFilePos' => 10610,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 242,
                      'endLine' => 242,
                      'startTokenPos' => 735,
                      'startFilePos' => 10622,
                      'endTokenPos' => 735,
                      'endFilePos' => 10623,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 242,
            'endLine' => 243,
            'startColumn' => 13,
            'endColumn' => 27,
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
 * Checks if feature is supported for specified version
 * @link https://php.net/manual/en/domnode.issupported.php
 * @param string $feature <p>
 * The feature to test. See the example of
 * DOMImplementation::hasFeature for a
 * list of features.
 * </p>
 * @param string $version <p>
 * The version number of the feature to test.
 * </p>
 * @return bool true on success or false on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 238,
        'endLine' => 246,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'hasAttributes' => 
      array (
        'name' => 'hasAttributes',
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
 * Checks if node has attributes
 * @link https://php.net/manual/en/domnode.hasattributes.php
 * @return bool true on success or false on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 253,
        'endLine' => 256,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'compareDocumentPosition' => 
      array (
        'name' => 'compareDocumentPosition',
        'parameters' => 
        array (
          'other' => 
          array (
            'name' => 'other',
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
            'startLine' => 261,
            'endLine' => 261,
            'startColumn' => 49,
            'endColumn' => 63,
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
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.4\' => \'int\']',
                'attributes' => 
                array (
                  'startLine' => 260,
                  'endLine' => 260,
                  'startTokenPos' => 778,
                  'startFilePos' => 11163,
                  'endTokenPos' => 784,
                  'endFilePos' => 11178,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 260,
                  'endLine' => 260,
                  'startTokenPos' => 790,
                  'startFilePos' => 11190,
                  'endTokenPos' => 790,
                  'endFilePos' => 11191,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * @return int
 */',
        'startLine' => 260,
        'endLine' => 263,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'isSameNode' => 
      array (
        'name' => 'isSameNode',
        'parameters' => 
        array (
          'otherNode' => 
          array (
            'name' => 'otherNode',
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
            'startLine' => 274,
            'endLine' => 274,
            'startColumn' => 36,
            'endColumn' => 54,
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
 * Indicates if two nodes are the same node
 * @link https://php.net/manual/en/domnode.issamenode.php
 * @param DOMNode $otherNode <p>
 * The compared node.
 * </p>
 * @return bool true on success or false on failure.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 273,
        'endLine' => 276,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'lookupPrefix' => 
      array (
        'name' => 'lookupPrefix',
        'parameters' => 
        array (
          'namespace' => 
          array (
            'name' => 'namespace',
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
                      'startLine' => 288,
                      'endLine' => 288,
                      'startTokenPos' => 849,
                      'startFilePos' => 12269,
                      'endTokenPos' => 855,
                      'endFilePos' => 12287,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 288,
                      'endLine' => 288,
                      'startTokenPos' => 861,
                      'startFilePos' => 12299,
                      'endTokenPos' => 861,
                      'endFilePos' => 12300,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 288,
            'endLine' => 289,
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
 * Gets the namespace prefix of the node based on the namespace URI
 * @link https://php.net/manual/en/domnode.lookupprefix.php
 * @param string $namespace <p>
 * The namespace URI.
 * </p>
 * @return string The prefix of the namespace.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 286,
        'endLine' => 292,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'isDefaultNamespace' => 
      array (
        'name' => 'isDefaultNamespace',
        'parameters' => 
        array (
          'namespace' => 
          array (
            'name' => 'namespace',
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
                      'startLine' => 305,
                      'endLine' => 305,
                      'startTokenPos' => 895,
                      'startFilePos' => 12967,
                      'endTokenPos' => 901,
                      'endFilePos' => 12985,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 305,
                      'endLine' => 305,
                      'startTokenPos' => 907,
                      'startFilePos' => 12997,
                      'endTokenPos' => 907,
                      'endFilePos' => 12998,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 305,
            'endLine' => 306,
            'startColumn' => 13,
            'endColumn' => 29,
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
 * Checks if the specified namespaceURI is the default namespace or not
 * @link https://php.net/manual/en/domnode.isdefaultnamespace.php
 * @param string $namespace <p>
 * The namespace URI to look for.
 * </p>
 * @return bool Return true if namespaceURI is the default
 * namespace, false otherwise.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 303,
        'endLine' => 309,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'lookupNamespaceURI' => 
      array (
        'name' => 'lookupNamespaceURI',
        'parameters' => 
        array (
          'prefix' => 
          array (
            'name' => 'prefix',
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
            ),
            'startLine' => 321,
            'endLine' => 321,
            'startColumn' => 44,
            'endColumn' => 58,
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
                'code' => '\'8.0\'',
                'attributes' => 
                array (
                  'startLine' => 319,
                  'endLine' => 319,
                  'startTokenPos' => 932,
                  'startFilePos' => 13515,
                  'endTokenPos' => 932,
                  'endFilePos' => 13519,
                ),
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\TentativeType',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'docComment' => '/**
 * Gets the namespace URI of the node based on the prefix
 * @link https://php.net/manual/en/domnode.lookupnamespaceuri.php
 * @param string|null $prefix <p>
 * The prefix of the namespace.
 * </p>
 * @return string|null The namespace URI of the node.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 319,
        'endLine' => 323,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'isEqualNode' => 
      array (
        'name' => 'isEqualNode',
        'parameters' => 
        array (
          'otherNode' => 
          array (
            'name' => 'otherNode',
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
              0 => 
              array (
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.3\' => \'DOMNode|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 330,
                      'endLine' => 330,
                      'startTokenPos' => 991,
                      'startFilePos' => 13948,
                      'endTokenPos' => 997,
                      'endFilePos' => 13972,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'DOMNode\'',
                    'attributes' => 
                    array (
                      'startLine' => 330,
                      'endLine' => 330,
                      'startTokenPos' => 1003,
                      'startFilePos' => 13984,
                      'endTokenPos' => 1003,
                      'endFilePos' => 13992,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 330,
            'endLine' => 331,
            'startColumn' => 13,
            'endColumn' => 30,
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
            'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
            'isRepeated' => false,
            'arguments' => 
            array (
              0 => 
              array (
                'code' => '[\'8.3\' => \'bool\']',
                'attributes' => 
                array (
                  'startLine' => 328,
                  'endLine' => 328,
                  'startTokenPos' => 965,
                  'startFilePos' => 13812,
                  'endTokenPos' => 971,
                  'endFilePos' => 13828,
                ),
              ),
              'default' => 
              array (
                'code' => '\'\'',
                'attributes' => 
                array (
                  'startLine' => 328,
                  'endLine' => 328,
                  'startTokenPos' => 977,
                  'startFilePos' => 13840,
                  'endTokenPos' => 977,
                  'endFilePos' => 13841,
                ),
              ),
            ),
          ),
        ),
        'docComment' => '/**
 * @param DOMNode|null $arg
 * @return bool
 */',
        'startLine' => 328,
        'endLine' => 334,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'getNodePath' => 
      array (
        'name' => 'getNodePath',
        'parameters' => 
        array (
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
 * Gets an XPath location path for the node
 * @return string|null the XPath, or NULL in case of an error.
 * @link https://secure.php.net/manual/en/domnode.getnodepath.php
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 341,
        'endLine' => 344,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'getLineNo' => 
      array (
        'name' => 'getLineNo',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
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
 * Get line number for a node
 * @link https://php.net/manual/en/domnode.getlineno.php
 * @return int Always returns the line number where the node was defined in.
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 351,
        'endLine' => 354,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'C14N' => 
      array (
        'name' => 'C14N',
        'parameters' => 
        array (
          'exclusive' => 
          array (
            'name' => 'exclusive',
            'default' => 
            array (
              'code' => '\\false',
              'attributes' => 
              array (
                'startLine' => 367,
                'endLine' => 367,
                'startTokenPos' => 1098,
                'startFilePos' => 15646,
                'endTokenPos' => 1098,
                'endFilePos' => 15650,
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
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 366,
                      'endLine' => 366,
                      'startTokenPos' => 1076,
                      'startFilePos' => 15583,
                      'endTokenPos' => 1082,
                      'endFilePos' => 15599,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 366,
                      'endLine' => 366,
                      'startTokenPos' => 1088,
                      'startFilePos' => 15611,
                      'endTokenPos' => 1088,
                      'endFilePos' => 15612,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 366,
            'endLine' => 367,
            'startColumn' => 13,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'withComments' => 
          array (
            'name' => 'withComments',
            'default' => 
            array (
              'code' => '\\false',
              'attributes' => 
              array (
                'startLine' => 369,
                'endLine' => 369,
                'startTokenPos' => 1126,
                'startFilePos' => 15785,
                'endTokenPos' => 1126,
                'endFilePos' => 15789,
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
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 368,
                      'endLine' => 368,
                      'startTokenPos' => 1104,
                      'startFilePos' => 15719,
                      'endTokenPos' => 1110,
                      'endFilePos' => 15735,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 368,
                      'endLine' => 368,
                      'startTokenPos' => 1116,
                      'startFilePos' => 15747,
                      'endTokenPos' => 1116,
                      'endFilePos' => 15748,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 368,
            'endLine' => 369,
            'startColumn' => 13,
            'endColumn' => 38,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'xpath' => 
          array (
            'name' => 'xpath',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 371,
                'endLine' => 371,
                'startTokenPos' => 1156,
                'startFilePos' => 15929,
                'endTokenPos' => 1156,
                'endFilePos' => 15932,
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
                      'name' => 'array',
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
                    'code' => '[\'7.1\' => \'array|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 370,
                      'endLine' => 370,
                      'startTokenPos' => 1132,
                      'startFilePos' => 15858,
                      'endTokenPos' => 1138,
                      'endFilePos' => 15880,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 370,
                      'endLine' => 370,
                      'startTokenPos' => 1144,
                      'startFilePos' => 15892,
                      'endTokenPos' => 1144,
                      'endFilePos' => 15893,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 370,
            'endLine' => 371,
            'startColumn' => 13,
            'endColumn' => 36,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'nsPrefixes' => 
          array (
            'name' => 'nsPrefixes',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 373,
                'endLine' => 373,
                'startTokenPos' => 1186,
                'startFilePos' => 16077,
                'endTokenPos' => 1186,
                'endFilePos' => 16080,
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
                      'name' => 'array',
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
                    'code' => '[\'7.1\' => \'array|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 372,
                      'endLine' => 372,
                      'startTokenPos' => 1162,
                      'startFilePos' => 16001,
                      'endTokenPos' => 1168,
                      'endFilePos' => 16023,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 372,
                      'endLine' => 372,
                      'startTokenPos' => 1174,
                      'startFilePos' => 16035,
                      'endTokenPos' => 1174,
                      'endFilePos' => 16036,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 372,
            'endLine' => 373,
            'startColumn' => 13,
            'endColumn' => 41,
            'parameterIndex' => 3,
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
 * Canonicalize nodes to a string
 * @param bool $exclusive [optional] Enable exclusive parsing of only the nodes matched by the provided xpath or namespace prefixes.
 * @param bool $withComments [optional] Retain comments in output.
 * @param null|array $xpath [optional] An array of xpaths to filter the nodes by.
 * @param null|array $nsPrefixes [optional] An array of namespace prefixes to filter the nodes by.
 * @return string|false Canonicalized nodes as a string or FALSE on failure
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 364,
        'endLine' => 376,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      'C14NFile' => 
      array (
        'name' => 'C14NFile',
        'parameters' => 
        array (
          'uri' => 
          array (
            'name' => 'uri',
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
                      'startLine' => 390,
                      'endLine' => 390,
                      'startTokenPos' => 1215,
                      'startFilePos' => 17018,
                      'endTokenPos' => 1221,
                      'endFilePos' => 17036,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 390,
                      'endLine' => 390,
                      'startTokenPos' => 1227,
                      'startFilePos' => 17048,
                      'endTokenPos' => 1227,
                      'endFilePos' => 17049,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 390,
            'endLine' => 391,
            'startColumn' => 13,
            'endColumn' => 23,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'exclusive' => 
          array (
            'name' => 'exclusive',
            'default' => 
            array (
              'code' => '\\false',
              'attributes' => 
              array (
                'startLine' => 393,
                'endLine' => 393,
                'startTokenPos' => 1261,
                'startFilePos' => 17207,
                'endTokenPos' => 1261,
                'endFilePos' => 17211,
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
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 392,
                      'endLine' => 392,
                      'startTokenPos' => 1239,
                      'startFilePos' => 17144,
                      'endTokenPos' => 1245,
                      'endFilePos' => 17160,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 392,
                      'endLine' => 392,
                      'startTokenPos' => 1251,
                      'startFilePos' => 17172,
                      'endTokenPos' => 1251,
                      'endFilePos' => 17173,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 392,
            'endLine' => 393,
            'startColumn' => 13,
            'endColumn' => 35,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'withComments' => 
          array (
            'name' => 'withComments',
            'default' => 
            array (
              'code' => '\\false',
              'attributes' => 
              array (
                'startLine' => 395,
                'endLine' => 395,
                'startTokenPos' => 1289,
                'startFilePos' => 17346,
                'endTokenPos' => 1289,
                'endFilePos' => 17350,
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
                'name' => 'JetBrains\\PhpStorm\\Internal\\LanguageLevelTypeAware',
                'isRepeated' => false,
                'arguments' => 
                array (
                  0 => 
                  array (
                    'code' => '[\'8.0\' => \'bool\']',
                    'attributes' => 
                    array (
                      'startLine' => 394,
                      'endLine' => 394,
                      'startTokenPos' => 1267,
                      'startFilePos' => 17280,
                      'endTokenPos' => 1273,
                      'endFilePos' => 17296,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 394,
                      'endLine' => 394,
                      'startTokenPos' => 1279,
                      'startFilePos' => 17308,
                      'endTokenPos' => 1279,
                      'endFilePos' => 17309,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 394,
            'endLine' => 395,
            'startColumn' => 13,
            'endColumn' => 38,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'xpath' => 
          array (
            'name' => 'xpath',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 397,
                'endLine' => 397,
                'startTokenPos' => 1319,
                'startFilePos' => 17490,
                'endTokenPos' => 1319,
                'endFilePos' => 17493,
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
                      'name' => 'array',
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
                    'code' => '[\'7.1\' => \'array|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 396,
                      'endLine' => 396,
                      'startTokenPos' => 1295,
                      'startFilePos' => 17419,
                      'endTokenPos' => 1301,
                      'endFilePos' => 17441,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 396,
                      'endLine' => 396,
                      'startTokenPos' => 1307,
                      'startFilePos' => 17453,
                      'endTokenPos' => 1307,
                      'endFilePos' => 17454,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 396,
            'endLine' => 397,
            'startColumn' => 13,
            'endColumn' => 36,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
          'nsPrefixes' => 
          array (
            'name' => 'nsPrefixes',
            'default' => 
            array (
              'code' => '\\null',
              'attributes' => 
              array (
                'startLine' => 399,
                'endLine' => 399,
                'startTokenPos' => 1349,
                'startFilePos' => 17638,
                'endTokenPos' => 1349,
                'endFilePos' => 17641,
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
                      'name' => 'array',
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
                    'code' => '[\'7.1\' => \'array|null\']',
                    'attributes' => 
                    array (
                      'startLine' => 398,
                      'endLine' => 398,
                      'startTokenPos' => 1325,
                      'startFilePos' => 17562,
                      'endTokenPos' => 1331,
                      'endFilePos' => 17584,
                    ),
                  ),
                  'default' => 
                  array (
                    'code' => '\'\'',
                    'attributes' => 
                    array (
                      'startLine' => 398,
                      'endLine' => 398,
                      'startTokenPos' => 1337,
                      'startFilePos' => 17596,
                      'endTokenPos' => 1337,
                      'endFilePos' => 17597,
                    ),
                  ),
                ),
              ),
            ),
            'startLine' => 398,
            'endLine' => 399,
            'startColumn' => 13,
            'endColumn' => 41,
            'parameterIndex' => 4,
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
 * Canonicalize nodes to a file.
 * @link https://www.php.net/manual/en/domnode.c14nfile
 * @param string $uri Number of bytes written or FALSE on failure
 * @param bool $exclusive [optional] Enable exclusive parsing of only the nodes matched by the provided xpath or namespace prefixes.
 * @param bool $withComments [optional]  Retain comments in output.
 * @param null|array $xpath [optional] An array of xpaths to filter the nodes by.
 * @param null|array $nsPrefixes [optional] An array of namespace prefixes to filter the nodes by.
 * @return int|false Number of bytes written or FALSE on failure
 * @betterReflectionTentativeReturnType
 */',
        'startLine' => 388,
        'endLine' => 402,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      '__sleep' => 
      array (
        'name' => '__sleep',
        'parameters' => 
        array (
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
        'docComment' => '/**
 * @since 8.1
 */',
        'startLine' => 406,
        'endLine' => 408,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
        'aliasName' => NULL,
      ),
      '__wakeup' => 
      array (
        'name' => '__wakeup',
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
        ),
        'docComment' => '/**
 * @since 8.1
 */',
        'startLine' => 412,
        'endLine' => 414,
        'startColumn' => 9,
        'endColumn' => 9,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => NULL,
        'declaringClassName' => 'DOMNode',
        'implementingClassName' => 'DOMNode',
        'currentClassName' => 'DOMNode',
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