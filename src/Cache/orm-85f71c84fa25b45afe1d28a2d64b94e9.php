<?php

return array (
  'uses' => 
  array (
    0 => 
    array (
      'use' => 'Websyspro\\Entity\\Shareds\\WhereByClosure',
      'key' => 'WhereByClosure',
    ),
    1 => 
    array (
      'use' => 'Websyspro\\Test\\Crm\\Entitys\\ItemPropostaEntity',
      'key' => 'ItemPropostaEntity',
    ),
    2 => 
    array (
      'use' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
      'key' => 'Props',
    ),
    3 => 
    array (
      'use' => 'Websyspro\\Test\\Enums\\Status',
      'key' => 'Status',
    ),
  ),
  'classe' => 'UserRepository',
  'method' => 'getAll',
  'caches' => 'yes',
  'scopes' => 
  array (
    0 => 
    array (
      'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
      'variable' => '$p',
    ),
  ),
  'tokens' => 
  array (
    'object' => 'ExpNode',
    'parent' => 'ExpInit',
    'scopes' => 
    array (
      0 => 
      array (
        'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
        'variable' => '$p',
      ),
    ),
    'tokens' => 
    array (
      0 => 
      array (
        'object' => 'ExpGroup',
        'parent' => 'ExpNode',
        'scopes' => 
        array (
          0 => 
          array (
            'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
            'variable' => '$p',
          ),
        ),
        'tokens' => 
        array (
          0 => 
          array (
            'object' => 'ExpUnary',
            'parent' => 'ExpGroup',
            'scopes' => 
            array (
              0 => 
              array (
                'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                'variable' => '$p',
              ),
            ),
            'tokens' => 
            array (
              'object' => 'ExpField',
              'events' => 
              array (
              ),
              'table' => 'Proposta',
              'field' => 'IsActive',
              'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
            ),
          ),
          1 => 
          array (
            'object' => 'ExpLog',
            'parent' => 'ExpGroup',
            'tokens' => 
            array (
              0 => 
              array (
                'type' => 286,
                'text' => '&&',
                'name' => 'T_BOOLEAN_AND',
              ),
            ),
          ),
          2 => 
          array (
            'object' => 'ExpNeg',
            'parent' => 'ExpGroup',
            'scopes' => 
            array (
              0 => 
              array (
                'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                'variable' => '$p',
              ),
            ),
            'tokens' => 
            array (
              0 => 
              array (
                'object' => 'ExpUnary',
                'parent' => 'ExpNeg',
                'scopes' => 
                array (
                  0 => 
                  array (
                    'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                    'variable' => '$p',
                  ),
                ),
                'tokens' => 
                array (
                  'object' => 'ExpField',
                  'events' => 
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
                      'name' => 'startWith',
                      'type' => 'compare',
                      'args' => 
                      array (
                        0 => 
                        array (
                          'type' => 320,
                          'text' => '"Test"',
                          'name' => 'T_CONSTANT_ENCAPSED_STRING',
                        ),
                      ),
                    ),
                  ),
                  'table' => 'Proposta',
                  'field' => 'NomeContato',
                  'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
                ),
              ),
            ),
          ),
          3 => 
          array (
            'object' => 'ExpLog',
            'parent' => 'ExpGroup',
            'tokens' => 
            array (
              0 => 
              array (
                'type' => 286,
                'text' => '&&',
                'name' => 'T_BOOLEAN_AND',
              ),
            ),
          ),
          4 => 
          array (
            'object' => 'ExpCompare',
            'parent' => 'ExpGroup',
            'scopes' => 
            array (
              0 => 
              array (
                'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                'variable' => '$p',
              ),
            ),
            'tokens' => 
            array (
              0 => 
              array (
                'object' => 'ExpField',
                'events' => 
                array (
                  0 => 
                  array (
                    'name' => 'trim',
                    'type' => 'modify',
                    'args' => 
                    array (
                    ),
                  ),
                ),
                'table' => 'Proposta',
                'field' => 'NomeContato',
                'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
              ),
              1 => 
              array (
                'object' => 'ExpEqual',
                'tokens' => 
                array (
                  0 => 
                  array (
                    'type' => 289,
                    'text' => '==',
                    'name' => 'T_IS_EQUAL',
                  ),
                ),
              ),
              2 => 
              array (
                'object' => 'ExpValue',
                'islist' => false,
                'tokens' => 
                array (
                  0 => 
                  array (
                    'type' => 320,
                    'text' => '\'TEST\'',
                    'name' => 'T_CONSTANT_ENCAPSED_STRING',
                  ),
                ),
              ),
            ),
          ),
          5 => 
          array (
            'object' => 'ExpLog',
            'parent' => 'ExpGroup',
            'tokens' => 
            array (
              0 => 
              array (
                'type' => 286,
                'text' => '&&',
                'name' => 'T_BOOLEAN_AND',
              ),
            ),
          ),
          6 => 
          array (
            'object' => 'ExpGroup',
            'parent' => 'ExpGroup',
            'scopes' => 
            array (
              0 => 
              array (
                'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                'variable' => '$p',
              ),
            ),
            'tokens' => 
            array (
              0 => 
              array (
                'object' => 'ExpCompare',
                'parent' => 'ExpGroup',
                'scopes' => 
                array (
                  0 => 
                  array (
                    'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                    'variable' => '$p',
                  ),
                ),
                'tokens' => 
                array (
                  0 => 
                  array (
                    'object' => 'ExpField',
                    'events' => 
                    array (
                    ),
                    'table' => 'Proposta',
                    'field' => 'NomeContato',
                    'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
                  ),
                  1 => 
                  array (
                    'object' => 'ExpEqual',
                    'tokens' => 
                    array (
                      0 => 
                      array (
                        'type' => 289,
                        'text' => '==',
                        'name' => 'T_IS_EQUAL',
                      ),
                    ),
                  ),
                  2 => 
                  array (
                    'object' => 'ExpValue',
                    'islist' => false,
                    'tokens' => 
                    array (
                      0 => 
                      array (
                        'type' => 320,
                        'text' => '\'TEST\'',
                        'name' => 'T_CONSTANT_ENCAPSED_STRING',
                      ),
                    ),
                  ),
                ),
              ),
              1 => 
              array (
                'object' => 'ExpLog',
                'parent' => 'ExpGroup',
                'tokens' => 
                array (
                  0 => 
                  array (
                    'type' => 286,
                    'text' => '&&',
                    'name' => 'T_BOOLEAN_AND',
                  ),
                ),
              ),
              2 => 
              array (
                'object' => 'ExpGroup',
                'parent' => 'ExpGroup',
                'scopes' => 
                array (
                  0 => 
                  array (
                    'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                    'variable' => '$p',
                  ),
                ),
                'tokens' => 
                array (
                  0 => 
                  array (
                    'object' => 'ExpCompare',
                    'parent' => 'ExpGroup',
                    'scopes' => 
                    array (
                      0 => 
                      array (
                        'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                        'variable' => '$p',
                      ),
                    ),
                    'tokens' => 
                    array (
                      0 => 
                      array (
                        'object' => 'ExpField',
                        'events' => 
                        array (
                        ),
                        'table' => 'Proposta',
                        'field' => 'NomeContato',
                        'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
                      ),
                      1 => 
                      array (
                        'object' => 'ExpEqual',
                        'tokens' => 
                        array (
                          0 => 
                          array (
                            'type' => 289,
                            'text' => '==',
                            'name' => 'T_IS_EQUAL',
                          ),
                        ),
                      ),
                      2 => 
                      array (
                        'object' => 'ExpValue',
                        'islist' => false,
                        'tokens' => 
                        array (
                          0 => 
                          array (
                            'type' => 320,
                            'text' => '\'TEST\'',
                            'name' => 'T_CONSTANT_ENCAPSED_STRING',
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
          7 => 
          array (
            'object' => 'ExpLog',
            'parent' => 'ExpGroup',
            'tokens' => 
            array (
              0 => 
              array (
                'type' => 286,
                'text' => '&&',
                'name' => 'T_BOOLEAN_AND',
              ),
            ),
          ),
          8 => 
          array (
            'object' => 'ExpCompare',
            'parent' => 'ExpGroup',
            'scopes' => 
            array (
              0 => 
              array (
                'instance' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                'variable' => '$p',
              ),
            ),
            'tokens' => 
            array (
              0 => 
              array (
                'object' => 'ExpField',
                'events' => 
                array (
                ),
                'table' => 'Proposta',
                'field' => 'Id',
                'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
              ),
              1 => 
              array (
                'object' => 'ExpEqual',
                'tokens' => 
                array (
                  0 => 
                  array (
                    'type' => 289,
                    'text' => '==',
                    'name' => 'T_IS_EQUAL',
                  ),
                ),
              ),
              2 => 
              array (
                'object' => 'ExpValue',
                'islist' => true,
                'tokens' => 
                array (
                  0 => 
                  array (
                    'type' => 91,
                    'text' => '[',
                    'name' => 'T_START_BRACKET',
                  ),
                  1 => 
                  array (
                    'type' => 320,
                    'text' => '\'0303AE33-D883-43C5-262B-08DBD9497C02\'',
                    'name' => 'T_CONSTANT_ENCAPSED_STRING',
                  ),
                  2 => 
                  array (
                    'type' => 44,
                    'text' => ',',
                    'name' => 'T_COMMA',
                  ),
                  3 => 
                  array (
                    'type' => 320,
                    'text' => '\'0303AE33-D883-43C5-262B-08DBD9497C02\'',
                    'name' => 'T_CONSTANT_ENCAPSED_STRING',
                  ),
                  4 => 
                  array (
                    'type' => 44,
                    'text' => ',',
                    'name' => 'T_COMMA',
                  ),
                  5 => 
                  array (
                    'type' => 320,
                    'text' => '\'0303AE33-D883-43C5-262B-08DBD9497C02\'',
                    'name' => 'T_CONSTANT_ENCAPSED_STRING',
                  ),
                  6 => 
                  array (
                    'type' => 44,
                    'text' => ',',
                    'name' => 'T_COMMA',
                  ),
                  7 => 
                  array (
                    'type' => 320,
                    'text' => '\'0303AE33-D883-43C5-262B-08DBD9497C02\'',
                    'name' => 'T_CONSTANT_ENCAPSED_STRING',
                  ),
                  8 => 
                  array (
                    'type' => 93,
                    'text' => ']',
                    'name' => 'T_END_BRACKET',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
);