<?php

namespace Websyspro\Entity\Core;

use ReflectionProperty;
use Websyspro\Commons\DataList;
use Websyspro\Commons\Reflect;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;
use Websyspro\Entity\Interfaces\IProperties;

class StructureTableAbstract
{
  public function __construct(
    public string $entity
  ){}

  private function PropertiesBase(
  ): DataList {
    $properts = new DataList(
      Reflect::PropertsFromClass(
        $this->entity
      )
    );

    return (
      $properts->Mapper(
        fn(ReflectionProperty $reflectionProperty) => (
          new IProperties($reflectionProperty->name, (
            new DataList($reflectionProperty->getAttributes())
          ))
        )
      )
    );
  }

  public function Properties(
    AttributeType $attributeType
  ): DataList {
    return (
      $this->PropertiesBase()->ForEach(
        fn(IProperties $properties) => (
          $properties->items->Where(
            fn(IAbstractColumn $abstractColumn) => (
              $abstractColumn->attributeType === $attributeType
            )
          )
        )
      )->Where(fn(IProperties $properties) => (
        $properties->items->Count() !== 0
      ))
    );
  }
}