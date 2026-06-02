<?php

return array (
  'entity' => 
  array (
    0 => 'Distribuidor',
    1 => 'Distribuidor',
  ),
  'columns' => 
  array (
    0 => 'Id',
    1 => 'IsActive',
    2 => 'IsDeleted',
    3 => 'Created',
    4 => 'CreatedById',
    5 => 'Updated',
    6 => 'UpdatedById',
    7 => 'Uf',
    8 => 'Majoracao',
    9 => 'Desconto',
    10 => 'Nome',
    11 => 'MargemLucro',
    12 => 'Cnpj',
    13 => 'Actived',
    14 => 'ActivedBy',
    15 => 'ActivedAt',
    16 => 'CreatedBy',
    17 => 'CreatedAt',
    18 => 'UpdatedBy',
    19 => 'UpdatedAt',
    20 => 'Deleted',
    21 => 'DeletedBy',
    22 => 'DeletedAt',
  ),
  'types' => 
  array (
    'Id' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'IsActive' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'IsDeleted' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'Created' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'CreatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Updated' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'UpdatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Uf' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Majoracao' => 'Websyspro\\Entity\\Decorations\\Columns\\Decimal',
    'Desconto' => 'Websyspro\\Entity\\Decorations\\Columns\\Decimal',
    'Nome' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'MargemLucro' => 'Websyspro\\Entity\\Decorations\\Columns\\Decimal',
    'Cnpj' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
  ),
  'alias' => 
  array (
  ),
  'indexes' => 
  array (
  ),
  'uniques' => 
  array (
  ),
  'foreign_keys' => 
  array (
  ),
  'primary_keys' => 
  array (
    0 => 'Id',
  ),
  'not_nulls' => 
  array (
    'IsActive' => 'IsActive',
    'IsDeleted' => 'IsDeleted',
    'Created' => 'Created',
    'CreatedById' => 'CreatedById',
    'Majoracao' => 'Majoracao',
    'MargemLucro' => 'MargemLucro',
  ),
  'auto_increments' => 
  array (
  ),
);