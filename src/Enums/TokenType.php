<?php

namespace Websyspro\Entity\Enums;

enum TokenType
{
  /** Campo de uma entidade (ex: $user->name) */
  case Entity;
  
  /** Variável estática capturada pela closure (ex: $variable) */
  case Static;
  
  /** Valor literal (ex: 'texto', 123) */
  case String;
  
  /** Referência a um enum (ex: Status::Active) */
  case Enum;
  
  /** Operador de comparação (ex: =, >, <) */
  case Compare;
  
  /** Operador lógico (ex: &&, ||, and, or) */
  case Logical;
  
  /** Parêntese de abertura */
  case StartGroup;
  
  /** Parêntese de fechamento */
  case EndGroup;
  
  /** Token vazio */
  case Empty;

  /** Token desconhecido */
  case Unknown;

  /** Token Range */
  case Range;   
  
  /** Token a ser ignorado no processamento */
  case Ignore; 
}