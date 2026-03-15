<?php

namespace Websyspro\Entity;

use ReflectionFunction;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\EntityRoot;
use Websyspro\Entity\Enums\LogicalType;
use Websyspro\Entity\Enums\TokenType;
use Websyspro\Entity\Shareds\ForeignKey;
use Websyspro\Entity\Shareds\HierarchyJoin;
use Websyspro\Entity\Shareds\StructureFromFn;
use Websyspro\Entity\Shareds\Token;

class Repository
{
  public StructureFromFn $structureFromFn;
  public Collection $joinsPrimary;
  public Collection $joinsSecondary;
  public Collection $wheresPrimary;

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
    // $this->queryBuilderJoinsPrimary();
    // $this->queryBuilderJoinsSecondary();
    // $this->queryBuilderWheresPrimary();
    // $this->queryBuilderWheresSecondary();
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
        if( end( $hierarchyJoin->entityHistory ) === AttributeType::oneToOne ){
          return Util::sprintFormat( "Inner Join %s On %s.%s = %s.%s", [
            $hierarchyJoin->entityForeignKey->entityReference->table,
            $hierarchyJoin->entityForeignKey->entityReference->table,
            $hierarchyJoin->entityForeignKey->entityReference->key,
            $hierarchyJoin->entityForeignKey->entity->table,
            $hierarchyJoin->entityForeignKey->key
          ]);
        } else if( end( $hierarchyJoin->entityHistory ) === AttributeType::oneToMany ){
          return Util::sprintFormat( "Inner Join %s On %s.%s = %s.%s", [
            $hierarchyJoin->entityForeignKey->entity->table,
            $hierarchyJoin->entityForeignKey->entity->table,
            $hierarchyJoin->entityForeignKey->key,
            $hierarchyJoin->entityForeignKey->entityReference->table,
            $hierarchyJoin->entityForeignKey->entityReference->key,
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
    EntityRoot $entityRoot,
    Collection $tokens = new Collection()
  ): Collection {
    // $joins = $this->structureFromFn->joins->where(
    //   fn( HierarchyJoin $hierarchyJoin ) => (
    //     $hierarchyJoin->entityRoot === $entityRoot
    //   ) 
    // ); 

    // $joins = $joins->mapper( 
    //   fn( HierarchyJoin $hierarchyJoin ) => (
    //     $hierarchyJoin->entity->table
    //   )
    // );

    // for( $i = 0; $i < $this->structureFromFn->tokens->count(); $i++ ){
    //   [ $field1, $equalOrRange, $field2 ] = [
    //     $this->structureFromFn->getToken( $i + 0 ),
    //     $this->structureFromFn->getToken( $i + 1 ),
    //     $this->structureFromFn->getToken( $i + 2 )
    //   ];

    //   $hasField1Entity = $field1 instanceof Token && $field1->takenType === TokenType::FieldEntity;
    //   $hasEqualOrRange = $equalOrRange instanceof Token && $equalOrRange->takenType === TokenType::FieldRange && $equalOrRange->tokenValue === LogicalType::Between->value;
    //   $hasfield2EntityOrValue = $field2 instanceof Token && (
    //     $field2->takenType === TokenType::FieldEntity ||
    //     $field2->takenType === TokenType::FieldValue
    //   );

    //   if( $hasEqualOrRange ){
    //     print_r( $equalOrRange );
    //   }
    // }

    return $tokens;
  }  

  private function queryBuilderWheresPrimary(
  ): void {
    $this->wheresPrimary = $this->getWheresByEntityRoot( EntityRoot::Yes );
  }

  private function queryBuilderWheresSecondary(): void {}
}