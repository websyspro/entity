<?php

return array (
  'hash' => 'ed7af2115078193095ce49d91aae06fb',
  'contexts' => 
  array (
    0 => 
    array (
      'object' => 'ExpGroup',
      'parent' => 'ExpIntial',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpBetween',
          'parent' => 'ExpGroup',
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
          'object' => 'ExpLike',
          'parent' => 'ExpGroup',
          'values' => 
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
              0 => 'ExpValue',
              1 => 
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
      ),
    ),
  ),
);