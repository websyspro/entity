<?php

namespace Websyspro\Entity\Schemas;

use Websyspro\Connection\Database;
use Websyspro\Entity\Interfaces\PrecisionDetails;
use Websyspro\Entity\Types\ColumnAutoIncrement;
use Websyspro\Entity\Types\ColumnAutoUUID;
use Websyspro\Entity\Types\ColumnBigInt;
use Websyspro\Entity\Types\ColumnBlob;
use Websyspro\Entity\Types\ColumnDatetime;
use Websyspro\Entity\Types\ColumnDecimal;
use Websyspro\Entity\Types\ColumnDouble;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnInt;
use Websyspro\Entity\Types\ColumnLongBlob;
use Websyspro\Entity\Types\ColumnLongText;
use Websyspro\Entity\Types\ColumnMediumText;
use Websyspro\Entity\Types\ColumnSmallInt;
use Websyspro\Entity\Types\ColumnText;
use Websyspro\Entity\Types\ColumnTime;
use Websyspro\Entity\Types\ColumnTimeStamp;
use function in_array;

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
             ,information_schema.columns.extra as extra
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
        "mediumtext" => ColumnMediumText::class,
        "longtext" => ColumnLongText::class,
        "varchar" => ColumnText::class,
        "tinyint" => ColumnFlag::class,
        "bigint" => ColumnBigInt::class,
        "int" => ColumnInt::class,
        "integer" => ColumnInt::class,
        "smallint" => ColumnSmallInt::class,
        "decimal" => ColumnDecimal::class,
        "double" => ColumnDouble::class,
        "datetime" => ColumnDatetime::class,
        "timestamp" => ColumnTimeStamp::class,
        "date" => ColumnDatetime::class,
        "time" => ColumnTime::class,
        "blob" => ColumnBlob::class,
        "longblob" => ColumnLongBlob::class,
          default => $this->extractColumnType( $column->type )
      };

      /* Define Column Primary Key */
      if(( int )$column->pk === 1 ){
        if(( string )$column->extra === "auto_increment" ){
          $this->types->items[ $column->name ] = ColumnAutoIncrement::class;
        } else if(( string )$column->type === "varchar(36)" ){
          $this->types->items[ $column->name ] = ColumnAutoUUID::class;
        }
      }

      /* Define Generateds */
      if( in_array( $this->types->items[ $column->name ], [ ColumnAutoIncrement::class , ColumnAutoUUID::class ])){
        $this->generateds->items[ $column->name ] = $column->name;
      }

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