<?php

return array (
  'entity' => 
  array (
    0 => 'Obra',
    1 => 'Obra',
  ),
  'columns' => 
  array (
    0 => 'Id',
    1 => 'Created',
    2 => 'CreatedById',
    3 => 'Updated',
    4 => 'UpdatedById',
    5 => 'Titulo',
    6 => 'ColecaoId',
    7 => 'Colecao',
    8 => 'EditoraId',
    9 => 'Editora',
    10 => 'IsActive',
    11 => 'IsDeleted',
    12 => 'CodigoISBN',
    13 => 'SegmentoId',
    14 => 'Segmento',
    15 => 'SKU',
    16 => 'ObrasIdRelacionadas',
    17 => 'TipoObra',
    18 => 'EmpresaId',
    19 => 'Empresa',
    20 => 'ZohoId',
    21 => 'ValoresObra',
    22 => 'Actived',
    23 => 'ActivedBy',
    24 => 'ActivedAt',
    25 => 'CreatedBy',
    26 => 'CreatedAt',
    27 => 'UpdatedBy',
    28 => 'UpdatedAt',
    29 => 'Deleted',
    30 => 'DeletedBy',
    31 => 'DeletedAt',
  ),
  'types' => 
  array (
    'Id' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Created' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'CreatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Updated' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'UpdatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Titulo' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'ColecaoId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'EditoraId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'IsActive' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'IsDeleted' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'CodigoISBN' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'SegmentoId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'SKU' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'ObrasIdRelacionadas' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'TipoObra' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'EmpresaId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'ZohoId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
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
    'ColecaoId' => 
    array (
      0 => 'Obra',
      1 => 'ColecaoId',
      2 => 'Colecao',
      3 => 'Id',
    ),
    'EditoraId' => 
    array (
      0 => 'Obra',
      1 => 'EditoraId',
      2 => 'Editora',
      3 => 'Id',
    ),
    'SegmentoId' => 
    array (
      0 => 'Obra',
      1 => 'SegmentoId',
      2 => 'Segmento',
      3 => 'Id',
    ),
    'EmpresaId' => 
    array (
      0 => 'Obra',
      1 => 'EmpresaId',
      2 => 'Empresa',
      3 => 'Id',
    ),
  ),
  'primary_keys' => 
  array (
    0 => 'Id',
  ),
  'not_nulls' => 
  array (
    'Created' => 'Created',
    'CreatedById' => 'CreatedById',
    'ColecaoId' => 'ColecaoId',
    'EditoraId' => 'EditoraId',
    'IsActive' => 'IsActive',
    'IsDeleted' => 'IsDeleted',
    'SegmentoId' => 'SegmentoId',
    'SKU' => 'SKU',
    'TipoObra' => 'TipoObra',
  ),
  'auto_increments' => 
  array (
  ),
);