<?php

namespace Websyspro\Entity\Shareds;

class Scope
{
  public string $name;
  public string $alias;

  public function __construct(
    array $parameterArr
  ){
    $this->startups(
      $parameterArr
    );
  }

  private function startups(
    array $parameterArr
  ): void {
    [ $tokenName, $tokenAlias ] = $parameterArr;

    if( $tokenName instanceof Token ){
      $this->name = $tokenName->value;
    }

    if( $tokenAlias instanceof Token ){
      $this->alias = $tokenAlias->value;
    }
  }

  public function isEquals(
    Scope $scope
  ): bool {
    return $this->name === $scope->name 
        && $this->alias === $scope->alias;
  }
}