<?php

namespace Websyspro\Entity\Shareds;

class HierarchyBuilder
{
  private array $joins;
  private array $identityMap = [];

  public function __construct(
    array $joins
  ){
    $this->joins = $joins;
  }

  public function build(
    array $rows
  ): array {
    $result = [];

    foreach( $rows as $row ){
      $rootAlias = $this->getRootAlias();
      $rootIdKey = $rootAlias . '_Id';
      $rootId = $row[$rootIdKey] ?? null;

      if (!$rootId) continue;

      if (!isset($this->identityMap[$rootAlias][$rootId])) {
        $entity = $this->extractEntity( $row, $rootAlias);
        $this->identityMap[$rootAlias][$rootId] = $entity;
        $result[$rootId] = &$this->identityMap[$rootAlias][$rootId];
      }

      foreach( $this->joins as $join ){
        $alias = $join->entity->alias;
        $parentAlias = $join->entityParent->alias;
        $isMulti = $join->multiLine->name === 'Yes';

        $entityData = $this->extractEntity($row, $alias);

        if (empty(array_filter($entityData))) continue;

        $entityId = $entityData[$join->key] ?? null;
        if (!$entityId) continue;

        if (!isset($this->identityMap[$alias][$entityId])) {
          $this->identityMap[$alias][$entityId] = $entityData;
        }

        $entityRef = &$this->identityMap[$alias][$entityId];

        $parentId = $row[$parentAlias . '_' . $join->referenceKey] ?? null;
        if (!$parentId) continue;

        if (!isset($this->identityMap[$parentAlias][$parentId])) continue;

        $parentRef = &$this->identityMap[$parentAlias][$parentId];

        if ($isMulti) {
          if (!isset($parentRef[$alias])) {
            $parentRef[$alias] = [];
          }

          if( !$this->inArrayByKey($parentRef[$alias], $entityId, $join->key)) {
            $parentRef[$alias][] = &$entityRef;
          }
        } else {
          $parentRef[$alias] = &$entityRef;
        }
      }
    }

    return array_values( $result );
  }

  private function extractEntity(
    array $row, 
    string $alias
  ): array {
    $data = [];

    foreach ($row as $key => $value) {
      if (strpos($key, $alias . '_') === 0) {
        $field = substr($key, strlen($alias) + 1);
        $data[$field] = $value;
      }
    }

    return $data;
  }

  private function inArrayByKey(
    array $array, $value, 
    string $key
  ): bool {
    foreach ($array as $item) {
      if (($item[$key] ?? null) === $value) {
        return true;
      }
    }

    return false;
  }

  private function getRootAlias(
  ): string {
    $parents = [];
    $children = [];

    foreach ($this->joins as $join) {
        $parents[] = $join->entityParent->alias;
        $children[] = $join->entity->alias;
    }

    $roots = array_diff($parents, $children);

    return reset($roots);
  }
}