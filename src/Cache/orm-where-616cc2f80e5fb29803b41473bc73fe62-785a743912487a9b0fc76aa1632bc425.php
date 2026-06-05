<?php

return array (
  'hash' => '35463656456cc51f601a4e6a723881fb',
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
            0 => 'ExpGroup',
            1 => 'ExpGroup',
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
                                0 => 320,
                                1 => 'SAO JOAO DA PONTE-MG - 2',
                                2 => 'T_CONSTANT_ENCAPSED_STRING',
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
                        0 => 320,
                        1 => 'SAO JOAO DA PONTE-MG - 2',
                        2 => 'T_CONSTANT_ENCAPSED_STRING',
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
    ),
  ),
);