<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\LogicalType;

class ExpressionLogical
{
  public string $value;
  public function __construct(
    public Token $token
  ){
    $this->startups();
    $this->startupsClear();
  }
  
  private function startups(
  ): void {
    $this->value = match( $this->token->id ){
      T_BOOLEAN_AND => LogicalType::And->value,
      T_LOGICAL_AND => LogicalType::And->value,
       T_BOOLEAN_OR => LogicalType::Or->value,
       T_LOGICAL_OR => LogicalType::Or->value,
            default => $this->token->value,
    };
  } 
  
  private function startupsClear(
  ): void {
    unset( $this->token );
  }  
}