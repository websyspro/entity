<?php

return array (
  'entity' => 
  array (
    0 => 'Proposta',
    1 => 'Proposta',
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
    7 => 'Status',
    8 => 'ContatoId',
    9 => 'InstituicaoId',
    10 => 'Instituicao',
    11 => 'DistribuidorId',
    12 => 'Distribuidor',
    13 => 'ConsultorVendasEspeciaisId',
    14 => 'ConsultorVendasEspeciais',
    15 => 'Observacao',
    16 => 'PrazoFaturamento',
    17 => 'NomeContato',
    18 => 'NomeProposta',
    19 => 'DescontoFinalCliente',
    20 => 'Arquivada',
    21 => 'ComentarioArquivamento',
    22 => 'Versao',
    23 => 'IdPipelineZoho',
    24 => 'Frete',
    25 => 'itemsProposta',
    26 => 'Actived',
    27 => 'ActivedBy',
    28 => 'ActivedAt',
    29 => 'CreatedBy',
    30 => 'CreatedAt',
    31 => 'UpdatedBy',
    32 => 'UpdatedAt',
    33 => 'Deleted',
    34 => 'DeletedBy',
    35 => 'DeletedAt',
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
    'Status' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'ContatoId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'InstituicaoId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'DistribuidorId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'ConsultorVendasEspeciaisId' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Observacao' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'PrazoFaturamento' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'NomeContato' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'NomeProposta' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'DescontoFinalCliente' => 'Websyspro\\Entity\\Decorations\\Columns\\Decimal',
    'Arquivada' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
    'ComentarioArquivamento' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Versao' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'IdPipelineZoho' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
    'Frete' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
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
      0 => 'Proposta',
      1 => 'InstituicaoId',
      2 => 'Instituicao',
      3 => 'Id',
    ),
    1 => 
    array (
      0 => 'Proposta',
      1 => 'DistribuidorId',
      2 => 'Distribuidor',
      3 => 'Id',
    ),
    2 => 
    array (
      0 => 'Proposta',
      1 => 'ConsultorVendasEspeciaisId',
      2 => 'ConsultorVendasEspeciais',
      3 => 'Id',
    ),
  ),
  'primary_keys' => 
  array (
    0 => 'Id',
  ),
  'not_nulls' => 
  array (
    0 => 'IsActive',
    1 => 'IsDeleted',
    2 => 'Created',
    3 => 'CreatedById',
    4 => 'Status',
    5 => 'InstituicaoId',
    6 => 'Arquivada',
    7 => 'Versao',
    8 => 'Frete',
  ),
  'auto_increments' => 
  array (
  ),
);