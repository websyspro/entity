<?php

namespace Websyspro\Entity\Enums;

/**
 * Enum que define os tipos de operadores de comparação SQL
 * 
 * Representa os operadores utilizados em cláusulas WHERE para comparar valores
 */
enum CompareType:string
{
  /** Operador de igualdade (=) */
  case Equals = "=";
  
  /** Operador maior que (>) */
  case Greater = ">";
  
  /** Operador menor que (<) */
  case Less = "<";
  
  /** Operador maior ou igual (>=) */
  case GreaterEqual = ">=";
  
  /** Operador menor ou igual (<=) */
  case LessEqual = "<=";
  
  /** Operador diferente (<>) */
  case NotEqual = "<>";
}