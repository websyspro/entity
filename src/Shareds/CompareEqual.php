<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Entity\Enums\CompareType;
use Websyspro\Commons\Collection;

class CompareEqual
{
  public string $value;
  
  public function __construct(
    public CompareField|CompareValue $compare,
    public Collection $tokens,
    public int $compareType,
  ){
    $this->startups();
    $this->startupsReverse();
    $this->startupsClear();
  }

  private function startups(
  ): void {
    [ $tokenCompare ] = $this->tokens->toArray();
    $this->value = $tokenCompare->value;
  }

  private function startupsReverse(
  ): void {
    if( $this->compareType === ExpressionCompare::T_VALUE_X_FIELD ){
      $this->value = match( CompareType::tryFrom( $this->value ) ){
        CompareType::GreaterEqual => CompareType::LessEqual->value, 
        CompareType::LessEqual => CompareType::GreaterEqual->value, 
        CompareType::Greater => CompareType::Less->value, 
        CompareType::Less => CompareType::Greater->value, 
          default => $this->value
      };
    }
  }  
  
  private function startupsClear(
  ): void {
    unset( $this->compare, $this->tokens, $this->compareType );
  }  
}