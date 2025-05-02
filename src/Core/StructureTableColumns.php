<?php

namespace Websyspro\Entity\Core;

use ReflectionProperty;
use Websyspro\Commons\TList;
use Websyspro\Commons\TReflect;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Shareds\Properties;

class StructureTableColumns
{
  public TList $columns;

  public function __construct(
    public string $entity
  ){}

  public function Properties(
  ): TList {
    $properts = new TList(
      TReflect::PropertsFromClass(
        $this->entity
      )
    );

    $properts->Mapper(
      fn(ReflectionProperty $reflectionProperty) => (
        new Properties($reflectionProperty->name, new TList($reflectionProperty->getAttributes()))
      )
    );

    return $properts->ForEach(fn(Properties $properties) => (
      $properties->properties->Find(fn(AbstractColumn $abstractColumn) => (
        $abstractColumn->attributeType === AttributeType::Column
      ))
    ));
  }
  
  public function Types(
  ): TList {
    return $this->Properties();
  } 
}