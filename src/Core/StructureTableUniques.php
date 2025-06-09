<?php

namespace Websyspro\Entity\Core;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IProperties;
use Websyspro\Entity\Interfaces\IUniqueItem;
use Websyspro\Entity\Interfaces\IUniqueNameItems;

class StructureTableUniques
extends StructureTableAbstract
{
  public function List(
  ): DataList {
    return $this->Properties(
      AttributeType::Uniques
    );
  }

  public function ListNames(
  ): DataList  {
    return (
      $this->List()
        ->Mapper(
          fn(IProperties $property) => (
            new IUniqueItem(
              $property->name, 
              $property->items->First()->uniqueGroup
            )
          )
        )
        ->Reduce([], function(mixed $curr, IUniqueItem $uniqueItem){
          $curr[$uniqueItem->uniqueGroup][] = $uniqueItem->name; 
          return $curr;
        })
        ->Mapper(fn(array $uniqueGroups) => DataList::Create($uniqueGroups))
        ->Mapper(fn(DataList $uniques) => new IUniqueNameItems("Unique_{$uniques->Join("_")}", $uniques->JoinWithComma()))
    );
  }

  public function IsUnique(
    string $name
  ): bool {
    return (
      $this->ListNames()->Where(
        fn(IUniqueNameItems $uniqueNameItems) => (
          $uniqueNameItems->name === $name
        )
      )->Exist()
    );
  }
}