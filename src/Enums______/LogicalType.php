<?php

namespace Websyspro\Entity\Enums;

/**
 * Enum que define os operadores lógicos SQL
 * 
 * Representa os operadores utilizados para combinar condições em cláusulas WHERE
 */
enum LogicalType: string
{
  case Between = "Between";

  /** Operador lógico AND - todas as condições devem ser verdadeiras */
  case And = "And";
  
  /** Operador lógico OR - pelo menos uma condição deve ser verdadeira */
  case Or = 'Or';
}