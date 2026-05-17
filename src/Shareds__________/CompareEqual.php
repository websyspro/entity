<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\CompareType;
use Websyspro\Commons\Collection;

class CompareEqual
{
  public string $value;
  
  public function __construct(
    CompareField|CompareValue $compare,
    Collection $tokens, 
    int $compareType
  ){
    $this->startups( $tokens );
    $this->startupsReverse( $compareType );
    $this->startupsReverseAdjustment( $compare );
  }

  private function startups(
    Collection $tokens
  ): void {
    [ $token ] = $tokens->toArray();
    $this->value = $token->value;
  }

  private function startupsReverse(
    int $compareType
  ): void {
    if( $compareType === ExpressionCompare::T_VALUE_X_FIELD ){
      $this->value = match( CompareType::tryFrom( $this->value ) ){
        CompareType::GreaterEqual => CompareType::LessEqual->value, 
        CompareType::LessEqual => CompareType::GreaterEqual->value, 
        CompareType::Greater => CompareType::Less->value, 
        CompareType::Less => CompareType::Greater->value, 
          default => $this->value
      };
    }
  }

  private function startupsReverseAdjustment(
    CompareField|CompareValue $compare
  ): void {
    // preg_match('/(?<!\\\\)%/', $text)
  }
}