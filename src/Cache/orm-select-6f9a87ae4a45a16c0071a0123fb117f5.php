<?php

return array (
  'hash' => '1059f1ad209b35605675e755649d1ba8',
  'contexts' => 
  array (
    0 => 
    array (
      'object' => 'SelField',
      'values' => 
      array (
        'object' => 'ExpField',
        'scheme' => 'Proposta',
        'column' => 'DescontoFinalCliente',
        'columnType' => 'Decimal',
        'columnMethods' => 
        array (
        ),
      ),
    ),
    1 => 
    array (
      'object' => 'SelSeparetor',
      'values' => ',',
    ),
    2 => 
    array (
      'object' => 'SelField',
      'values' => 
      array (
        'object' => 'ExpField',
        'scheme' => 'Proposta',
        'column' => 'DistribuidorId',
        'columnType' => 'Text',
        'columnMethods' => 
        array (
        ),
      ),
    ),
    3 => 
    array (
      'object' => 'SelSeparetor',
      'values' => ',',
    ),
    4 => 
    array (
      'object' => 'SelMethods',
      'method' => 'sum',
      'childs' => 
      array (
        0 => 
        array (
          'object' => 'ExpField',
          'scheme' => 'Proposta',
          'column' => 'DescontoFinalCliente',
          'columnType' => 'Decimal',
          'columnMethods' => 
          array (
          ),
        ),
        1 => 
        array (
          'tokenKey' => 42,
          'tokenValue' => '*',
          'tokenName' => 'T_MULTIPLY',
        ),
        2 => 
        array (
          'object' => 'ExpField',
          'scheme' => 'Proposta',
          'column' => 'DescontoFinalCliente',
          'columnType' => 'Decimal',
          'columnMethods' => 
          array (
          ),
        ),
      ),
    ),
  ),
);