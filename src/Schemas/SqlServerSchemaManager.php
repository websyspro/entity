<?php

namespace Websyspro\Entity\Schemas;

use Websyspro\Connection\Database;
use Websyspro\Entity\Interfaces\ColumnType;
use Websyspro\Entity\Interfaces\CommandScript;
use function sprintf;

class SqlServerSchemaManager
extends AbstractSchemaManager
{
  public function entityExists(
  ): bool {
    $results = Database::query(
      "select count(*) as cnt 
         from information_schema.tables 
        where information_schema.tables.table_name = ?",
      [ $this->getAliasFromEntity() ]
    );

    if( !$results ){
      return false;
    }

    [ $row ] = $results;
    return (bool)$row->cnt;
  } 

  public function entityCreateScript(
  ): void {
    $this->commandScritps[] = new CommandScript(
      command: 'Create Table %1$s (%2$s)',
      message: 'Creating table %1$s',
      args: [ $this->getAliasFromEntity(), $this->getColumnsFromEntity()]
    );
  }

  public function columnAutoIncrement(
    string $column
  ): string {
    return sprintf( "{$column} BigInt %s %s auto_increment",
      $this->getColumnRequired( $column ),
      $this->getColumnPrimaryKey( $column )
    );
  }

  public function columnAutoUUID(
    string $column
  ): string {
    return sprintf( "{$column} Varchar(36) %s %s",
      $this->getColumnRequired( $column ),
      $this->getColumnPrimaryKey( $column )
    );    
  }

  public function columnBigInt(
    string $column
  ): string {
    return sprintf( "{$column} BigInt %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnBlob(
    string $column
  ): string {
    return sprintf( "{$column} Blob %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnBoolean(
    string $column
  ): string {
    return sprintf( "{$column} Boolean %s",
      $this->getColumnRequired( $column )
    );
  }

  public function columnDate(
    string $column
  ): string {
    return sprintf( "{$column} Date %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnDatetime(
    string $column
  ): string {
    return sprintf( "{$column} Datetime %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnDecimal(
    string $column
  ): string {
    return sprintf( "{$column} Decimal(%s) %s",
      $this->getColumnPrecision( $column ),
      $this->getColumnRequired( $column )
    );    
  }

  public function columnDouble(
    string $column
  ): string {
    return sprintf( "{$column} Double %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnEnum(
    string $column
  ): string {
    return "/TO-DO";
  }

  public function columnFlag(
    string $column
  ): string {
    return sprintf( "{$column} TinyInt(1) %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnFloat(
    string $column
  ): string {
    return sprintf( "{$column} Float %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnInteger(
    string $column
  ): string {
    return sprintf( "{$column} Int %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnJSON( 
    string $column
  ): string {
    return sprintf( "{$column} LongText %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnLongBlob(
    string $column
  ): string {
    return sprintf( "{$column} LongBlob %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnLongText(
    string $column
  ): string {
    return sprintf( "{$column} LongText %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnMediumBlob(
    string $column
  ): string {
    return sprintf( "{$column} MediumBlob %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnMediumInt(
    string $column
  ): string {
    return sprintf( "{$column} MediumInt %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnMediumText(
    string $column
  ): string {
    return sprintf( "{$column} MediumText %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnSmallInt(
    string $column
  ): string {
    return sprintf( "{$column} SmallInt %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnText(
    string $column
  ): string {
    return sprintf( "{$column} Varchar(%s) %s",
      $this->getColumnLength( $column ),
      $this->getColumnRequired( $column )
    );     
  }

  public function columnTime(
    string $column
  ): string {
    return sprintf( "{$column} Time %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnTimeStamp(
    string $column
  ): string {
    return sprintf( "{$column} TimeStamp %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnTinyBlob(
    string $column
  ): string {
    return sprintf( "{$column} TinyBlob %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnTinyInt(
    string $column
  ): string {
    return sprintf( "{$column} TinyInt %s",
      $this->getColumnRequired( $column )
    );     
  }

  public function columnUUID(
    string $column
  ): string {
    return sprintf( "{$column} Varchar(36) %s",
      $this->getColumnRequired( $column )
    );     
  }

  public function columnYear(
    string $column
  ): string {
    return sprintf( "{$column} Year %s",
      $this->getColumnRequired( $column )
    );    
  }
  
  public function getColumnsFromEntityPersisteds(
  ): array {
    return array_map( fn( object $result ) => new ColumnType( 
      $result->name, $result->type, (int)$result->notnull === 1 ? "Not Null" : "Null"  
    ), $this->entityStructurePersisteds->getColumnsFromEntityPersisteds() );
  }  
}