<?php

return array (
  'hash' => 'b45bbd40aeeb2071ec662bcfc8e7ebfd',
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
        0 => 'ExpBetween',
        1 => 'ExpIntial',
        2 => 
        array (
          0 => 
          array (
            0 => 'ExpField',
            1 => 'Proposta',
            2 => 'Created',
            3 => 'Datetime',
            4 => 
            array (
              0 => 
              array (
                0 => 
                array (
                  0 => 'date',
                  1 => 
                  array (
                  ),
                ),
              ),
              1 => 
              array (
              ),
            ),
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
      1 => 
      array (
        0 => 'ExpLogical',
        1 => 'And',
      ),
      2 => 
      array (
        0 => 'ExpCompare',
        1 => 'ExpIntial',
        2 => 
        array (
          0 => 
          array (
            0 => 'ExpField',
            1 => 'Proposta',
            2 => 'IsActive',
            3 => 'Flag',
            4 => 
            array (
              0 => 
              array (
              ),
              1 => 
              array (
              ),
            ),
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
                0 => 313,
                1 => 'true',
                2 => 'T_STRING',
              ),
            ),
          ),
        ),
      ),
      3 => 
      array (
        0 => 'ExpLogical',
        1 => 'And',
      ),
      4 => 
      array (
        0 => 'ExpCompare',
        1 => 'ExpIntial',
        2 => 
        array (
          0 => 
          array (
            0 => 'ExpField',
            1 => 'Proposta',
            2 => 'IsDeleted',
            3 => 'Flag',
            4 => 
            array (
              0 => 
              array (
              ),
              1 => 
              array (
              ),
            ),
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
                0 => 313,
                1 => 'false',
                2 => 'T_STRING',
              ),
            ),
          ),
        ),
      ),
      5 => 
      array (
        0 => 'ExpLogical',
        1 => 'And',
      ),
      6 => 
      array (
        0 => 'ExpCompare',
        1 => 'ExpIntial',
        2 => 
        array (
          0 => 
          array (
            0 => 'ExpField',
            1 => 'Proposta',
            2 => 'CreatedById',
            3 => 'Text',
            4 => 
            array (
              0 => 
              array (
              ),
              1 => 
              array (
              ),
            ),
          ),
          1 => 
          array (
            0 => 'ExpEqual',
            1 => '=',
          ),
          2 => 
          array (
            0 => 'ExpValue',
            1 => 
            array (
              0 => 
              array (
                0 => 320,
                1 => '84850ECB-2443-442A-A9B0-4555C9421227',
                2 => 'T_CONSTANT_ENCAPSED_STRING',
              ),
            ),
          ),
        ),
      ),
      7 => 
      array (
        0 => 'ExpLogical',
        1 => 'And',
      ),
      8 => 
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
                2 => 'NomeProposta',
                3 => 'Text',
                4 => 
                array (
                  0 => 
                  array (
                    0 => 
                    array (
                      0 => 'trim',
                      1 => 
                      array (
                      ),
                    ),
                    1 => 
                    array (
                      0 => 'upper',
                      1 => 
                      array (
                      ),
                    ),
                  ),
                  1 => 
                  array (
                    0 => 
                    array (
                      0 => 'contains',
                      1 => 
                      array (
                        0 => 
                        array (
                          0 => 
                          array (
                            0 => 317,
                            1 => '$startsWith',
                            2 => 'T_VARIABLE',
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
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
                    0 => 313,
                    1 => '%',
                    2 => 'T_STRING',
                  ),
                  1 => 
                  array (
                    0 => 317,
                    1 => '$startsWith',
                    2 => 'T_VARIABLE',
                  ),
                  2 => 
                  array (
                    0 => 313,
                    1 => '%',
                    2 => 'T_STRING',
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