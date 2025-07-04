<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;
use Websyspro\Entity\Interfaces\IStatisticsItem;
use Websyspro\Entity\Interfaces\IStatisticsNamesItem;

class StructureTableStatistics
extends StructureTableAbstract
{
  public function list(
  ): DataList {
    return $this->properties(
      AttributeType::indexes
    );
  }

  public function listNames(
  ): DataList  {
    return (
      $this
        ->list()
        ->mapper(
          fn(IProperties $property) => (
            new IStatisticsItem(
              $property->name, 
              $property->items
                ->first()->indexGroup
            )
          )
        )
        ->reduce([], function(mixed $curr, IStatisticsItem $statisticsItem){
          $curr[$statisticsItem->indexGroup][] = $statisticsItem->name; 
          return $curr;
        })
        ->mapper(fn(array $indexGroups) => DataList::create($indexGroups))
        ->mapper(fn(DataList $indexe) => new IStatisticsNamesItem("Index_{$indexe->join("_")}", $indexe->joinWithComma()))
    );
  }

  public function isIndex(
    string $name
  ): bool {
    return (
      $this->listNames()->where(
        fn(IStatisticsNamesItem $statisticsItem) => (
          $statisticsItem->name === $name
        )
      )->exist()
    );
  }
}