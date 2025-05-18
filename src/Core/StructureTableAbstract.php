<?php

namespace Websyspro\Entity\Core;

use ReflectionProperty;
use Websyspro\Commons\TList;
use Websyspro\Commons\TReflect;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Shareds\Properties;

class StructureTableAbstract
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
          new Properties($reflectionProperty->name, (
            new TList($reflectionProperty->getAttributes())
          ))
        )
      )
    );
  }

  public function Properties(
    AttributeType $attributeType
  ): TList {
    return (
      $this->PropertiesBase()->ForEach(
        fn(Properties $properties) => (
          $properties->items->Find(
            fn(AbstractColumn $abstractColumn) => (
              $abstractColumn->attributeType === $attributeType
            )
          )
        )
      )->Find(fn(Properties $properties) => (
        $properties->items->Count() !== 0
      ))
    );
  }
}