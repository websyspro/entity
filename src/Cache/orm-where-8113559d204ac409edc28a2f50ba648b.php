<?php

return array (
  'hash' => '860d70c91a28f6e66d5e068d2b5e5470',
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
  ),
);