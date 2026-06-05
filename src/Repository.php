<?php

namespace Websyspro\Entity;

use Closure;
use Websyspro\Entity\Core\DB;
use Websyspro\Entity\Shareds\ExpressionWhere;

use function sprintf;

class Repository
{
  public ExpressionWhere $expressionWhere;

  public Closure $closureIncludes;
  public Closure $closureWhere;
  public string $table;
  public string $where;
  public array $whereParams = [];
  private int $page;
  private int $rowsPerPage;
  private int $defaultRowsPerPage = 12;

  public function __construct(
    public string $entity
  ){}

  public function where(
    Closure $closure
  ): Repository {
    if( isset( $this->expressionWhere ) === false ){
      $this->expressionWhere = new ExpressionWhere( $closure );
      [ $this->table, $this->where, $this->whereParams ] = $this->expressionWhere->get();
    }

    return $this;
  }

  public function paged(
    int $page,
    int $rowsPerPage
  ): Repository {
    $this->page = $page;
    $this->rowsPerPage = $rowsPerPage;
    return $this;
  }

  private function getTable(
  ): string {
    return $this->table;
  }  

  private function getWhere(
  ): string {
    return $this->where;
  }

  private function getOrderBy(
  ): string {
    return "Order by 1 asc";
  }

  private function getPage(
  ): int {
    return isset( $this->page ) === false 
      ? ( 1 - 1 ) * $this->getRowsPerPage() 
      : ( $this->page - 1 ) * $this->getRowsPerPage();
  }

  private function getRowsPerPage(
  ): int {
    return isset( $this->rowsPerPage ) === false 
      ? $this->defaultRowsPerPage 
      : $this->rowsPerPage;
  }

  private function getPaged(
  ): string|null {
    return sprintf(
      match( DB::driver() ){
        'mysql' => 'Limit %1$s, %2$s',
        'sqlsrv' => 'Offset %1$s Rows Fetch Next %2$s Rows Only',
        'pgsql' => 'Limit %2$s Offset %1$s'
      }, $this->getPage(), $this->getRowsPerPage()
    );
  } 

  private function getSqlAll(
  ): string {
    return sprintf( 'Select * from %1$s Where %2$s %3$s %4$s',
      $this->getTable(), $this->getWhere(), $this->getOrderBy(), $this->getPaged()  
    );
  }

  private function getParams(
  ): array {
    return $this->whereParams;
  }

  public function all(
  ): array {
    return DB::query(
      $this->getSqlAll(), 
      $this->getParams()
    );
  }
}