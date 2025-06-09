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
  public function List(
  ): DataList {
    return $this->Properties(
      AttributeType::Indexes
    );
  }

  public function ListNames(
  ): DataList  {
    return (
      $this->List()
        ->Mapper(
          fn(IProperties $property) => (
            new IStatisticsItem(
              $property->name, 
              $property->items->First()->indexGroup
            )
          )
        )
        ->Reduce([], function(mixed $curr, IStatisticsItem $statisticsItem){
          $curr[$statisticsItem->indexGroup][] = $statisticsItem->name; 
          return $curr;
        })
        ->Mapper(fn(array $indexGroups) => DataList::Create($indexGroups))
        ->Mapper(fn(DataList $indexe) => new IStatisticsNamesItem("Index_{$indexe->Join("_")}", $indexe->JoinWithComma()))
    );
  }

  public function IsIndex(
    string $name
  ): bool {
    return (
      $this->ListNames()->Where(
        fn(IStatisticsNamesItem $statisticsItem) => (
          $statisticsItem->name === $name
        )
      )->Exist()
    );
  }
}