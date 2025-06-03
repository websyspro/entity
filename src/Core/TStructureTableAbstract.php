<?php

namespace Websyspro\Entity\Core;

use ReflectionProperty;
use Websyspro\Commons\TList;
use Websyspro\Commons\TReflect;
use Websyspro\Entity\Enums\TAttributeType;
use Websyspro\Entity\Shareds\TAbstractColumn;
use Websyspro\Entity\Shareds\TProperties;

class TStructureTableAbstract
{
  public function __construct(
    public string $entity
  ){}

  private function PropertiesBase(
  ): TList {
    $properts = new TList(
      TReflect::PropertsFromClass(
        $this->entity
      )
    );

    return (
      $properts->Mapper(
        fn(ReflectionProperty $reflectionProperty) => (
          new TProperties($reflectionProperty->name, (
            new TList($reflectionProperty->getAttributes())
          ))
        )
      )
    );
  }

  public function Properties(
    TAttributeType $attributeType
  ): TList {
    return (
      $this->PropertiesBase()->ForEach(
        fn(TProperties $properties) => (
          $properties->items->Where(
            fn(TAbstractColumn $abstractColumn) => (
              $abstractColumn->attributeType === $attributeType
            )
          )
        )
      )->Where(fn(TProperties $properties) => (
        $properties->items->Count() !== 0
      ))
    );
  }
}