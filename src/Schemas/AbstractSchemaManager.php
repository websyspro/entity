<?php

namespace Websyspro\Entity\Schemas;

use Websyspro\Entity\Interfaces\CommandScript;
use Websyspro\Entity\Types\ColumnAutoIncrement;
use Websyspro\Entity\Types\ColumnAutoUUID;
use Websyspro\Entity\Types\ColumnBigInt;
use Websyspro\Entity\Types\ColumnBlob;
use Websyspro\Entity\Types\ColumnDate;
use Websyspro\Entity\Types\ColumnDatetime;
use Websyspro\Entity\Types\ColumnDecimal;
use Websyspro\Entity\Types\ColumnDouble;
use Websyspro\Entity\Types\ColumnFlag;
use Websyspro\Entity\Types\ColumnFloat;
use Websyspro\Entity\Types\ColumnInt;
use Websyspro\Entity\Types\ColumnLongBlob;
use Websyspro\Entity\Types\ColumnLongText;
use Websyspro\Entity\Types\ColumnMediumText;
use Websyspro\Entity\Types\ColumnSmallInt;
use Websyspro\Entity\Types\ColumnText;
use Websyspro\Entity\Types\ColumnTime;
use Websyspro\Entity\Types\ColumnTimeStamp;
use Websyspro\Entity\Types\ColumnUUID;
use function sprintf;

abstract class AbstractSchemaManager
{
  public array $commandScritps = [];

  public function __construct(
    public AbstractEntityStructure $entityStructure,
    public AbstractEntityStructurePersisteds $entityStructurePersisteds
  ){}

  abstract protected function entityExists(): bool;
  abstract protected function columnAutoIncrement( string $column ): string;
  abstract protected function columnAutoUUID( string $column ): string;  
  abstract protected function columnBigInt( string $column ): string;
  abstract protected function columnBlob( string $column ): string;
  abstract protected function columnBoolean( string $column ): string;
  abstract protected function columnDate( string $column ): string;
  abstract protected function columnDatetime( string $column ): string;
  abstract protected function columnDecimal( string $column ): string;
  abstract protected function columnDouble( string $column ): string;
  abstract protected function columnEnum( string $column ): string;
  abstract protected function columnFlag( string $column ): string;
  abstract protected function columnFloat( string $column ): string;
  abstract protected function columnInteger( string $column ): string;
  abstract protected function columnJSON( string $column ): string;
  abstract protected function columnLongBlob( string $column ): string;
  abstract protected function columnLongText( string $column ): string;
  abstract protected function columnMediumBlob( string $column ): string;
  abstract protected function columnMediumInt( string $column ): string;
  abstract protected function columnMediumText( string $column ): string;
  abstract protected function columnSmallInt( string $column ): string;
  abstract protected function columnText( string $column ): string;
  abstract protected function columnTime( string $column ): string;
  abstract protected function columnTimeStamp( string $column ): string;
  abstract protected function columnTinyBlob( string $column ): string;
  abstract protected function columnTinyInt( string $column ): string;
  abstract protected function columnUUID( string $column ): string;  
  abstract protected function columnYear( string $column ): string;  
  abstract protected function entityCreateScript(): void;
  abstract protected function entityCreateIndexes(): void;
  abstract protected function entityCreateUniques(): void;
  abstract protected function getColumnsFromEntityPersisteds(): array;

  public function asyncEntity(
  ): void {
    if( $this->entityStructure->synchronize === true ){
      if( $this->entityExists() === false ){
        $this->entityCreateScript();
        $this->entityCreateIndexes();
        $this->entityCreateUniques();
      } else {
        $this->getColumnsFromEntityPersisteds();
      }

      $this->asyncUpdate();
    }
  }

  public function getColumnLength(
    string $column
  ): int {
    return $this->entityStructure->lengths->items[ $column ] ?? 255;
  }

  public function getColumnPrecision(
    string $column
  ): string|null {
    if( isset( $this->entityStructure->precisions->items[ $column ]) === false){
      return null;
    }

    return sprintf( "%s,%s", 
      $this->entityStructure->precisions->items[ $column ]->precision, 
      $this->entityStructure->precisions->items[ $column ]->scale
    );
  }

  public function getColumnRequired(
    string $column
  ): string {
    return isset( $this->entityStructure->requireds->items[ $column ]) 
      ? "Not Null" : "Null"; 
  }

  public function getColumnPrimaryKey(
    string $column
  ): string|null {
    if( isset( $this->entityStructure->primaryKeys->items[ $column ])){
      return "primary key";
    }

    return null;
  }

  public function getAliasFromEntity(
  ): string {
    return $this->entityStructure->entityNames->alias;
  }  

  public function getColumnsFromEntity(
    array $columnList = []
  ): string {
    foreach( $this->entityStructure->columns->items as $column ){
      $columnList[] = match( $this->entityStructure->types->items[ $column ]){
        ColumnAutoIncrement::class => $this->columnAutoIncrement( $column ),
        ColumnAutoUUID::class => $this->columnAutoUUID( $column ),
        ColumnBigInt::class => $this->columnBigInt( $column ),
        ColumnBlob::class => $this->columnBlob( $column ),
        // ColumnBoolean::class => $this->columnBoolean( $column ),
        ColumnDate::class => $this->columnDate( $column ),
        ColumnDatetime::class => $this->columnDatetime( $column ),
        ColumnDecimal::class => $this->columnDecimal( $column ),
        ColumnDouble::class => $this->columnDouble( $column ),
        // ColumnEnum::class => $this->columnEnum( $column ),
        ColumnFlag::class => $this->columnFlag( $column ),
        ColumnFloat::class => $this->columnFloat( $column ),
        ColumnInt::class => $this->columnInteger( $column ),
        // ColumnJSON::class => $this->columnJSON( $column ),
        ColumnLongBlob::class => $this->columnLongBlob( $column ),
        ColumnLongText::class => $this->columnLongText( $column ),
        // ColumnMediumBlob::class => $this->columnMediumBlob( $column ),
        // ColumnMediumInt::class => $this->columnMediumInt( $column ),
        ColumnMediumText::class => $this->columnMediumText( $column ),
        ColumnSmallInt::class => $this->columnSmallInt( $column ),
        ColumnText::class => $this->columnText( $column ),
        ColumnTime::class => $this->columnTime( $column ),
        ColumnTimeStamp::class => $this->columnTimeStamp( $column ),
        // ColumnTinyBlob::class => $this->columnTinyBlob( $column ),
        // ColumnTinyInt::class => $this->columnTinyInt( $column ),
        ColumnUUID::class => $this->columnUUID( $column ),
        // ColumnYear::class => $this->columnYear( $column ),
          default => $column
      };
    }
    
    return implode(
      ",", $columnList
    );
  } 

  public function asyncUpdate(
  ): void {
    foreach( $this->commandScritps as $commandScript ){
      if( $commandScript instanceof CommandScript ){
        $commandScript->execute();
      }
    }
  }
}