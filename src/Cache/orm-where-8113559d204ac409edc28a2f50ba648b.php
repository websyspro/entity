<?php

return array (
  'hash' => '1c81ff2a7607ff9799e4138485d21ced',
  'contexts' => 
  array (
    0 => 
    array (
      'object' => 'ExpCompare',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpField',
          'scheme' => 'Proposta',
          'column' => 'Status',
          'columnType' => 'Text',
          'columnMethods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpEqual',
          'values' => 
          array (
            'tokenKey' => 61,
            'tokenValue' => '=',
            'tokenName' => 'T_EQUAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpValue',
          'values' => 
          array (
            0 => 
            array (
              'tokenKey' => 313,
              'tokenValue' => 'Status',
              'tokenName' => 'T_STRING',
            ),
            1 => 
            array (
              'tokenKey' => 402,
              'tokenValue' => '::',
              'tokenName' => 'T_DOUBLE_COLON',
            ),
            2 => 
            array (
              'tokenKey' => 313,
              'tokenValue' => 'Aprovada',
              'tokenName' => 'T_STRING',
            ),
          ),
          'valuesType' => 'Text',
        ),
      ),
    ),
    1 => 
    array (
      'object' => 'ExpLogical',
      'values' => 'And',
    ),
    2 => 
    array (
      'object' => 'ExpIn',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpField',
          'scheme' => 'Proposta',
          'column' => 'NomeProposta',
          'columnType' => 'Text',
          'columnMethods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpValue',
          'values' => 
          array (
            0 => 
            array (
              'tokenKey' => 91,
              'tokenValue' => '[',
              'tokenName' => 'T_START_BRACKET',
            ),
            1 => 
            array (
              'tokenKey' => 317,
              'tokenValue' => '$startsWith',
              'tokenName' => 'T_VARIABLE',
            ),
            2 => 
            array (
              'tokenKey' => 44,
              'tokenValue' => ',',
              'tokenName' => 'T_COMMA',
            ),
            3 => 
            array (
              'tokenKey' => 320,
              'tokenValue' => 'TESTB',
              'tokenName' => 'T_CONSTANT_ENCAPSED_STRING',
            ),
            4 => 
            array (
              'tokenKey' => 93,
              'tokenValue' => ']',
              'tokenName' => 'T_END_BRACKET',
            ),
          ),
          'valuesType' => 'Text',
        ),
      ),
    ),
    3 => 
    array (
      'object' => 'ExpLogical',
      'values' => 'And',
    ),
    4 => 
    array (
      'object' => 'ExpLike',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpField',
          'scheme' => 'Proposta',
          'column' => 'NomeProposta',
          'columnType' => 'Text',
          'columnMethods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpValue',
          'values' => 
          array (
            0 => 
            array (
              'tokenKey' => 320,
              'tokenValue' => '98%',
              'tokenName' => 'T_CONSTANT_ENCAPSED_STRING',
            ),
          ),
        ),
      ),
    ),
    5 => 
    array (
      'object' => 'ExpLogical',
      'values' => 'And',
    ),
    6 => 
    array (
      'object' => 'ExpDenying',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpNotNull',
          'parent' => 'ExpDenying',
          'childs' => 
          array (
            0 => 
            array (
              'object' => 'ExpField',
              'scheme' => 'Proposta',
              'column' => 'NomeProposta',
              'columnType' => 'Text',
              'columnMethods' => 
              array (
              ),
            ),
          ),
        ),
      ),
    ),
    7 => 
    array (
      'object' => 'ExpLogical',
      'values' => 'And',
    ),
    8 => 
    array (
      'object' => 'ExpBetween',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpField',
          'scheme' => 'Proposta',
          'column' => 'Created',
          'columnType' => 'Datetime',
          'columnMethods' => 
          array (
            0 => 
            array (
              'name' => 'date',
              'type' => 'modify',
              'args' => 
              array (
              ),
            ),
          ),
        ),
        1 => 
        array (
          'object' => 'ExpValue',
          'values' => 
          array (
            0 => 
            array (
              'tokenKey' => 317,
              'tokenValue' => '$dateStart',
              'tokenName' => 'T_VARIABLE',
            ),
          ),
          'valuesType' => 'Datetime',
        ),
        2 => 
        array (
          'object' => 'ExpValue',
          'values' => 
          array (
            0 => 
            array (
              'tokenKey' => 317,
              'tokenValue' => '$dateEnd',
              'tokenName' => 'T_VARIABLE',
            ),
          ),
          'valuesType' => 'Datetime',
        ),
      ),
    ),
    9 => 
    array (
      'object' => 'ExpLogical',
      'values' => 'And',
    ),
    10 => 
    array (
      'object' => 'ExpCompare',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpField',
          'scheme' => 'Proposta',
          'column' => 'NomeContato',
          'columnType' => 'Text',
          'columnMethods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpEqual',
          'values' => 
          array (
            'tokenKey' => 60,
            'tokenValue' => '<>',
            'tokenName' => 'T_LESS_THAN',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpValue',
          'values' => 
          array (
            0 => 
            array (
              'tokenKey' => 320,
              'tokenValue' => 'EMERSON',
              'tokenName' => 'T_CONSTANT_ENCAPSED_STRING',
            ),
          ),
          'valuesType' => 'Text',
        ),
      ),
    ),
    11 => 
    array (
      'object' => 'ExpLogical',
      'values' => 'And',
    ),
    12 => 
    array (
      'object' => 'ExpDenying',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpGroup',
          'parent' => 'ExpDenying',
          'childs' => 
          array (
            0 => 
            array (
              'object' => 'ExpLike',
              'parent' => 'ExpDenying',
              'childs' => 
              array (
                0 => 
                array (
                  'object' => 'ExpField',
                  'scheme' => 'Proposta',
                  'column' => 'NomeProposta',
                  'columnType' => 'Text',
                  'columnMethods' => 
                  array (
                    0 => 
                    array (
                      'name' => 'trim',
                      'type' => 'modify',
                      'args' => 
                      array (
                      ),
                    ),
                    1 => 
                    array (
                      'name' => 'upper',
                      'type' => 'modify',
                      'args' => 
                      array (
                      ),
                    ),
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpValue',
                  'values' => 
                  array (
                    0 => 
                    array (
                      'tokenKey' => 313,
                      'tokenValue' => '%',
                      'tokenName' => 'T_STRING',
                    ),
                    1 => 
                    array (
                      'tokenKey' => 317,
                      'tokenValue' => '$startsWith',
                      'tokenName' => 'T_VARIABLE',
                    ),
                  ),
                  'valuesType' => 'Text',
                ),
              ),
            ),
            1 => 
            array (
              'object' => 'ExpLogical',
              'values' => 'Or',
            ),
            2 => 
            array (
              'object' => 'ExpLike',
              'parent' => 'ExpDenying',
              'childs' => 
              array (
                0 => 
                array (
                  'object' => 'ExpField',
                  'scheme' => 'Proposta',
                  'column' => 'NomeProposta',
                  'columnType' => 'Text',
                  'columnMethods' => 
                  array (
                    0 => 
                    array (
                      'name' => 'trim',
                      'type' => 'modify',
                      'args' => 
                      array (
                      ),
                    ),
                    1 => 
                    array (
                      'name' => 'upper',
                      'type' => 'modify',
                      'args' => 
                      array (
                      ),
                    ),
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpValue',
                  'values' => 
                  array (
                    0 => 
                    array (
                      'tokenKey' => 313,
                      'tokenValue' => '%',
                      'tokenName' => 'T_STRING',
                    ),
                    1 => 
                    array (
                      'tokenKey' => 320,
                      'tokenValue' => 'TEST',
                      'tokenName' => 'T_CONSTANT_ENCAPSED_STRING',
                    ),
                  ),
                  'valuesType' => 'Text',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    13 => 
    array (
      'object' => 'ExpLogical',
      'values' => 'And',
    ),
    14 => 
    array (
      'object' => 'ExpDenying',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpSubQuery',
          'parent' => 'ExpDenying',
          'method' => 'any',
          'childs' => 
          array (
            0 => 
            array (
              'object' => 'ExpCompare',
              'parent' => 'ExpSubQuery',
              'childs' => 
              array (
                0 => 
                array (
                  'object' => 'ExpField',
                  'scheme' => 'ItemProposta',
                  'column' => 'PropostaId',
                  'columnType' => 'Text',
                  'columnMethods' => 
                  array (
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpEqual',
                  'values' => 
                  array (
                    'tokenKey' => 61,
                    'tokenValue' => '=',
                    'tokenName' => 'T_EQUAL',
                  ),
                ),
                2 => 
                array (
                  'object' => 'ExpField',
                  'scheme' => 'Proposta',
                  'column' => 'Id',
                  'columnType' => 'Text',
                  'columnMethods' => 
                  array (
                  ),
                ),
              ),
            ),
            1 => 
            array (
              'object' => 'ExpLogical',
              'values' => 'And',
            ),
            2 => 
            array (
              'object' => 'ExpCompare',
              'parent' => 'ExpSubQuery',
              'childs' => 
              array (
                0 => 
                array (
                  'object' => 'ExpField',
                  'scheme' => 'ItemProposta',
                  'column' => 'IsActive',
                  'columnType' => 'Flag',
                  'columnMethods' => 
                  array (
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpEqual',
                  'values' => 
                  array (
                    'tokenKey' => 61,
                    'tokenValue' => '=',
                    'tokenName' => 'T_EQUAL',
                  ),
                ),
                2 => 
                array (
                  'object' => 'ExpValue',
                  'values' => 
                  array (
                    0 => 
                    array (
                      'tokenKey' => 313,
                      'tokenValue' => 'false',
                      'tokenName' => 'T_STRING',
                    ),
                  ),
                  'valuesType' => 'Flag',
                ),
              ),
            ),
            3 => 
            array (
              'object' => 'ExpLogical',
              'values' => 'And',
            ),
            4 => 
            array (
              'object' => 'ExpBetween',
              'parent' => 'ExpSubQuery',
              'childs' => 
              array (
                0 => 
                array (
                  'object' => 'ExpField',
                  'scheme' => 'Proposta',
                  'column' => 'Created',
                  'columnType' => 'Datetime',
                  'columnMethods' => 
                  array (
                    0 => 
                    array (
                      'name' => 'date',
                      'type' => 'modify',
                      'args' => 
                      array (
                      ),
                    ),
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpValue',
                  'values' => 
                  array (
                    0 => 
                    array (
                      'tokenKey' => 317,
                      'tokenValue' => '$dateStart',
                      'tokenName' => 'T_VARIABLE',
                    ),
                  ),
                  'valuesType' => 'Datetime',
                ),
                2 => 
                array (
                  'object' => 'ExpValue',
                  'values' => 
                  array (
                    0 => 
                    array (
                      'tokenKey' => 317,
                      'tokenValue' => '$dateEnd',
                      'tokenName' => 'T_VARIABLE',
                    ),
                  ),
                  'valuesType' => 'Datetime',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    15 => 
    array (
      'object' => 'ExpLogical',
      'values' => 'And',
    ),
    16 => 
    array (
      'object' => 'ExpCompare',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpField',
          'scheme' => 'Proposta',
          'column' => 'IsActive',
          'columnType' => 'Flag',
          'columnMethods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpEqual',
          'values' => 
          array (
            'tokenKey' => 61,
            'tokenValue' => '=',
            'tokenName' => 'T_EQUAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpValue',
          'values' => 
          array (
            0 => 
            array (
              'tokenKey' => 313,
              'tokenValue' => 'true',
              'tokenName' => 'T_STRING',
            ),
          ),
          'valuesType' => 'Flag',
        ),
      ),
    ),
  ),
);