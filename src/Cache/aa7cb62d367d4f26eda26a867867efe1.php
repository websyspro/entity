<?php

return array (
  'entity' => 
  array (
    0 => 'Municipio',
    1 => 'Municipio',
  ),
  'columns' => 
  array (
    0 => 'Id',
    1 => 'Created',
    2 => 'CreatedById',
    3 => 'Updated',
    4 => 'UpdatedById',
    5 => 'Nome',
    6 => 'Uf',
    7 => 'IsActive',
    8 => 'IsDeleted',
    9 => 'Sincronizado',
    10 => 'UltimaSincronizacao',
    11 => 'Actived',
    12 => 'ActivedBy',
    13 => 'ActivedAt',
    14 => 'CreatedBy',
    15 => 'CreatedAt',
    16 => 'UpdatedBy',
    17 => 'UpdatedAt',
    18 => 'Deleted',
    19 => 'DeletedBy',
    20 => 'DeletedAt',
  ),
  'types' => 
  array (
    'Id' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Created' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'CreatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Updated' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'UpdatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Nome' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Uf' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'IsActive' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'IsDeleted' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'Sincronizado' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'UltimaSincronizacao' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
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
    'Created' => 'Created',
    'CreatedById' => 'CreatedById',
    'Nome' => 'Nome',
    'Uf' => 'Uf',
    'IsActive' => 'IsActive',
    'IsDeleted' => 'IsDeleted',
    'Sincronizado' => 'Sincronizado',
  ),
  'auto_increments' => 
  array (
  ),
);