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
  public function list(
  ): DataList {
    return $this->properties(
      AttributeType::uniques
    );
  }

  public function listNames(
  ): DataList  {
    return (
      $this
        ->list()
        ->Mapper(
          fn(IProperties $property) => (
            new IUniqueItem(
              $property->name, 
              $property->items
                ->first()->uniqueGroup
            )
          )
        )
        ->reduce([], function(mixed $curr, IUniqueItem $uniqueItem){
          $curr[$uniqueItem->uniqueGroup][] = $uniqueItem->name; 
          return $curr;
        })
        ->mapper(fn(array $uniqueGroups) => DataList::create($uniqueGroups))
        ->mapper(fn(DataList $uniques) => new IUniqueNameItems("Unique_{$uniques->join("_")}", $uniques->joinWithComma()))
    );
  }

  public function isUnique(
    string $name
  ): bool {
    return (
      $this->listNames()->where(
        fn(IUniqueNameItems $uniqueNameItems) => (
          $uniqueNameItems->name === $name
        )
      )->exist()
    );
  }
}