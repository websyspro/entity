<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Enums\AttributeType;

class IColumn
{
  public function __construct(
    public object $column,
    public IEntity $entity
  ){
    [ "column" => $this->column ] = $this->column->where(
      fn( IAbstractColumn $column ) => $column->attributeType === AttributeType::column
    )->all();
  }
}