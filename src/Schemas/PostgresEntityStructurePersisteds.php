<?php

namespace Websyspro\Entity\Schemas;

use Websyspro\Connection\Database;
use Websyspro\Entity\Types\ColumnAutoIncrement;
use Websyspro\Entity\Types\ColumnAutoUUID;
use Websyspro\Entity\Types\ColumnBlob;
use Websyspro\Entity\Types\ColumnDate;
use Websyspro\Entity\Types\ColumnDatetime;
use Websyspro\Entity\Types\ColumnDecimal;
use Websyspro\Entity\Types\ColumnDouble;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnInt;
use Websyspro\Entity\Types\ColumnSmallInt;
use Websyspro\Entity\Types\ColumnText;
use Websyspro\Entity\Types\ColumnTime;
use Websyspro\Entity\Types\ColumnUUID;
use stdClass;

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
             ,information_schema.columns.column_default as extra
        ,Case 
         When Exists (
       Select 1
         From information_schema.key_column_usage kcu
   Inner Join information_schema.table_constraints tc On tc.constraint_name = kcu.constraint_name
          And tc.table_schema = kcu.table_schema
          And tc.table_name = kcu.table_name
        Where kcu.table_schema = information_schema.columns.table_schema
              And kcu.table_name = information_schema.columns.table_name
              And kcu.column_name = information_schema.columns.column_name
              And tc.constraint_type = 'PRIMARY KEY' ) Then 1 Else 0 End As pk
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
      "Select i.relname AS index_name
         From pg_class t
   Inner Join pg_index ix On ix.indrelid = t.oid
   Inner Join pg_class i On i.oid = ix.indexrelid
   Inner Join pg_namespace n On n.oid = t.relnamespace
        Where n.nspname = current_schema()
          And t.relname = ?
          And ix.indisunique = false
     Group By t.relname, i.relname", [
        strtolower( $this->entityNames->alias )
      ]
    );
  }
  
  public function getUniquesFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select i.relname AS unique_name
         From pg_class t
   Inner Join pg_index ix On ix.indrelid = t.oid
   Inner Join pg_class i On i.oid = ix.indexrelid
   Inner Join pg_namespace n On n.oid = t.relnamespace
        Where n.nspname = current_schema()
          And t.relname = ?
          And ix.indisunique = true
          And ix.indisprimary = false
     Group By t.relname, i.relname", [
        strtolower( $this->entityNames->alias )
      ]
    );
  }
  
  public function getForeignKeysFromEntityPersisteds(
  ): array {
    return Database::query(
      "Select con.conname AS constraint_name
         From pg_constraint con
   Inner Join pg_class t On t.oid = con.conrelid
   Inner Join pg_namespace n On n.oid = t.relnamespace
        Where n.nspname = current_schema()
          And t.relname = ?
          And con.contype = 'f'
     Group By t.relname
             ,con.conname", [
        strtolower( $this->entityNames->alias )
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
        "int" => ColumnInt::class,
        "int2" => ColumnSmallInt::class,
        "bigint" => ColumnInt::class,
        "boolean" => ColumnInt::class,
        "varchar" => ColumnText::class,
        "tinyint" => ColumnFlag::class,
        "integer" => ColumnInt::class,
        "decimal" => ColumnDecimal::class,
        "float8" => ColumnDouble::class,
        "datetime" => ColumnDatetime::class,
        "date" => ColumnDate::class,
        "time" => ColumnTime::class,
        "bytea" => ColumnBlob::class,
          default => $this->extractColumnType( $column->type )
      };

      /* Define Column Primary Key */
      if(( int )$column->pk === 1 ){
        if( $column->type === "bigint" && ( string )$column->extra === "nextval('test_fieldautoincrement_seq'::regclass)" ){
          $this->types->items[ $column->name ] = ColumnAutoIncrement::class;
        } else if( $column->type === "uuid" && ( string )$column->extra === "gen_random_uuid()" ){
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
      if( (int)$column->pk === 1  ){
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