<?php

namespace Websyspro\Entity\Schemas;

use Websyspro\Connection\Database;
use Websyspro\Entity\Types\ColumnDate;
use Websyspro\Entity\Types\ColumnDatetime;
use Websyspro\Entity\Types\ColumnDecimal;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnInt;
use Websyspro\Entity\Types\ColumnText;
use Websyspro\Entity\Types\ColumnTime;
use Websyspro\Entity\Types\ColumnUUID;

class PostgresEntityStructurePersisteds
extends AbstractEntityStructurePersisteds
{
  public function getColumnsFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select information_schema.columns.ordinal_position As cid
			       ,information_schema.columns.column_name As name
		    ,Case information_schema.columns.udt_name 
	       When 'varchar' then concat( 'varchar(', information_schema.columns.character_maximum_length,')')
         When 'decimal' then concat( 'decimal(', information_schema.columns.numeric_precision, ',', information_schema.columns.numeric_scale, ')')
         When 'numeric' then concat( 'decimal(', information_schema.columns.numeric_precision, ',', information_schema.columns.numeric_scale, ')')
         When 'bool' then 'boolean'
         When 'int8' then 'bigint'
         When 'int4' then 'int'
         When 'timestamp' then 'datetime'
         Else information_schema.columns.udt_name
          End As type
        ,Case information_schema.columns.is_nullable When 'YES' Then 0 Else 1 End As notnull
        ,Null as diff_value
         ,Case 
          When (
        Select information_schema.key_column_usage.column_name
	        From information_schema.key_column_usage 
	    	      ,information_schema.table_constraints
	       Where information_schema.key_column_usage.table_name = information_schema.columns.table_name
	         And information_schema.key_column_usage.column_name = information_schema.columns.column_name
	         And information_schema.key_column_usage.table_name = information_schema.table_constraints.table_name
	         And information_schema.key_column_usage.constraint_name = information_schema.table_constraints.constraint_name ) Is Null then 0 else 1 
	         End As pk
		      From information_schema.columns 
		     Where information_schema.columns.table_name = ?
	    Order by information_schema.columns.ordinal_position Asc", [
        strtolower( $this->entityNames->alias )
      ]
    );
  }

  public function getIndexesFromEntityPersisteds(
  ): array {
    return Database::query(
      "", [
        $this->entityNames->alias
      ]
    );
  }
  
  public function getUniquesFromEntityPersisteds(
  ): array {
    return Database::query(
      "", [
        $this->entityNames->alias
      ]
    );
  }
  
  public function getForeignKeysFromEntityPersisteds(
  ): array {
    return Database::query(
      "", [
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
        "uuid" => ColumnUUID::class,
        "bigint" => ColumnInt::class,
        "boolean" => ColumnInt::class,
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
    }
  }
}