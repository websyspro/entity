<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\LogicalType;

class ExpressionLogical
{
  public string $value;
  public function __construct(
    Token $token
  ){
    $this->startups( $token );
  }

  public function get(
  ): string {
    return $this->value;
  }  
  
  private function startups(
    Token $token
  ): void {
    $this->value = match( $token->id ){
      T_BOOLEAN_AND => LogicalType::And->value,
      T_LOGICAL_AND => LogicalType::And->value,
       T_BOOLEAN_OR => LogicalType::Or->value,
       T_LOGICAL_OR => LogicalType::Or->value,
            default => $token->value,
    };
  }
}