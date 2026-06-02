<?php

return array (
  'hash' => '1f9b394782c0919c396ffad2e2fea213',
  'context' => 
  array (
    'scopes' => 
    array (
      0 => 
      array (
        0 => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
        1 => 'Proposta',
        2 => '$p',
      ),
    ),
    'tokens' => 
    array (
      0 => 
      array (
        0 => 'ExpGroup',
        1 => 'ExpIntial',
        2 => 
        array (
          0 => 
          array (
            0 => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
            1 => 'Proposta',
            2 => '$p',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            0 => 'ExpDenying',
            1 => 'ExpGroup',
            2 => 
            array (
              0 => 
              array (
                0 => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                1 => 'Proposta',
                2 => '$p',
              ),
            ),
            3 => 
            array (
              0 => 
              array (
                0 => 'ExpUnary',
                1 => 'ExpDenying',
                2 => 
                array (
                  0 => 
                  array (
                    0 => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                    1 => 'Proposta',
                    2 => '$p',
                  ),
                ),
                3 => 
                array (
                  0 => 'ExpField',
                  1 => 'Proposta',
                  2 => 'IsActive',
                  3 => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
                ),
              ),
            ),
          ),
          1 => 
          array (
            0 => 'ExpLogical',
            1 => 'ExpGroup',
            2 => 
            array (
              0 => 286,
              1 => '&&',
              2 => 'T_BOOLEAN_AND',
            ),
          ),
          2 => 
          array (
            0 => 'ExpBetween',
            1 => 'ExpGroup',
            2 => 
            array (
              0 => 
              array (
                0 => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                1 => 'Proposta',
                2 => '$p',
              ),
            ),
            3 => 
            array (
              0 => 
              array (
                0 => 'ExpField',
                1 => 'Proposta',
                2 => 'Created',
                3 => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
              ),
              1 => 
              array (
                0 => 'ExpValue',
                1 => 
                array (
                  0 => 
                  array (
                    0 => 317,
                    1 => '$dateStart',
                    2 => 'T_VARIABLE',
                  ),
                ),
              ),
              2 => 
              array (
                0 => 'ExpValue',
                1 => 
                array (
                  0 => 
                  array (
                    0 => 317,
                    1 => '$dateEnd',
                    2 => 'T_VARIABLE',
                  ),
                ),
              ),
            ),
          ),
          3 => 
          array (
            0 => 'ExpLogical',
            1 => 'ExpGroup',
            2 => 
            array (
              0 => 286,
              1 => '&&',
              2 => 'T_BOOLEAN_AND',
            ),
          ),
          4 => 
          array (
            0 => 'ExpCompare',
            1 => 'ExpGroup',
            2 => 
            array (
              0 => 
              array (
                0 => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
                1 => 'Proposta',
                2 => '$p',
              ),
            ),
            3 => 
            array (
              0 => 
              array (
                0 => 'ExpField',
                1 => 'Proposta',
                2 => 'NomeContato',
                3 => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
              ),
              1 => 
              array (
                0 => 'ExpEqual',
                1 => 
                array (
                  0 => 60,
                  1 => '<=',
                  2 => 'T_LESS_THAN',
                ),
              ),
              2 => 
              array (
                0 => 'ExpValue',
                1 => 
                array (
                  0 => 
                  array (
                    0 => 320,
                    1 => '\'TEST\'',
                    2 => 'T_CONSTANT_ENCAPSED_STRING',
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