<?php

namespace Websyspro\Entity\Core;

use ReflectionProperty;
use Websyspro\Commons\Collection;
use Websyspro\Commons\Reflect;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Shareds\Properties;

class StructureTableAbstract
{
  public function __construct(
    public string $entity
  ){}

  private function PropertiesBase(
  ): Collection {
    $properts = new Collection(
      Reflect::PropertsFromClass(
        $this->entity
      )
    );

    return (
      $properts->Mapper(
        fn(ReflectionProperty $reflectionProperty) => (
          new Properties($reflectionProperty->name, (
            new Collection($reflectionProperty->getAttributes())
          ))
        )
      )
    );
  }

  public function Properties(
    AttributeType $attributeType
  ): Collection {
    return (
      $this->PropertiesBase()->ForEach(
        fn(Properties $properties) => (
          $properties->items->Where(
            fn(AbstractColumn $abstractColumn) => (
              $abstractColumn->attributeType === $attributeType
            )
          )
        )
      )->Where(fn(Properties $properties) => (
        $properties->items->Count() !== 0
      ))
    );
  }
}