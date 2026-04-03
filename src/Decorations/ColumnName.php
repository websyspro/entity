<?php

namespace Websyspro\Entity\Decorations;

use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Enums\AttributeType;
use Attribute;

/**
 * PHP attribute for defining date column type in entity properties.
 * Generates DATE SQL column for storing date values without time component.
 * Stores dates in YYYY-MM-DD format in database.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class ColumnName
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  
  public function __construct(
    public string $columnName
  ){}
}