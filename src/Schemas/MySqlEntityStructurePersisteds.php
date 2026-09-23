<?php

namespace Websyspro\Entity\Schemas;

use Websyspro\Connection\Database;
use Websyspro\Entity\Interfaces\PrecisionDetails;
use Websyspro\Entity\Types\ColumnBigInt;
use Websyspro\Entity\Types\ColumnDatetime;
use Websyspro\Entity\Types\ColumnDecimal;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnInt;
use Websyspro\Entity\Types\ColumnSmallInt;
use Websyspro\Entity\Types\ColumnText;
use Websyspro\Entity\Types\ColumnTime;

class MySqlEntityStructurePersisteds
extends AbstractEntityStructurePersisteds
{
  public function getColumnsFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select information_schema.columns.ordinal_position As cid
		         ,information_schema.columns.column_name As name
		         ,information_schema.columns.column_type As type
	      ,Case information_schema.columns.is_nullable When 'YES' Then 0 Else 1 End As notnull
	           ,Null as diff_value
        ,Case information_schema.columns.column_key When 'PRI' Then 1 Else 0 End As pk 
         From information_schema.columns 
        Where table_schema = database() 
          And table_name = ?
     Order by information_schema.columns.ordinal_position ASC", [
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
        "bigint" => ColumnBigInt::class,
        "int" => ColumnInt::class,
        "integer" => ColumnInt::class,
        "smallint" => ColumnSmallInt::class,
        "decimal" => ColumnDecimal::class,
        "datetime" => ColumnDatetime::class,
        "date" => ColumnDatetime::class,
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