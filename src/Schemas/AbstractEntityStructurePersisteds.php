<?php

namespace Websyspro\Entity\Schemas;

use Websyspro\Entity\Decorators\Entity;
use Websyspro\Entity\Interfaces\EntityColumns;
use Websyspro\Entity\Interfaces\EntityNames;
use ReflectionAttribute;
use ReflectionClass;
use Websyspro\Entity\Interfaces\PrecisionDetails;
use function count;

abstract class AbstractEntityStructurePersisteds
{
  public EntityNames $entityNames;
  public EntityColumns $columns;
  public EntityColumns $alias;
  public EntityColumns $types;
  public EntityColumns $lengths;
  public EntityColumns $precisions;
  public EntityColumns $requireds;
  public EntityColumns $primaryKeys;
  public EntityColumns $generateds;
  public EntityColumns $indexes;
  public EntityColumns $uniques;
  public EntityColumns $foreignKeys;
  public EntityColumns $initialDefaults;

  public function __construct(
    private readonly ReflectionClass $reflectionClass
  ){
    $this->start();
  }

  private function start(
  ): void {
    $this->getInitialArgs();
    $this->getEntityNames();
    $this->getEntityColumns();
  }

  private function getInitialArgs(
  ): void {
    $this->columns = new EntityColumns();
    $this->alias = new EntityColumns();
    $this->types = new EntityColumns();
    $this->lengths = new EntityColumns();
    $this->precisions = new EntityColumns();
    $this->requireds = new EntityColumns();
    $this->primaryKeys = new EntityColumns();
    $this->generateds = new EntityColumns();
    $this->indexes = new EntityColumns();
    $this->uniques = new EntityColumns();
    $this->foreignKeys = new EntityColumns();
    $this->initialDefaults = new EntityColumns();
  }

  private function getEntityNames(
  ): void {
    $entityNames = $this->reflectionClass
      ->getAttributes( Entity::class );

    foreach( $entityNames as $entityName ){
      if( $entityName instanceof ReflectionAttribute ){
        $entityInstance = $entityName->newInstance();

        if( $entityInstance instanceof Entity ){
          $this->entityNames = new EntityNames(
            $entityInstance->alias,
            $entityInstance->alias
          );
        }
      }
    }    
  }

  public function extractColumnType(
    string $type
  ): string {
    return strtolower(
      preg_replace( "#\\(.*$#", "", $type )
    );
  }

  public function extractColumnGenerateds(
    string $type    
  ): bool {
    return false;
  }

  public function extractColumnLength(
    string $type
  ): int {
    if((bool)preg_match( "#^decimal#", strtolower($type)) === true ){
      return 0;
    }

    return (int)preg_replace(
      [ "#^.*\\(#", "#\\).*$#" ], "", $type
    );
  }
  
  public function extractColumnPrecisions(
    string $type
  ): PrecisionDetails|null {
    if((bool)preg_match( "#^decimal#", strtolower($type)) === false ){
      return null;
    }

    $precisions = explode( ",", preg_replace(
      [ "#^.*\\(#", "#\\).*$#" ], "", $type
    ));

    if( count( $precisions ) === 2 ){
      [ $precision, $scale ] = $precisions;
      return new PrecisionDetails(
        (int)$precision, (int)$scale
      );
    }

    return null;
  } 
  
  abstract public function getColumnsFromEntityPersisteds(
  ): array;

  abstract public function getEntityColumns(
  ): void; 
}