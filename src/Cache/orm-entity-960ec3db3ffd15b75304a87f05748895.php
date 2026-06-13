<?php

return array (
  'entity' => 
  array (
    0 => 'Empresa',
    1 => 'Empresa',
  ),
  'columns' => 
  array (
    0 => 'Id',
    1 => 'Nome',
    2 => 'Ordem',
    3 => 'CorDestaque',
    4 => 'IsDeleted',
    5 => 'OrdemParaCalculos',
    6 => 'Actived',
    7 => 'ActivedBy',
    8 => 'ActivedAt',
    9 => 'CreatedBy',
    10 => 'CreatedAt',
    11 => 'UpdatedBy',
    12 => 'UpdatedAt',
    13 => 'Deleted',
    14 => 'DeletedBy',
    15 => 'DeletedAt',
  ),
  'types' => 
  array (
    'Id' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Nome' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Ordem' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'CorDestaque' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'IsDeleted' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'OrdemParaCalculos' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
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
    0 => 'Ordem',
    1 => 'IsDeleted',
    2 => 'OrdemParaCalculos',
  ),
  'auto_increments' => 
  array (
  ),
);