<?php

namespace Websyspro\Entity\Decorations\Statistics;

use Attribute;
use Websyspro\Entity\Enums\AttributeType;

/**
 * PHP attribute for defining database index on entity property.
 * Creates index for query performance optimization on frequently searched columns.
 * Supports composite indexes via indexGroup parameter for multi-column indexes.
 */
#[Attribute( Attribute::TARGET_PROPERTY )]
class Index
{
  public AttributeType $attributeType = AttributeType::indexes;

  /**
   * Initializes index with optional group identifier for composite indexes.
   * 
   * @param int $indexGroup Group ID for composite indexes (default: 1)
   */
  public function __construct(
    public int $indexGroup = 1
  ){}
}