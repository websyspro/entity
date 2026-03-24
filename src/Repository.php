<?php

namespace Websyspro\Entity;

use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Shareds\HierarchyJoin;
use Websyspro\Entity\Shareds\OrderByDesc;
use Websyspro\Entity\Shareds\OrderByAsc;
use Websyspro\Entity\Shareds\Structure;
use Websyspro\Entity\Shareds\Parameter;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Entity\Enums\TokenType;
use Websyspro\Entity\Shareds\Token;
use Websyspro\Entity\Shareds\Param;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use ReflectionFunction;
use Websyspro\Entity\Core\Database;
use Websyspro\Entity\Enums\DriverType;
use Websyspro\Entity\Shareds\PrimaryKey;

class Repository
{
  public mixed $fn;
  public string $sql;
  public int $page;
  public int $rowsPerPage;
  public EntityStructure $entityStructure;
  public Structure $structure;
  public OrderByAsc $orderByAsc;
  public OrderByDesc $orderByDesc;
  public Collection $joinsPrimary;
  public Collection $joinsSecondary;
  public Collection $columnsPrimary;
  public Collection $columnsSecondary;
  public Collection $wheresPrimary;
  public Collection $wheresSecondary;
  public Collection $wheresPrimaryCompare;
  public Collection $wheresSecondaryCompare;
  public Collection $prepareds;
  public Collection $orderBy;

  public function __construct(
    string $entity
  ){
    $this->entityStructure = Util::callUserClassFN( 
      $entity, "getAttributes", []
    );

    $this->queryBuilderInitial();
  }

  public function queryBuilder(
  ): Repository {
    $this->queryBuilderApplyWhere();
    $this->queryBuilderConstructorSQL();
    return $this;
  }

  private function queryBuilderInitial(
  ): void {
    $this->orderBy = new Collection();
    $this->prepareds = new Collection();
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
  ): string {
    return $this->getJoinsByEntityRoot([ EntityRoot::Yes ])->joinWithSpace();
  }

  private function queryBuilderJoinsSecondary(
  ): string {
    return $this->getJoinsByEntityRoot([ EntityRoot::Yes, EntityRoot::No ])->joinWithSpace();
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
  ): string {
    return $this->getColumnsByEntityRoot( EntityRoot::Yes )->joinWithComma();
  }

  private function queryBuilderColumnsSecondary(
  ): string {
    return $this->getColumnsByEntityRoot( EntityRoot::No )->joinWithComma();
  }  

  private function getWheresByEntityRoot(
    array $entityRootLit
  ): Collection {
    return $this->structure->tokens->where(
      fn( Token $token ) => Util::inArray( $token->root, $entityRootLit )
    );
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

  private function queryBuilderWheresPrimary(
  ): string {
    return $this->queryBuilderWheresCompare(
      $this->getWheresByEntityRoot([ EntityRoot::Yes ])
    )->joinWithSpace();
  }

  private function queryBuilderWheresSecondary(
  ): string {
    return $this->queryBuilderWheresCompare(
      $this->getWheresByEntityRoot([ EntityRoot::Yes, EntityRoot::No ])
    )->joinWithSpace();    
  }

  private function queryBuilderOrderBy(
  ): string|null {
    if( $this->orderBy->exist() === false ){
      if( $this->entityStructure->primaryKey->exist()){
        $orderByFromPrimaryKeys = $this->entityStructure->primaryKey->mapper(
          fn( PrimaryKey $primaryKey ) => Util::sprintFormat( "%s.%s Asc", [  
            $this->entityStructure->entity->table, $primaryKey->name
          ])
        );

        return Util::sprintFormat(
          "Order By %s", [ $orderByFromPrimaryKeys->joinWithComma() ]
        );
      } else {
        $orderByFromColumns = $this->entityStructure->columns->slice(0, 1)->mapper(
          fn( string $primaryKey ) => Util::sprintFormat( "%s.%s Asc", [  
            $this->entityStructure->entity->table, $primaryKey 
          ])
        );

        return Util::sprintFormat(
          "Order By %s.%s Asc", [ $orderByFromColumns->joinWithComma() ]
        );        
      }
    }

    return Util::sprintFormat(
      "Order By %s", [ $this->orderBy->joinWithComma() ]
    );
  }

  private function queryBuilderPaged(
   ): string|null {
    $this->page = isset( $this->page ) === false ? 1 : $this->page;
    $this->rowsPerPage = isset( $this->rowsPerPage ) === false ? 12 : $this->rowsPerPage;

    return match( DriverType::tryFrom( Database::getDriver())){
      DriverType::MySql => Util::sprintFormat( 
        "Limit %s, %s", [
          ( $this->page - 1 ) * $this->rowsPerPage, $this->rowsPerPage
        ]
      ),

      DriverType::SqlServer => Util::sprintFormat(
        "Offset %s Rows Fetch Next %s Rows Only", [
          ( $this->page - 1 ) * $this->rowsPerPage, $this->rowsPerPage
        ]
      ),

      DriverType::PostgreSQL => Util::sprintFormat( 
        "Limit %s Offset %s", [
          $this->rowsPerPage, ( $this->page - 1 ) * $this->rowsPerPage
        ]
      ),
        
      default => null
    };
   }
  
  private function getSQLFormat(
  ): string {
    return "Select %s From ( Select %s From %s Where %s %s %s ) As %s Where %s";
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
          $this->queryBuilderColumnsSecondary(),
          $this->queryBuilderColumnsPrimary(),
          $this->queryBuilderJoinsPrimary(),
          $this->queryBuilderWheresPrimary(),
          $this->queryBuilderOrderBy(),
          $this->queryBuilderPaged(),
          $this->queryBuilderJoinsSecondary(),
          $this->queryBuilderWheresSecondary(),
        ]
      )
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
    $orderBy = new OrderByAsc(
      new ReflectionFunction( $fn )
    );

    $this->orderBy->merge( $orderBy->tokens );
    return $this;
  }

  public function orderByDesc(
    callable $fn
  ): Repository {
    $orderBy = new OrderByDesc(
      new ReflectionFunction( $fn )
    );

    $this->orderBy->merge( $orderBy->tokens );
    return $this;
  }

  public function paged(
    int $page, 
    int $rowsPerPage = 12   
  ): Repository {
    $this->page = $page;
    $this->rowsPerPage = $rowsPerPage;
    return $this;
  }
}