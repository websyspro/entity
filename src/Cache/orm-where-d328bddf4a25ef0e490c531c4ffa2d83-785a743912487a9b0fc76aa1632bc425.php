<?php

return array (
  'hash' => '4e2e174a636c574f47b4cc99a0a33242',
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
            0 => 'ExpCompare',
            1 => 'ExpGroup',
            2 => 
            array (
              0 => 
              array (
                0 => 'ExpField',
                1 => 'Proposta',
                2 => 'ComentarioArquivamento',
                3 => 'Text',
              ),
              1 => 
              array (
                0 => 'ExpEqual',
                1 => '==',
              ),
              2 => 
              array (
                0 => 'ExpValue',
                1 => 
                array (
                  0 => 
                  array (
                    0 => 320,
                    1 => 'Comentario Arquivamento%',
                    2 => 'T_CONSTANT_ENCAPSED_STRING',
                  ),
                ),
              ),
            ),
          ),
          1 => 
          array (
            0 => 'ExpLogical',
            1 => 'And',
          ),
          2 => 
          array (
            0 => 'ExpBetween',
            1 => 'ExpGroup',
            2 => 
            array (
              0 => 
              array (
                0 => 'ExpField',
                1 => 'Proposta',
                2 => 'Created',
                3 => 'Datetime',
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
        ),
      ),
    ),
  ),
);