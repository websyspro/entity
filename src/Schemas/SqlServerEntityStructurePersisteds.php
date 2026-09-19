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
    }
  }
}