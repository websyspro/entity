<?php

return array (
  'entity' => 
  array (
    0 => 'Colecao',
    1 => 'Colecao',
  ),
  'columns' => 
  array (
    0 => 'Id',
    1 => 'Created',
    2 => 'CreatedById',
    3 => 'Updated',
    4 => 'UpdatedById',
    5 => 'Nome',
    6 => 'EditoraId',
    7 => 'SegmentoId',
    8 => 'DisciplinaId',
    9 => 'IsActive',
    10 => 'IsDeleted',
    11 => 'Sincronizado',
    12 => 'UltimaSincronizacao',
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
    'Created' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'CreatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Number',
    'Updated' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'UpdatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Number',
    'Nome' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'EditoraId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'SegmentoId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'DisciplinaId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
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
    1 => 'uniques_Id',
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
    'Updated' => 'Updated',
    'UpdatedById' => 'UpdatedById',
    'UltimaSincronizacao' => 'UltimaSincronizacao',
  ),
  'auto_increments' => 
  array (
  ),
);