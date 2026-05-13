<?php

namespace Websyspro\Entity\Shareds;

use Closure;

class Scope
{
  public string $alias;
  public string $variable;
  public Entity $entity;

  public function __construct(
    array $parameterArr,
    Closure $closure
  ){
    $this->startups(
      $parameterArr, $closure
    );
  }

  private function startups(
    array $parameterArr,
    Closure $closure
  ): void {
    [ $tokenName, $tokenAlias ] = $parameterArr;

    if( $tokenName instanceof Token ){
      $this->alias = $tokenName->value;

      $usesItem = ClosureUtil::getUses( $closure )->getUse( $this->alias );
      if( $usesItem instanceof UsesItem ){
        $entityStructure = ClosureUtil::getEntityStructure( $usesItem->path );
        if( $entityStructure instanceof EntityStructure ){
          $this->entity = $entityStructure->entity;
        }
      }
    }

    if( $tokenAlias instanceof Token ){
      $this->variable = $tokenAlias->value;
    }
  }

  public function isEquals(
    Scope $scope
  ): bool {
    return $this->alias === $scope->alias 
        && $this->variable === $scope->variable;
  }
}