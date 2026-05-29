<?php

return array (
  'entity' => 
  array (
    'class' => 'Websyspro\\Test\\Crm\\Entitys\\PropostaEntity',
    'alias' => 'Proposta',
    'table' => 'Proposta',
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
    10 => 'DistribuidorId',
    11 => 'ConsultorVendasEspeciaisId',
    12 => 'Observacao',
    13 => 'PrazoFaturamento',
    14 => 'NomeContato',
    15 => 'NomeProposta',
    16 => 'DescontoFinalCliente',
    17 => 'Arquivada',
    18 => 'ComentarioArquivamento',
    19 => 'Versao',
    20 => 'IdPipelineZoho',
    21 => 'Frete',
    22 => 'itemsProposta',
  ),
  'columnsAlias' => 
  array (
  ),
  'tokens' => 
  array (
    0 => 
    array (
      'number' => 348,
      'value' => 'fn',
      'type' => 'T_FN',
    ),
    1 => 
    array (
      'number' => 40,
      'value' => '(',
      'type' => 'T_START_PARENTESES',
    ),
    2 => 
    array (
      'number' => 313,
      'value' => 'PropostaEntity',
      'type' => 'T_STRING',
    ),
    3 => 
    array (
      'number' => 317,
      'value' => '$i',
      'type' => 'T_VARIABLE',
    ),
    4 => 
    array (
      'number' => 41,
      'value' => ')',
      'type' => 'T_END_PARENTESES',
    ),
    5 => 
    array (
      'number' => 269,
      'value' => '=>',
      'type' => 'T_DOUBLE_ARROW',
    ),
    6 => 
    array (
      'number' => 317,
      'value' => '$i',
      'type' => 'T_VARIABLE',
    ),
    7 => 
    array (
      'number' => 390,
      'value' => '->',
      'type' => 'T_OBJECT_OPERATOR',
    ),
    8 => 
    array (
      'number' => 313,
      'value' => 'IsActive',
      'type' => 'T_STRING',
    ),
    9 => 
    array (
      'number' => 289,
      'value' => '==',
      'type' => 'T_IS_EQUAL',
    ),
    10 => 
    array (
      'number' => 313,
      'value' => 'true',
      'type' => 'T_STRING',
    ),
    11 => 
    array (
      'number' => 286,
      'value' => '&&',
      'type' => 'T_BOOLEAN_AND',
    ),
    12 => 
    array (
      'number' => 317,
      'value' => '$i',
      'type' => 'T_VARIABLE',
    ),
    13 => 
    array (
      'number' => 390,
      'value' => '->',
      'type' => 'T_OBJECT_OPERATOR',
    ),
    14 => 
    array (
      'number' => 313,
      'value' => 'IsDeleted',
      'type' => 'T_STRING',
    ),
    15 => 
    array (
      'number' => 289,
      'value' => '==',
      'type' => 'T_IS_EQUAL',
    ),
    16 => 
    array (
      'number' => 313,
      'value' => 'false',
      'type' => 'T_STRING',
    ),
    17 => 
    array (
      'number' => 286,
      'value' => '&&',
      'type' => 'T_BOOLEAN_AND',
    ),
    18 => 
    array (
      'number' => 317,
      'value' => '$i',
      'type' => 'T_VARIABLE',
    ),
    19 => 
    array (
      'number' => 390,
      'value' => '->',
      'type' => 'T_OBJECT_OPERATOR',
    ),
    20 => 
    array (
      'number' => 313,
      'value' => 'ConsultorVendasEspeciaisId',
      'type' => 'T_STRING',
    ),
    21 => 
    array (
      'number' => 290,
      'value' => '!=',
      'type' => 'T_IS_NOT_EQUAL',
    ),
    22 => 
    array (
      'number' => 313,
      'value' => 'null',
      'type' => 'T_STRING',
    ),
    23 => 
    array (
      'number' => 286,
      'value' => '&&',
      'type' => 'T_BOOLEAN_AND',
    ),
    24 => 
    array (
      'number' => 317,
      'value' => '$i',
      'type' => 'T_VARIABLE',
    ),
    25 => 
    array (
      'number' => 390,
      'value' => '->',
      'type' => 'T_OBJECT_OPERATOR',
    ),
    26 => 
    array (
      'number' => 313,
      'value' => 'Id',
      'type' => 'T_STRING',
    ),
    27 => 
    array (
      'number' => 289,
      'value' => '==',
      'type' => 'T_IS_EQUAL',
    ),
    28 => 
    array (
      'number' => 91,
      'value' => '[',
      'type' => 'T_START_BRACKET',
    ),
    29 => 
    array (
      'number' => 320,
      'value' => '\'0303AE33-D883-43C5-262B-08DBD9497C02\'',
      'type' => 'T_CONSTANT_ENCAPSED_STRING',
    ),
    30 => 
    array (
      'number' => 93,
      'value' => ']',
      'type' => 'T_END_BRACKET',
    ),
  ),
);