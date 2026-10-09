<?php

namespace Websyspro\Entity\Schemas;

use Websyspro\Connection\Database;
use Websyspro\Entity\Interfaces\ColumnType;
use Websyspro\Entity\Interfaces\CommandScript;
use Websyspro\Entity\Interfaces\ForeignKeyStructure;
use function array_slice;
use function sprintf;
use function is_string;

class SqLiteSchemaManager
extends AbstractSchemaManager
{
  public function entityExists(
  ): bool {
    $results = Database::query(
      "select count(*) as cnt 
         from sqlite_master 
        where sqlite_master.type='table' 
          and sqlite_master.name = ?",
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
      command: "Create Table {$this->getAliasFromEntity()} ({$this->getColumnsFromEntity()}{$this->entityCreateForeignKeysToEntity()})",
      message: "Creating table {$this->getAliasFromEntity()}"
    );
  }

  public function entityCreateIndexesScript(
    string $indexName
  ): void {
    $columns = implode( ", ", array_slice(
      explode( "_", $indexName ), 2
    ));

    $this->commandScritps[] = new CommandScript(
      command: "Create Index {$indexName} On {$this->getAliasFromEntity()} ({$columns})",
      message: "Creating table {$this->getAliasFromEntity()}"
    );  
  }

  public function entityDropIndexesScript( 
    string $indexName
  ): void {
    $this->commandScritps[] = new CommandScript(
      command: "Alter Table {$this->getAliasFromEntity()} Drop Index {$indexName}",
      message: "Drop Index {$indexName} on {$this->getAliasFromEntity()}"
    );
  }  

  public function entityCreateIndexes(
  ): void {
    foreach( $this->entityStructure->indexes->items as $indexName ){
      if( is_string( $indexName )){
        $this->entityCreateIndexesScript( $indexName );       
      }
    }
  }

  public function entityUpdateIndexes(
  ): void {
    foreach( array_diff( 
      $this->entityStructure->indexes->items,
      $this->entityStructurePersisteds->indexes->items
    ) as $indexName ){
      if( is_string( $indexName )){
        $this->entityCreateIndexesScript( $indexName );      
      }
    }

    foreach( array_diff( 
      $this->entityStructurePersisteds->indexes->items,
      $this->entityStructure->indexes->items
    ) as $indexName ){
      if( is_string( $indexName )){
        $this->entityDropIndexesScript( $indexName );      
      }
    }    
  }

  public function entityCreateUniquesScript(
    string $uniqueName
  ): void {
    $columns = implode( ", ", array_slice(
      explode( "_", $uniqueName ), 2
    ));

    $this->commandScritps[] = new CommandScript(
      command: "Create Unique Index {$uniqueName} On {$this->getAliasFromEntity()} ({$columns})",
      message: "Creating Constraint Unique {$uniqueName} on {$this->getAliasFromEntity()}"
    );
  }  

  public function entityDropUniquesScript( 
    string $uniqueName
  ): void {
    $this->commandScritps[] = new CommandScript(
      command: "Alter Table {$this->getAliasFromEntity()} Drop Unique {$uniqueName}",
      message: "Drop Index {$uniqueName} on {$this->getAliasFromEntity()}"
    );
  }  
  
  public function entityCreateUniques(
  ): void {
    foreach( $this->entityStructure->uniques->items as $uniqueName ){
      if( is_string( $uniqueName )){
        $this->entityCreateUniquesScript( $uniqueName );       
      }
    }
  }

  public function entityUpdateUniques(
  ): void {
    foreach( array_diff( 
      $this->entityStructure->uniques->items,
      $this->entityStructurePersisteds->uniques->items
    ) as $uniqueName ){
      if( is_string( $uniqueName )){
        $this->entityCreateUniquesScript( $uniqueName );      
      }
    }

    foreach( array_diff( 
      $this->entityStructurePersisteds->uniques->items,
      $this->entityStructure->uniques->items
    ) as $uniqueName ){
      if( is_string( $uniqueName )){
        $this->entityDropUniquesScript( $uniqueName );      
      }
    }    
  }
  
  public function entityCreateForeignKeyName(
    ForeignKeyStructure $foreignKeys, string $key 
  ): string {
    return sprintf( "FK_%s_%s_%s_%s",
      $this->getAliasFromEntity(), $key, $foreignKeys->entity->alias, $foreignKeys->references
    );
  }

  public function entityCreateForeignKeysScript(
    ForeignKeyStructure $foreignKeys, string $key
  ): void {
    // Implemented in entityCreateForeignKeysToEntity
    // SQLite cannot implement a foreign key constraint after the table has already been created.
  }

  public function entityCreateForeignKeys(
  ): void {
    // Implemented in entityCreateForeignKeysToEntity
    // SQLite cannot implement a foreign key constraint after the table has already been created.
  }  
  
  public function entityCreateForeignKeysToEntity(
  ): string|null {
    $foreignKeysItems = [];

    foreach( $this->entityStructure->foreignKeys->items as $key => $foreignKeys ){
      $foreignKeyName = $this->entityCreateForeignKeyName( 
        $foreignKeys, $key
      );

      array_push( $foreignKeysItems, sprintf( "Constraint %s Foreign Key (%s) References %s (%s) On Delete Cascade On Update Cascade",
        $foreignKeyName, $key, $foreignKeys->entity->alias, $foreignKeys->references
      ));
    }

    if( empty( $foreignKeysItems )){
      return null;
    }

    return sprintf(
      ", %s", implode(
        ",", $foreignKeysItems
      )
    );
  }  

  public function columnAutoIncrement(
    string $column
  ): string {
    return "{$column} Integer Primary Key Autoincrement";
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
    return sprintf( "{$column} Integer %s",
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
    return sprintf( "{$column} Real %s",
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
    return sprintf( "{$column} Text %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnLongBlob(
    string $column
  ): string {
    return sprintf( "{$column} Blob %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnLongText(
    string $column
  ): string {
    return sprintf( "{$column} Text %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnMediumBlob(
    string $column
  ): string {
    return sprintf( "{$column} Blob %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnMediumInt(
    string $column
  ): string {
    return sprintf( "{$column} Integer %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnMediumText(
    string $column
  ): string {
    return sprintf( "{$column} Text %s",
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
    return sprintf( "{$column} Text %s",
      $this->getColumnRequired( $column )
    );    
  }

  public function columnTinyBlob(
    string $column
  ): string {
    return sprintf( "{$column} Blob %s",
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
    return sprintf( "{$column} Integer %s",
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