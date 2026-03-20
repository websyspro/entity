<?php

namespace Websyspro\Entity;

use Websyspro\Entity\Shareds\StructureFromFn;
use Websyspro\Entity\Shareds\HierarchyJoin;
use Websyspro\Entity\Shareds\ForeignKey;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionFunction;
use Websyspro\Entity\Enums\TokenType;
use Websyspro\Entity\Shareds\Token;

class Repository
{
  public StructureFromFn $structureFromFn;
  public Collection $joinsPrimary;
  public Collection $joinsSecondary;
  public Collection $wheresPrimary;
  public Collection $wheresSecondary;

  public function __construct(
    public string $entity
  ){}

  public function where(
    callable $fn
  ): Repository {
    $this->structureFromFn = new StructureFromFn(
      new ReflectionFunction( $fn )
    );

    return $this;
  }

  public function queryBuilder(
  ): Repository {
    $this->queryBuilderJoinsPrimary();
    $this->queryBuilderJoinsSecondary();
    $this->queryBuilderWheresPrimary();
    $this->queryBuilderWheresSecondary();

    //print_r( $this->wheresPrimary );
    return $this;
  }

  private function getJoinsByEntityRoot(
    EntityRoot $entityRoot
  ): Collection {
    $joins = $this->structureFromFn->joins->where(
      fn( HierarchyJoin $hierarchyJoin ) => (
        $hierarchyJoin->entityRoot === $entityRoot && 
        $hierarchyJoin->entityForeignKey instanceof ForeignKey
      ) 
    );

    return $joins->mapper(
      function( HierarchyJoin $hierarchyJoin ) {
        if( $hierarchyJoin->entityRoot === EntityRoot::Yes ){
          return Util::sprintFormat( 'Inner Join %1$s On %1$s.%2$s = %3$s.%4$s', [
            $hierarchyJoin->entityForeignKey->entityReference->table,
            $hierarchyJoin->entityForeignKey->entityReference->key,
            $hierarchyJoin->entityForeignKey->entity->table,
            $hierarchyJoin->entityForeignKey->key
          ]);
        } else if( $hierarchyJoin->entityRoot === EntityRoot::No ){
          return Util::sprintFormat( 'Inner Join %3$s On %3$s.%4$s = %1$s.%2$s', [
            $hierarchyJoin->entityForeignKey->entityReference->table,
            $hierarchyJoin->entityForeignKey->entityReference->key,
            $hierarchyJoin->entityForeignKey->entity->table,
            $hierarchyJoin->entityForeignKey->key,
          ]);
        }
      }
    );    
  }

  private function queryBuilderJoinsPrimary(
  ): void {
    $this->joinsPrimary = $this->getJoinsByEntityRoot( EntityRoot::Yes );
  }

  private function queryBuilderJoinsSecondary(
  ): void {
    $this->joinsSecondary = $this->getJoinsByEntityRoot( EntityRoot::No );
  }

  private function getWheresByEntityRoot(
    array $entityRootLit
  ): Collection {
    return $this->structureFromFn->tokens->where(
      fn( Token $token ) => Util::inArray( $token->entityRoot, $entityRootLit )
    );
  }  

  private function queryBuilderWheresPrimary(
  ): void {
    $this->wheresPrimary = $this->getWheresByEntityRoot([ EntityRoot::Yes ]);
  }

  private function queryBuilderWheresSecondary(
  ): void {
    $this->wheresSecondary = $this->getWheresByEntityRoot([ EntityRoot::Yes, EntityRoot::No ]);
  }
}