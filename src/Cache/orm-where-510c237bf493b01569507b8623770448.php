<?php

return array (
  'hash' => '7965b39560ea86b8a83f3e0071213677',
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
  ),
);