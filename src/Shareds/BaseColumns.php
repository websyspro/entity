<?php

namespace Websyspro\Entity\Shareds;

/**
 * Represents base entity column structure for ordering purposes.
 * Separates columns into initial positions (primary keys) and end positions (audit fields).
 * Used by AbstractEntity to maintain consistent column ordering across all entities.
 */
class BaseColumns
{
  /**
   * Initializes base column structure with initial and end column arrays.
   * 
   * @param array $initials Column names that should appear first (typically primary key)
   * @param array $ends Column names that should appear last (typically timestamps, soft delete)
   */
  public function __construct(
    public array $initials,
    public array $ends
  ){}
}