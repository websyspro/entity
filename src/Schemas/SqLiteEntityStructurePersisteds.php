<?php

namespace Websyspro\Entity\Schemas;

use stdClass;
use Websyspro\Connection\Database;
use Websyspro\Entity\Types\ColumnDate;
use Websyspro\Entity\Types\ColumnDatetime;
use Websyspro\Entity\Types\ColumnDecimal;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnInt;
use Websyspro\Entity\Types\ColumnText;
use Websyspro\Entity\Types\ColumnTime;

class SqLiteEntityStructurePersisteds
extends AbstractEntityStructurePersisteds
{
  public function getColumnsFromEntityPersisteds(
  ): array {
    return Database::query(
      "PRAGMA table_info({$this->entityNames->alias})"
    );
  }

  public function getIndexesFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select name As index_name
         From pragma_index_list(?)
        Where \"unique\" = 0
          And origin = 'c'", [
        $this->entityNames->alias
      ]
    );
  }
  
  public function getUniquesFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select name As unique_name
         From pragma_index_list(?)
        Where \"unique\" = 1
          And origin = 'c'", [
        $this->entityNames->alias
      ]
    );
  }
  
  public function getForeignKeysFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select 
     Distinct m.name As constraint_name
         From sqlite_master m
             ,pragma_foreign_key_list(m.name) fk
        Where m.type = 'table'
          And m.name = ?", [
        $this->entityNames->alias
      ]
    );
  }  

  public function getEntityColumns(
  ): void {
    foreach( $this->getColumnsFromEntityPersisteds() as $column ){
      $this->columns->items[] = $column->name;

      /* Define Column Type */
      $this->types->items[ $column->name ] = match(
        $this->extractColumnType( $column->type )
      ){
        "varchar" => ColumnText::class,
        "tinyint" => ColumnFlag::class,
        "integer" => ColumnInt::class,
        "decimal" => ColumnDecimal::class,
        "datetime" => ColumnDatetime::class,
        "date" => ColumnDate::class,
        "time" => ColumnTime::class,
          default => $this->extractColumnType( $column->type )
      };

      /* Define Length */
      if( $this->extractColumnLength( $column->type ) !== 0 ){
        $this->lengths->items[ $column->name ] = $this->extractColumnLength( $column->type );
      }

      /* Define Column Requireds */
      if( (int)$column->notnull === 1 ){
        $this->requireds->items[ $column->name ] = $column->name;
      }

      /* Define Column Precisions */
      if( $this->extractColumnPrecisions( $column->type ) instanceof PrecisionDetails ){
        $this->precisions->items[ $column->name ] = $this->extractColumnPrecisions( $column->type );
      }

      /* Define Column PrimaryKey */
      if( (int)$column->pk === 1 ){
        $this->primaryKeys->items[ $column->name ] = $column->name;
      }
      
      /* Define Indexes */
      $this->indexes->items = array_map(
        fn( stdClass $object ) => $object->index_name,
          $this->getIndexesFromEntityPersisteds()
      );
      
      /* Define Uniques */
      $this->uniques->items = array_map(
        fn( stdClass $object ) => $object->unique_name, 
          $this->getUniquesFromEntityPersisteds()
      );

      /* Define ForeignKeys */
      $this->foreignKeys->items = array_map(
        fn( stdClass $object ) => $object->constraint_name,
          $this->getForeignKeysFromEntityPersisteds()
      );       
    }
  }
}