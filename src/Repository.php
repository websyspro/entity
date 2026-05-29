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

  public float $start;

  public function __construct(
    public string $entity
  ){}

  private function tableAlias(
  ): string {
    return $this->expressionWhere->context['entity']['alias'];
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
    $this->start = microtime( true );

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

    // $timerAst = ( microtime( true ) - $this->start ) * 1000;    
    // echo "Time required to execute the AST: {$timerAst}(ms)\n";

    // $this->start = microtime( true );

    $rows = Database::query(
      $this->sqlAll(), $this->params
    );

    // $timerQuery = ( microtime( true ) - $this->start ) * 1000;

    // echo "Time to execute the SQL query: {$timerQuery}(ms)\n"; 
    // echo "Total processing time: " . $timerAst + $timerQuery . "(ms)\n"; 

    return $rows;
  }
}