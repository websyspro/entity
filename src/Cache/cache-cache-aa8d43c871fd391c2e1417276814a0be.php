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
    'object' => 'ExpressionNode',
    'tokens' => 
    array (
      0 => 
      array (
        'object' => 'ExpressionCompare',
        'tokens' => 
        array (
          0 => 
          array (
            'object' => 'ExpressionField',
            'entity' => 'Proposta',
            'field' => 'IsActive',
            'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
            'methods' => 
            array (
            ),
          ),
          1 => 
          array (
            'object' => 'ExpressionEqual',
            'tokens' => 
            array (
              'number' => 61,
              'value' => '===',
              'type' => 'T_EQUAL',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'number' => 49,
              'value' => '1',
              'type' => 'UNKNOWN',
            ),
          ),
        ),
      ),
      1 => 
      array (
        'object' => 'ExpressionLogical',
        'tokens' => 'And',
      ),
      2 => 
      array (
        'object' => 'ExpressionCompare',
        'tokens' => 
        array (
          0 => 
          array (
            'object' => 'ExpressionField',
            'entity' => 'Proposta',
            'field' => 'IsDeleted',
            'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Flag',
            'methods' => 
            array (
            ),
          ),
          1 => 
          array (
            'object' => 'ExpressionEqual',
            'tokens' => 
            array (
              'number' => 61,
              'value' => '===',
              'type' => 'T_EQUAL',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'number' => 0,
              'value' => '',
              'type' => 'UNKNOWN',
            ),
          ),
        ),
      ),
      3 => 
      array (
        'object' => 'ExpressionLogical',
        'tokens' => 'And',
      ),
      4 => 
      array (
        'object' => 'ExpressionCompare',
        'tokens' => 
        array (
          0 => 
          array (
            'object' => 'ExpressionField',
            'entity' => 'Proposta',
            'field' => 'ConsultorVendasEspeciaisId',
            'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
            'methods' => 
            array (
            ),
          ),
          1 => 
          array (
            'object' => 'ExpressionEqual',
            'tokens' => 
            array (
              'number' => 290,
              'value' => '!=',
              'type' => 'T_IS_NOT_EQUAL',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'number' => 313,
              'value' => 'null',
              'type' => 'T_STRING',
            ),
          ),
        ),
      ),
      5 => 
      array (
        'object' => 'ExpressionLogical',
        'tokens' => 'And',
      ),
      6 => 
      array (
        'object' => 'ExpressionCompare',
        'tokens' => 
        array (
          0 => 
          array (
            'object' => 'ExpressionField',
            'entity' => 'Proposta',
            'field' => 'NomeProposta',
            'type' => 'Websyspro\\Entity\\Decorations\\Columns\\Text',
            'methods' => 
            array (
            ),
          ),
          1 => 
          array (
            'object' => 'ExpressionEqual',
            'tokens' => 
            array (
              'number' => 291,
              'value' => '===',
              'type' => 'T_IS_IDENTICAL',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'number' => 320,
              'value' => '"MARACANAU - Frio - 1"',
              'type' => 'T_CONSTANT_ENCAPSED_STRING',
            ),
          ),
        ),
      ),
    ),
  ),
);