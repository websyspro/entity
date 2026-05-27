<?php

return array (
  'object' => 'ExpressionNode',
  'tokens' => 
  array (
    0 => 
    array (
      'object' => 'ExpressionCompare',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionField',
          'entity' => 'Proposta',
          'field' => 'NomeProposta',
          'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
          'methods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionEqual',
          'tokens' => 
          array (
            'number' => 291,
            'value' => '===',
            'type' => 'T_IS_IDENTICAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'yes',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 91,
              'value' => '[',
              'type' => 'T_START_BRACKET',
            ),
            1 => 
            array (
              'number' => 320,
              'value' => '\'Item 1 \'',
              'type' => 'T_CONSTANT_ENCAPSED_STRING',
            ),
            2 => 
            array (
              'number' => 317,
              'value' => '$concate',
              'type' => 'T_VARIABLE',
            ),
            3 => 
            array (
              'number' => 44,
              'value' => ',',
              'type' => 'T_COMMA',
            ),
            4 => 
            array (
              'number' => 34,
              'value' => '"',
              'type' => 'UNKNOWN',
            ),
            5 => 
            array (
              'number' => 317,
              'value' => '$escolaA',
              'type' => 'T_VARIABLE',
            ),
            6 => 
            array (
              'number' => 34,
              'value' => '"',
              'type' => 'UNKNOWN',
            ),
            7 => 
            array (
              'number' => 44,
              'value' => ',',
              'type' => 'T_COMMA',
            ),
            8 => 
            array (
              'number' => 313,
              'value' => 'Status',
              'type' => 'T_STRING',
            ),
            9 => 
            array (
              'number' => 402,
              'value' => '::',
              'type' => 'T_DOUBLE_COLON',
            ),
            10 => 
            array (
              'number' => 313,
              'value' => 'Aprovada',
              'type' => 'T_STRING',
            ),
            11 => 
            array (
              'number' => 390,
              'value' => '->',
              'type' => 'T_OBJECT_OPERATOR',
            ),
            12 => 
            array (
              'number' => 313,
              'value' => 'value',
              'type' => 'T_STRING',
            ),
            13 => 
            array (
              'number' => 44,
              'value' => ',',
              'type' => 'T_COMMA',
            ),
            14 => 
            array (
              'number' => 313,
              'value' => 'Status',
              'type' => 'T_STRING',
            ),
            15 => 
            array (
              'number' => 402,
              'value' => '::',
              'type' => 'T_DOUBLE_COLON',
            ),
            16 => 
            array (
              'number' => 313,
              'value' => 'RevisaoGerentePendente',
              'type' => 'T_STRING',
            ),
            17 => 
            array (
              'number' => 44,
              'value' => ',',
              'type' => 'T_COMMA',
            ),
            18 => 
            array (
              'number' => 317,
              'value' => '$escolaB',
              'type' => 'T_VARIABLE',
            ),
            19 => 
            array (
              'number' => 44,
              'value' => ',',
              'type' => 'T_COMMA',
            ),
            20 => 
            array (
              'number' => 311,
              'value' => '12',
              'type' => 'T_LNUMBER',
            ),
            21 => 
            array (
              'number' => 93,
              'value' => ']',
              'type' => 'T_END_BRACKET',
            ),
          ),
        ),
      ),
    ),
    1 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    2 => 
    array (
      'object' => 'ExpressionCompare',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionField',
          'entity' => 'Proposta',
          'field' => 'NomeProposta',
          'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
          'methods' => 
          array (
            0 => 
            array (
              'methods' => 'trim',
              'action' => 'modify',
              'args' => 
              array (
              ),
            ),
            1 => 
            array (
              'methods' => 'upper',
              'action' => 'modify',
              'args' => 
              array (
              ),
            ),
            2 => 
            array (
              'methods' => 'contains',
              'action' => 'compare',
              'args' => 
              array (
                0 => ':param_4_0',
              ),
            ),
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionEqual',
          'tokens' => 
          array (
            'number' => 61,
            'value' => '===',
            'type' => 'T_EQUAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'no',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 49,
              'value' => '1',
              'type' => 'UNKNOWN',
            ),
          ),
        ),
      ),
    ),
    3 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    4 => 
    array (
      'object' => 'ExpressionCompare',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionField',
          'entity' => 'Proposta',
          'field' => 'NomeProposta',
          'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
          'methods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionEqual',
          'tokens' => 
          array (
            'number' => 291,
            'value' => '===',
            'type' => 'T_IS_IDENTICAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'no',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 320,
              'value' => '\'%TEST\'',
              'type' => 'T_CONSTANT_ENCAPSED_STRING',
            ),
          ),
        ),
      ),
    ),
    5 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    6 => 
    array (
      'object' => 'ExpressionCompare',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionField',
          'entity' => 'Proposta',
          'field' => 'IsActive',
          'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
          'methods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionEqual',
          'tokens' => 
          array (
            'number' => 61,
            'value' => '===',
            'type' => 'T_EQUAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'no',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 0,
              'value' => '',
              'type' => 'UNKNOWN',
            ),
          ),
        ),
      ),
    ),
    7 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    8 => 
    array (
      'object' => 'ExpressionCompare',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionField',
          'entity' => 'Proposta',
          'field' => 'Status',
          'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
          'methods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionEqual',
          'tokens' => 
          array (
            'number' => 291,
            'value' => '===',
            'type' => 'T_IS_IDENTICAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'no',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 313,
              'value' => 'Status',
              'type' => 'T_STRING',
            ),
            1 => 
            array (
              'number' => 402,
              'value' => '::',
              'type' => 'T_DOUBLE_COLON',
            ),
            2 => 
            array (
              'number' => 313,
              'value' => 'Aprovada',
              'type' => 'T_STRING',
            ),
          ),
        ),
      ),
    ),
    9 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    10 => 
    array (
      'object' => 'ExpressionCompare',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionField',
          'entity' => 'Proposta',
          'field' => 'IsDeleted',
          'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
          'methods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionEqual',
          'tokens' => 
          array (
            'number' => 291,
            'value' => '===',
            'type' => 'T_IS_IDENTICAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'no',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 313,
              'value' => 'false',
              'type' => 'T_STRING',
            ),
          ),
        ),
      ),
    ),
    11 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    12 => 
    array (
      'object' => 'ExpressionGroup',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionCompare',
          'tokens' => 
          array (
            0 => 
            array (
              'object' => 'ExpressionField',
              'entity' => 'Proposta',
              'field' => 'PrazoFaturamento',
              'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
              'methods' => 
              array (
              ),
            ),
            1 => 
            array (
              'object' => 'ExpressionEqual',
              'tokens' => 
              array (
                'number' => 291,
                'value' => '===',
                'type' => 'T_IS_IDENTICAL',
              ),
            ),
            2 => 
            array (
              'object' => 'ExpressionValue',
              'islist' => 'no',
              'tokens' => 
              array (
                0 => 
                array (
                  'number' => 311,
                  'value' => '9098767',
                  'type' => 'T_LNUMBER',
                ),
              ),
            ),
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionLogical',
          'tokens' => 'Or',
        ),
        2 => 
        array (
          'object' => 'ExpressionGroup',
          'tokens' => 
          array (
            0 => 
            array (
              'object' => 'ExpressionCompare',
              'tokens' => 
              array (
                0 => 
                array (
                  'object' => 'ExpressionField',
                  'entity' => 'Proposta',
                  'field' => 'PrazoFaturamento',
                  'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
                  'methods' => 
                  array (
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpressionEqual',
                  'tokens' => 
                  array (
                    'number' => 291,
                    'value' => '===',
                    'type' => 'T_IS_IDENTICAL',
                  ),
                ),
                2 => 
                array (
                  'object' => 'ExpressionValue',
                  'islist' => 'no',
                  'tokens' => 
                  array (
                    0 => 
                    array (
                      'number' => 311,
                      'value' => '11191',
                      'type' => 'T_LNUMBER',
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    13 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    14 => 
    array (
      'object' => 'ExpressionBetween',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionField',
          'entity' => 'Proposta',
          'field' => 'Created',
          'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
          'methods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'no',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 317,
              'value' => '$startDate',
              'type' => 'T_VARIABLE',
            ),
          ),
        ),
        2 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'no',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 320,
              'value' => '\'01/31/2026\'',
              'type' => 'T_CONSTANT_ENCAPSED_STRING',
            ),
          ),
        ),
      ),
    ),
    15 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    16 => 
    array (
      'object' => 'ExpressionCompare',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionField',
          'entity' => 'Proposta',
          'field' => 'IsActive',
          'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
          'methods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionEqual',
          'tokens' => 
          array (
            'number' => 291,
            'value' => '===',
            'type' => 'T_IS_IDENTICAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'no',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 313,
              'value' => 'true',
              'type' => 'T_STRING',
            ),
          ),
        ),
      ),
    ),
    17 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    18 => 
    array (
      'object' => 'ExpressionNegative',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionSubQuery',
          'entity' => 'ItemProposta',
          'methods' => 'Exists',
          'tokens' => 
          array (
            0 => 
            array (
              'object' => 'ExpressionCompare',
              'tokens' => 
              array (
                0 => 
                array (
                  'object' => 'ExpressionField',
                  'entity' => 'ItemProposta',
                  'field' => 'PropostaId',
                  'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
                  'methods' => 
                  array (
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpressionEqual',
                  'tokens' => 
                  array (
                    'number' => 291,
                    'value' => '===',
                    'type' => 'T_IS_IDENTICAL',
                  ),
                ),
                2 => 
                array (
                  'object' => 'ExpressionField',
                  'entity' => 'Proposta',
                  'field' => 'Id',
                  'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
                  'methods' => 
                  array (
                  ),
                ),
              ),
            ),
            1 => 
            array (
              'object' => 'ExpressionLogical',
              'tokens' => 'And',
            ),
            2 => 
            array (
              'object' => 'ExpressionCompare',
              'tokens' => 
              array (
                0 => 
                array (
                  'object' => 'ExpressionField',
                  'entity' => 'ItemProposta',
                  'field' => 'IsActive',
                  'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
                  'methods' => 
                  array (
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpressionEqual',
                  'tokens' => 
                  array (
                    'number' => 291,
                    'value' => '===',
                    'type' => 'T_IS_IDENTICAL',
                  ),
                ),
                2 => 
                array (
                  'object' => 'ExpressionValue',
                  'islist' => 'no',
                  'tokens' => 
                  array (
                    0 => 
                    array (
                      'number' => 313,
                      'value' => 'true',
                      'type' => 'T_STRING',
                    ),
                  ),
                ),
              ),
            ),
            3 => 
            array (
              'object' => 'ExpressionLogical',
              'tokens' => 'And',
            ),
            4 => 
            array (
              'object' => 'ExpressionCompare',
              'tokens' => 
              array (
                0 => 
                array (
                  'object' => 'ExpressionField',
                  'entity' => 'ItemProposta',
                  'field' => 'IsDeleted',
                  'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
                  'methods' => 
                  array (
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpressionEqual',
                  'tokens' => 
                  array (
                    'number' => 291,
                    'value' => '===',
                    'type' => 'T_IS_IDENTICAL',
                  ),
                ),
                2 => 
                array (
                  'object' => 'ExpressionValue',
                  'islist' => 'no',
                  'tokens' => 
                  array (
                    0 => 
                    array (
                      'number' => 313,
                      'value' => 'false',
                      'type' => 'T_STRING',
                    ),
                  ),
                ),
              ),
            ),
            5 => 
            array (
              'object' => 'ExpressionLogical',
              'tokens' => 'And',
            ),
            6 => 
            array (
              'object' => 'ExpressionCompare',
              'tokens' => 
              array (
                0 => 
                array (
                  'object' => 'ExpressionField',
                  'entity' => 'Proposta',
                  'field' => 'NomeContato',
                  'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
                  'methods' => 
                  array (
                  ),
                ),
                1 => 
                array (
                  'object' => 'ExpressionEqual',
                  'tokens' => 
                  array (
                    'number' => 292,
                    'value' => '!==',
                    'type' => 'T_IS_NOT_IDENTICAL',
                  ),
                ),
                2 => 
                array (
                  'object' => 'ExpressionValue',
                  'islist' => 'no',
                  'tokens' => 
                  array (
                    0 => 
                    array (
                      'number' => 320,
                      'value' => '\'EMERSON%\'',
                      'type' => 'T_CONSTANT_ENCAPSED_STRING',
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    19 => 
    array (
      'object' => 'ExpressionLogical',
      'tokens' => 'And',
    ),
    20 => 
    array (
      'object' => 'ExpressionCompare',
      'tokens' => 
      array (
        0 => 
        array (
          'object' => 'ExpressionField',
          'entity' => 'Proposta',
          'field' => 'IsActive',
          'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
          'methods' => 
          array (
          ),
        ),
        1 => 
        array (
          'object' => 'ExpressionEqual',
          'tokens' => 
          array (
            'number' => 291,
            'value' => '===',
            'type' => 'T_IS_IDENTICAL',
          ),
        ),
        2 => 
        array (
          'object' => 'ExpressionValue',
          'islist' => 'no',
          'tokens' => 
          array (
            0 => 
            array (
              'number' => 313,
              'value' => 'false',
              'type' => 'T_STRING',
            ),
          ),
        ),
      ),
    ),
  ),
);