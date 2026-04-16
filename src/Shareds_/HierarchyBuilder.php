<?php

namespace Websyspro\Entity\Shareds_;

use Websyspro\Entity\Enums\MultiLine;
use Websyspro\Entity\Interfaces\Join;

class HierarchyBuilder
{
  private array $colsAlias;
  private array $joins;
  private array $identityMap = [];
  private array $identityHierarchyMap = [];

  public function __construct(
    array $colsAlias = [],
    array $joins = []
  ){
    $this->colsAlias = $colsAlias;
    $this->joins = $joins;
  }

  private function isBuildRowSingle(
    array $row,
    array $identityMap,
    array $identityMapPrimaryKeys,
    array $identityMapValids = []
  ): bool {
    if( sizeof( $identityMap ) === 0 ){
      return true;
    }

    foreach( $identityMap as $identityMapRows ){
      foreach( $identityMapPrimaryKeys as $primaryKey ){
        $identityMapValids[$primaryKey] = $identityMapRows[$primaryKey] === $row[$primaryKey];
      }

      if( in_array( false, $identityMapValids ) === false ){
        return false;
      }
    }
    
    return true;
  }

  private function buildRow(
    string $alias,
    array $row,
    array $entityRow = []
  ): array {
    foreach( $row as $key => $value ){
      if( str_starts_with( $key, $alias )){
        $entityRow[ str_replace( "{$alias}_", "", $key ) ] = $value;
      }
    }

    return $entityRow;
  }

  private function columnByAlias(
    string $aliasTable,
    string $aliasColumn
  ): string {
    if( sizeof( $this->colsAlias ) !== 0 ){
      if( isset( $this->colsAlias[ $aliasTable ][ $aliasColumn ] ) === true ){
        return $this->colsAlias[ $aliasTable ][ $aliasColumn ];
      }
    }

    return $aliasColumn;
  }

  private function buildHierarchy(
    string $alias,
    array $identityMapItems
  ): array {
    $result = [];

    foreach ($identityMapItems as $item) {
      $newItem = $item;

      foreach ($this->joins as $join) {
        if ($join->entityParent->alias !== $alias) {
          continue;
        }

        if ($join->entity->alias === $alias) {
          continue;
        }

        $children = [];

        foreach( $this->identityMap[ $join->entity->alias ] as $subItem ){
          $itemValue = $item[ $this->columnByAlias( $join->entityParent->alias, $join->referenceKey )];
          $subItemValue = $subItem[ $this->columnByAlias( $join->entity->alias, $join->key )];

          if( $itemValue === $subItemValue ){
            $childWithHierarchy = $this->buildHierarchy(
              $join->entity->alias,
              [$subItem]
            );

            $children[] = count($childWithHierarchy) !== 0
              ? $childWithHierarchy[0]
              : $subItem;
          }
        }

        if($join->multiLineReal=== MultiLine::Yes) {
          $newItem[$join->entity->alias] = $children;
        } else {
          $newItem[$join->entity->alias] = count($children) > 0
            ? $children[0]
            : null;
        }
      }

      $result[] = $newItem;
    }

    return $result;
  }

  public function build(
    array $rows   
  ): array {
    foreach( $this->joins as $join ){
      if( isset( $this->identityMap[ $join->entity->alias ]) === false ){
        $this->identityMap[ $join->entity->alias ] = [];
      }

      foreach( $rows as $row ){
        $rowSingle = $this->buildRow(
          $join->entity->alias, $row
        );

        if( $this->isBuildRowSingle( $rowSingle, $this->identityMap[ $join->entity->alias ], $join->primaryKey ) === true ){
          $this->identityMap[ $join->entity->alias ][] = $rowSingle;
        }
      }
    }

    $joinFirst = reset( $this->joins );
    if( $joinFirst instanceof Join ){
      foreach( $this->identityMap as $alias => $identityMapItems ){
        if( $joinFirst->entity->alias === $alias ){
          $this->identityHierarchyMap = $this->buildHierarchy( $alias, $identityMapItems );
        }
      }
    }

    return $this->identityHierarchyMap;
  }
}