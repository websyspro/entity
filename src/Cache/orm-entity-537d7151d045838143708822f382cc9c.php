<?php

return array (
  'entity' => 
  array (
    0 => 'Instituicao',
    1 => 'Instituicao',
  ),
  'columns' => 
  array (
    0 => 'Id',
    1 => 'Created',
    2 => 'CreatedById',
    3 => 'Updated',
    4 => 'UpdatedById',
    5 => 'RazaoSocial',
    6 => 'CodigoMec',
    7 => 'Tipo',
    8 => 'Logradouro',
    9 => 'Numero',
    10 => 'Cep',
    11 => 'Bairro',
    12 => 'Latitude',
    13 => 'Longitude',
    14 => 'MunicipioId',
    15 => 'Municipio',
    16 => 'Rede',
    17 => 'CNPJ',
    18 => 'Complemento',
    19 => 'CodigoIBGE',
    20 => 'DDD',
    21 => 'Telefone',
    22 => 'Email',
    23 => 'Potencial',
    24 => 'Autonomia',
    25 => 'Localizacao',
    26 => 'IsActive',
    27 => 'IsDeleted',
    28 => 'Sincronizado',
    29 => 'UltimaSincronizacao',
    30 => 'Actived',
    31 => 'ActivedBy',
    32 => 'ActivedAt',
    33 => 'CreatedBy',
    34 => 'CreatedAt',
    35 => 'UpdatedBy',
    36 => 'UpdatedAt',
    37 => 'Deleted',
    38 => 'DeletedBy',
    39 => 'DeletedAt',
  ),
  'types' => 
  array (
    'Id' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Created' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'CreatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Updated' => 'Websyspro\\Entity\\Decorations\\Columns\\Datetime',
    'UpdatedById' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'RazaoSocial' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'CodigoMec' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Tipo' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Logradouro' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Numero' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Cep' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Bairro' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Latitude' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Longitude' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'MunicipioId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Rede' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'CNPJ' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Complemento' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'CodigoIBGE' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'DDD' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Telefone' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Email' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Potencial' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Autonomia' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Localizacao' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
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
    0 => 
    array (
      0 => 'Instituicao',
      1 => 'MunicipioId',
      2 => 'Municipio',
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
    2 => 'RazaoSocial',
    3 => 'CodigoMec',
    4 => 'Tipo',
    5 => 'MunicipioId',
    6 => 'Rede',
    7 => 'Autonomia',
    8 => 'Localizacao',
    9 => 'IsActive',
    10 => 'IsDeleted',
    11 => 'Sincronizado',
  ),
  'auto_increments' => 
  array (
  ),
);