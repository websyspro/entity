<?php

namespace Websyspro\Entity;

use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Shareds\HierarchyJoin;
use Websyspro\Entity\Shareds\Structure;
use Websyspro\Entity\Shareds\Parameter;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Entity\Shareds\Token;
use Websyspro\Entity\Shareds\Param;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionFunction;
use Websyspro\Entity\Enums\TokenType;

class Repository
{
  public mixed $fn;
  public string $sql;
  public EntityStructure $entityStructure;
  public Structure $structure;
  public Collection $joinsPrimary;
  public Collection $joinsSecondary;
  public Collection $columnsPrimary;
  public Collection $columnsSecondary;
  public Collection $wheresPrimary;
  public Collection $wheresSecondary;
  public Collection $wheresPrimaryCompare;
  public Collection $wheresSecondaryCompare;
  public Collection $prepareds;
  public Collection $orderByAsc;
  public Collection $orderByDesc;

  public function __construct(
    string $entity
  ){
    $this->entityStructure = Util::callUserClassFN( 
      $entity, "getAttributes", []
    );
  }

  public function where(
    callable $fn
  ): Repository {
    $this->fn = $fn;
    return $this;
  }

  public function orderByAsc(
    callable $fn
  ): Repository {
    print_r( $fn );
    return $this;
  }

  public function orderByDesc(
    callable $fn
  ): Repository {
    return $this;
  }  

  public function queryBuilder(
  ): Repository {
    $this->queryBuilderInitial();
    $this->queryBuilderApplyWhere();
    $this->queryBuilderJoinsPrimary();
    $this->queryBuilderJoinsSecondary();
    $this->queryBuilderColumnsPrimary();
    $this->queryBuilderColumnsSecondary();
    $this->queryBuilderWheresPrimary();
    $this->queryBuilderWheresSecondary();
    $this->queryBuilderWheresPrimaryCompare();
    $this->queryBuilderWheresSecondaryCompare();
    $this->queryBuilderConstructorSQL();
    return $this;
  }

  private function queryBuilderInitial(
  ): void {
    $this->prepareds = new Collection();
    $this->orderByAsc = new Collection();
    $this->orderByDesc = new Collection();
    $this->wheresPrimary = new Collection();
    $this->wheresSecondary = new Collection();
  }

  private function queryBuilderApplyWhere(
  ): Repository {
    $this->structure = new Structure(
      new ReflectionFunction( $this->fn )
    );

    return $this;
  }   

  private function getJoinsByEntityRoot(
    array $entityRoot
  ): Collection {
    $joins = $this->structure->joins->where(
      fn( HierarchyJoin $hierarchyJoin ) => (
        Util::inArray( $hierarchyJoin->entityRoot, $entityRoot )
      ) 
    );

    return $joins->mapper(
      function( HierarchyJoin $hierarchyJoin ) {
        if( $hierarchyJoin->entityForeignKey === null ){
          return $hierarchyJoin->entity->table;
        } else
        if( $hierarchyJoin->entityForeignKey !== null ){
          if( $hierarchyJoin->entityRoot === EntityRoot::Yes ){
            return Util::sprintFormat( 'Inner Join %1$s On %1$s.%2$s = %3$s.%4$s', [
              $hierarchyJoin->entityForeignKey->entityReference->table,
              $hierarchyJoin->entityForeignKey->entityReference->key,
              $hierarchyJoin->entityForeignKey->entity->table,
              $hierarchyJoin->entityForeignKey->key
            ]);
          } else if( $hierarchyJoin->entityRoot === EntityRoot::No ){
            return Util::sprintFormat( 
              $hierarchyJoin->entity->table !== 
              $hierarchyJoin->entityForeignKey->entityReference->table
                ? 'Inner Join %3$s On %3$s.%4$s = %1$s.%2$s' 
                : 'Inner Join %1$s On %1$s.%2$s = %3$s.%4$s', [
                  $hierarchyJoin->entityForeignKey->entityReference->table,
                  $hierarchyJoin->entityForeignKey->entityReference->key,
                  $hierarchyJoin->entityForeignKey->entity->table,
                  $hierarchyJoin->entityForeignKey->key
              ]
            );
          }
        }
      }
    );    
  }

  private function queryBuilderJoinsPrimary(
  ): void {
    $this->joinsPrimary = $this->getJoinsByEntityRoot([ EntityRoot::Yes ]);
  }

  private function queryBuilderJoinsSecondary(
  ): void {
    $this->joinsSecondary = $this->getJoinsByEntityRoot([ EntityRoot::Yes, EntityRoot::No ]);
  }

  private function getColumnsByEntityRoot(
    EntityRoot $entityRoot
  ): Collection {
    $columns = $this->structure->joins->where(
      function( HierarchyJoin $hierarchyJoin ) use ( $entityRoot ){
        if( $entityRoot === EntityRoot::Yes ){
          return $hierarchyJoin->entity->class === $this->entityStructure->entity->class;
        } else return true;
      }
    );


    $columns = $columns->mapper(
      function( HierarchyJoin $hierarchyJoin ) use ( $entityRoot ){
        $parameter = $this->structure->parameters->getOneOrFail( $hierarchyJoin->entity->class );
        if( $parameter instanceof Parameter ){
          return $parameter->entityStructure->columns->mapper(
            fn( String $column ) => $entityRoot === EntityRoot::Yes
              ? Util::sprintFormat( '%1$s.%2$s As %2$s', [ $parameter->entityStructure->entity->table, $column ])
              : Util::sprintFormat( '%1$s.%2$s As %1$s_%2$s', [ $parameter->entityStructure->entity->table, $column ])
          );
        }
      }
    );

    return $columns->reduce(
      [], fn( array|null $acc, Collection $column ) => (
        array_merge( $acc, $column->toArray() )
      )
    );
  }  

  private function queryBuilderColumnsPrimary(
  ): void {
    $this->columnsPrimary = $this->getColumnsByEntityRoot( EntityRoot::Yes );
  }

  private function queryBuilderColumnsSecondary(
  ): void {
    $this->columnsSecondary = $this->getColumnsByEntityRoot( EntityRoot::No );
  }  

  private function getWheresByEntityRoot(
    array $entityRootLit
  ): Collection {
    return $this->structure->tokens->where(
      fn( Token $token, int $i ) => Util::inArray( $token->root, $entityRootLit )
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

  private function queryBuilderWheresCompare(
    Collection $wheres
  ): Collection {
    $wheres = $wheres->where( 
      function( Token $token, int $i ) use ($wheres){
        if( $token->type === TokenType::Logical ){
          $prevToken = $wheres->getOneOrFail( $i - 1 );
          if( $prevToken instanceof Token ){
            if( $prevToken->type === TokenType::StartGroup ){
              return false;
            }
          }
        }

        return true;
      }
    );
    
    return $wheres->mapper( fn( Token $token ) => $token->value );     
  }

  private function queryBuilderWheresPrimaryCompare(
  ): void {
    $this->wheresPrimaryCompare = $this->queryBuilderWheresCompare( $this->wheresPrimary );
  }

  private function queryBuilderWheresSecondaryCompare(
  ): void {
    $this->wheresSecondaryCompare = $this->queryBuilderWheresCompare( $this->wheresSecondary );    
  }
  
  private function getSQLFormat(
  ): string {
    return "Select %s From ( Select %s From %s Where %s Limit 0, 6 ) As %s Where %s";
  }

  private function queryBuilderConstructorSQL(
  ): void {
    $this->sql = preg_replace_callback( 
      "#\:param_\d+#", function ( $matches ){
        [ $paramKey ] = $matches;
        $param = $this->structure->params->getOneOrFail( $paramKey );
        if( $param instanceof Param ){
          $this->prepareds->add( $param->value );  
        }

        return "?";
      }, Util::sprintFormat( $this->getSQLFormat(), [
          $this->columnsSecondary->joinWithComma(),
          $this->columnsPrimary->joinWithComma(),
          $this->joinsPrimary->joinWithSpace(),
          $this->wheresPrimaryCompare->joinWithSpace(),
          $this->joinsSecondary->joinWithSpace(),
          $this->wheresSecondaryCompare->joinWithSpace(),
        ]
      )
    );
  }
}