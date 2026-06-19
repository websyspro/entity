<?php

return array (
  'hash' => 'f25a7fc864f23f7095e4422bd15a11c3',
  'contexts' => 
  array (
    0 => 
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
              'tokenKey' => 406,
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
        ),
      ),
    ),
  ),
);