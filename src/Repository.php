<?php

namespace Websyspro\Entity;

use Websyspro\Entity\Core\Database;
use Websyspro\Entity\Enums\DriverType;
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
  private int $page;
  private int $rowsPerPage;

  public function select(
    callable $fn   
  ): AbstractRepository {
    return parent::select($fn);
  }

  public function paged(
    int $page,
    int $rowsPerPage
  ): AbstractRepository {
    $this->page = $page;
    $this->rowsPerPage = $rowsPerPage;
    return $this;
  }  

  public function include(
    callable $fn   
  ): AbstractRepository {
    return parent::include($fn);
  }

  public function where(
    callable $fn   
  ): AbstractRepository {
    return parent::where($fn);
  }  

  public function groupBy(
    callable $fn   
  ): AbstractRepository {
    return parent::groupBy($fn);
  }  

  private function createEntityBase(
  ): void {
    $this->entityStructure = $this
      ->entityStructure($this->entity);
  }

  private function createSql(
  ): string {
    if( $this->getStructure()->includeList->count() !== 0 ){
      return "Select * From ( Select * From %s %s ) as %s %s Order By 1 %s";
    }

    return "Select * From %s Order By 1 %s";
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
  
  private function createPaged(
  ): string|null {
    $this->page = isset( $this->page ) === false ? 1 : $this->page;
    $this->rowsPerPage = isset( $this->rowsPerPage ) === false ? 12 : $this->rowsPerPage;

    return match( DriverType::tryFrom( Database::getDriver())){
      DriverType::MySql => Util::sprintFormat( "Limit %s, %s", [( $this->page - 1 ) * $this->rowsPerPage, $this->rowsPerPage ]),
      DriverType::SqlServer => Util::sprintFormat( "Offset %s Rows Fetch Next %s Rows Only", [( $this->page - 1 ) * $this->rowsPerPage, $this->rowsPerPage ]),
      DriverType::PostgreSQL => Util::sprintFormat( "Limit %s Offset %s", [ $this->rowsPerPage, ( $this->page - 1 ) * $this->rowsPerPage ]),
        
      default => null
    };    
  }

  public function all(
  ): mixed {
    $this->createEntityBase();
    $this->createWheres();
    $this->createJois();

    $sql = Util::sprintFormat(
      $this->createSql(), [ 
      $this->entityStructure->entity->alias, $this->wheres,
      $this->entityStructure->entity->alias, 
      $this->joins->joinWithSpace(), $this->createPaged()
    ]);

    var_dump($sql);
    
    return Database::query( 
      $sql, $this->params->toArray()
    );
  }
}