<?php

namespace Websyspro\Entity\Enums;

enum TokenType
{
  /** Campo de uma entidade (ex: $user->name) */
  case FieldEntity;
  
  /** Variável estática capturada pela closure (ex: $variable) */
  case FieldStatic;
  
  /** Valor literal (ex: 'texto', 123) */
  case FieldValue;
  
  /** Referência a um enum (ex: Status::Active) */
  case FieldEnum;
  
  /** Operador de comparação (ex: =, >, <) */
  case Compare;
  
  /** Operador lógico (ex: &&, ||, and, or) */
  case Logical;
  
  /** Parêntese de abertura */
  case StartParent;
  
  /** Parêntese de fechamento */
  case EndParent;
  
  /** Token vazio */
  case Empty;

  /** Token desconhecido */
  case Unknown;  
  
  /** Token a ser ignorado no processamento */
  case FieldIgnore;
}