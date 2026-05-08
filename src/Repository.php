<?php

namespace Websyspro\Entity;

use Websyspro\Entity\Core\Database;
use Websyspro\Entity\Enums\WhereType;
use Websyspro\Entity\Shareds\AbstractRepository;
use Websyspro\Entity\Shareds\EntityStructure;
use Websyspro\Entity\Shareds\IncludeItem;
use Websyspro\Entity\Shareds\IncludeList;
use Websyspro\Entity\Shareds\WhereList;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Util;

class Repository
extends AbstractRepository
{
  private EntityStructure $entityStructure;
  private IncludeList $joins;
  private Collection $params;
  private string $wheres;

  private function createEntityBase(
  ): void {
    $this->entityStructure = $this
      ->entityStructure( $this->entity );
  }

  private function createParameter(
    Collection $params,
    array $matches  
  ): string {
    [ $key ] = $matches;

    if( $params->count() !== 0 ){
      if( isset( $this->params ) === false ){
        $this->params = new Collection();
      }

      $this->params->add( 
        $params->toArray()[$key]
      );
    }
    
    return "?";
  }
  
  private function createWhereFromList(
    WhereList|array $whereList,
    WhereType $whereType
  ): string|null {
    if( $whereList instanceof WhereList ){
      if( $whereList->whereBody->tokens->count() !== 0 ){
        $wheres = preg_replace_callback( 
          "#\:param_\d+#", fn( array $matches ) => (
          $this->createParameter( $whereList->whereBody->params, $matches )
        ), $whereList->whereBody->tokensToString());

        if( empty($wheres ) === false ){
          if( $whereType === WhereType::Join ){
            return Util::sprintFormat( "And %s", [ $wheres ]);
          } else if( $whereType === WhereType::Where ){
            return Util::sprintFormat( "Where %s", [ $wheres ]);
          }
        }
      }
    }

    return null;
  }

  private function createJois(
  ): void {
    $this->joins = $this->getStructure()
      ->includeList->mapper(fn( IncludeItem $i ) => Util::sprintFormat( 
        "inner Join %s On %s.%s = %s.%s %s", [
          $i->relationship->targetItem->itemForeignKey->table,
          $i->relationship->targetItem->itemForeignKey->table,
          $i->relationship->targetItem->itemForeignKey->key,
          $i->relationship->targetItem->itemForeignKey->referenceTable,
          $i->relationship->targetItem->itemForeignKey->referenceKey, $this->createWhereFromList( 
            $i->whereList ?? [], WhereType::Join
          )
        ])
      );
  }

  private function createWheres(
  ): void {
    $this->wheres = $this->createWhereFromList( 
      $this->getStructure()->whereList ?? [], WhereType::Where
    );
  }  

  public function all(
  ): mixed {
    $this->createEntityBase();
    $this->createWheres();
    $this->createJois();

    return Database::query( 
      Util::sprintFormat( "Select * From ( Select * From %s %s ) as %s %s Order By 1 OffSet 1 Rows Fetch Next 64 Rows Only;", [ 
        $this->entityStructure->entity->alias, $this->wheres,
        $this->entityStructure->entity->alias, $this->joins->joinWithSpace()
      ]), $this->params->toArray()
    );
  }
}