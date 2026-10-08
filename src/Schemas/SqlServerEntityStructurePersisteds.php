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
use Websyspro\Entity\Types\ColumnUUID;

class SqlServerEntityStructurePersisteds
extends AbstractEntityStructurePersisteds
{
  public function getColumnsFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select information_schema.columns.ordinal_position As cid
	  	       ,information_schema.columns.column_name As name
        ,Case information_schema.columns.data_type 
         When 'nvarchar' then concat( 'NVarchar(', information_schema.columns.character_maximum_length,')')
         When 'nvarchar' then concat( 'NVarchar(', information_schema.columns.character_maximum_length,')')
         When 'decimal' then concat( 'Decimal(', information_schema.columns.numeric_precision, ',', information_schema.columns.numeric_scale, ')')
         Else information_schema.columns.data_type
          End As type
        ,Case information_schema.columns.is_nullable When 'YES' Then 0 Else 1 End As notnull
 	      ,Null as diff_value
        ,Case When (
       Select information_schema.key_column_usage.column_name  
 	       From information_schema.key_column_usage 
 	      Where information_schema.key_column_usage.table_name = information_schema.columns.table_name
 	        And information_schema.key_column_usage.column_name = information_schema.columns.column_name ) Is Null then 0 else 1 
 	     End As pk
         From information_schema.columns 
        Where information_schema.columns.table_name = ?", [
          $this->entityNames->alias
        ]
    );
  }

  public function getIndexesFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select i.name AS index_name
         From sys.indexes i
   Inner Join sys.tables t On t.object_id = i.object_id
   Inner Join sys.schemas s On s.schema_id = t.schema_id
        Where s.name = SCHEMA_NAME()
          And t.name = ?
          And i.is_unique = 0
          And i.is_primary_key = 0
          And i.is_unique_constraint = 0
          And i.type > 0", [
        $this->entityNames->alias
      ]
    );
  }
  
  public function getUniquesFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select i.name AS unique_name
         From sys.indexes i
   Inner Join sys.tables t On t.object_id = i.object_id
   Inner Join sys.schemas s On s.schema_id = t.schema_id
        Where s.name = SCHEMA_NAME()
          And t.name = ?
          And i.is_unique = 1
          And i.is_primary_key = 0
          And i.is_unique_constraint = 1
          And i.type <> 0", [
        $this->entityNames->alias
      ]
    );
  }
  
  public function getForeignKeysFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select LOWER(fk.name) AS constraint_name
         From sys.foreign_keys fk
   Inner Join sys.tables t On t.object_id = fk.parent_object_id
   Inner Join sys.schemas s On s.schema_id = t.schema_id
        Where s.name = SCHEMA_NAME()
          And t.name = ?", [
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
        "uniqueidentifier" => ColumnUUID::class,
        "nvarchar" => ColumnText::class,
        "varchar" => ColumnText::class,
        "tinyint" => ColumnFlag::class,
        "bigint" => ColumnInt::class,
        "integer" => ColumnInt::class,
        "decimal" => ColumnDecimal::class,
        "datetime" => ColumnDatetime::class,
        "datetime2" => ColumnDatetime::class,
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