<?php

namespace Websyspro\Entity;

use Closure;
use Websyspro\Entity\Core\Database;
use Websyspro\Entity\Enums\MetaType;
use Websyspro\Entity\Shareds\ClosureUtil;
use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Shareds\ExpressionWhere;

class Repository
{
  public EntityStructure $entityStructure;
  public ExpressionWhere $expressionWhere;

  public Closure $closureIncludes;
  public Closure $closureWhere;

  public array $params = [];

  public function __construct(
    public string $entity
  ){}

  private function tableAlias(
  ): string {
    return $this->entityStructure->entity[ 'alias' ];
  }

  private function addParam(
    array $matches,
    array $params
  ): string {
    [ $key ] = $matches;

    $this->params[] = $params[$key];
    return "?";
  }  

  public function where(
    Closure $closure
  ): Repository {
    if( isset( $this->expressionWhere ) === false ){
      $this->expressionWhere = new ExpressionWhere(
        $this->entity, $this->closureWhere = $closure
      );
    }

    return $this;
  }

  private function preparedWhere(
  ): string|null {
    if( isset( $this->expressionWhere )){
      return preg_replace_callback( "#\:param_\d*_\d*#", fn( array $matches ) => (
        $this->addParam( $matches, ClosureUtil::getParams( $this->closureWhere ))
      ), $this->expressionWhere->sqlBuild());
    }

    return null;
  }

  private function sqlAll(
  ): string {
    return (
      "Select *
         From {$this->tableAlias()} 
        Where {$this->preparedWhere()}
     Order by 1 asc   
       Offset 0 Rows Fetch Next 12 Rows Only"
    );
  }

  public function all(
  ): array {
    // return Database::query(
    //   $this->sqlAll(), $this->params
    // );

    return [];
  }
}