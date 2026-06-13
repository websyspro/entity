<?php

return array (
  'entity' => 
  array (
    0 => 'ConsultorVendasEspeciais',
    1 => 'ConsultorVendasEspeciais',
  ),
  'columns' => 
  array (
    0 => 'Id',
    1 => 'Created',
    2 => 'CreatedById',
    3 => 'Updated',
    4 => 'UpdatedById',
    5 => 'Nome',
    6 => 'Email',
    7 => 'Ddd',
    8 => 'Celular',
    9 => 'Chapa',
    10 => 'Apelido',
    11 => 'GerenteVendasEspeciaisId',
    12 => 'GerenteVendasEspeciais',
    13 => 'IsActive',
    14 => 'IsDeleted',
    15 => 'Imagem',
    16 => 'Sincronizado',
    17 => 'UltimaSincronizacao',
    18 => 'Especialista',
    19 => 'Actived',
    20 => 'ActivedBy',
    21 => 'ActivedAt',
    22 => 'CreatedBy',
    23 => 'CreatedAt',
    24 => 'UpdatedBy',
    25 => 'UpdatedAt',
    26 => 'Deleted',
    27 => 'DeletedBy',
    28 => 'DeletedAt',
  ),
  'types' => 
  array (
    'Id' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Created' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'CreatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Updated' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'UpdatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Nome' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Email' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Ddd' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Celular' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Chapa' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Apelido' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'GerenteVendasEspeciaisId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'IsActive' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'IsDeleted' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'Imagem' => 'Websyspro\\Entity\\Decorations\\Columns\\LongText',
    'Sincronizado' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'UltimaSincronizacao' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'Especialista' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
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
    0 => 
    array (
      0 => 'ConsultorVendasEspeciais',
      1 => 'GerenteVendasEspeciaisId',
      2 => 'GerenteVendasEspeciais',
      3 => 'Id',
    ),
  ),
  'primary_keys' => 
  array (
    0 => 'Id',
  ),
  'not_nulls' => 
  array (
    0 => 'Created',
    1 => 'CreatedById',
    2 => 'GerenteVendasEspeciaisId',
    3 => 'IsActive',
    4 => 'IsDeleted',
    5 => 'Sincronizado',
    6 => 'Especialista',
  ),
  'auto_increments' => 
  array (
  ),
);