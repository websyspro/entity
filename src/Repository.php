<?php

namespace Websyspro\Entity;

use Websyspro\SqlFromClass\Enums\EntityRoot;
use Websyspro\SqlFromClass\Interfaces\HierarchyJoin;
use Websyspro\SqlFromClass\StructureTokens;
use Websyspro\SqlFromClass\Shareds;
use Websyspro\SqlFromClass\Token;
use Websyspro\Commons\Collection;

class Repository
{
  public StructureTokens $structureTokens;
  public Collection $fromSubQuery;
  public Collection $whereSubQuery;

  public function __construct(
    public string $entity
  ){}

  public function where(
    callable $fn
  ): Repository {
    $this->structureTokens = Shareds::createStructure( $fn );
    $this->structureTokens->getStructure();
    return $this;
  }

  public function getSql(
  ): array {
    $this->getFromSubQuerySql();
    $this->getWhereSubQuerySql();
    $this->setClearArgs();
    return [];
  }

  private function getLeftJoinStr(
    array $args
  ): string {
    [ $table, $key, $joinTable, $joinKey ] = $args;
    return "Left Join {$joinTable} On {$table}.{$key} = {$joinTable}.{$joinKey}";
  }

  private function getFromSubQuerySql(
  ): string {
    $joinsInRoot = $this->structureTokens->joins->where(
      fn( HierarchyJoin $hierarchyJoin ) => (
        $hierarchyJoin->entityRoot === EntityRoot::Yes
      )
    );

    $this->fromSubQuery = $joinsInRoot->mapper(
      fn( HierarchyJoin $hierarchyJoin ) => (
        $hierarchyJoin->entityParent === null
          ? $hierarchyJoin->entity->table 
          : $this->getLeftJoinStr(
              [ 
                $hierarchyJoin->entityJoin->table,
                $hierarchyJoin->entityJoin->key,
                $hierarchyJoin->entityJoin->joinTable,
                $hierarchyJoin->entityJoin->joinKey
            ]
          )
      )
    );
    
    return "";
  }

  private function getWhereSubQuerySql(
  ): void {
    $wheresInRoot = $this->structureTokens->tokens->mapper(
      function( Token $token ) {
        return $token;
      } 
    );

    var_dump( $wheresInRoot->mapper( fn(Token $t) => $t->value )->joinWithSpace() );
  }

  private function setClearArgs(
  ): void {
    unset( $this->structureTokens );
  }
}