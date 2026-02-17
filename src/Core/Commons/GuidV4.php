<?php

namespace Websyspro\Entity\Core\Commons;

use Websyspro\Commons\Util;

/**
 * Utility class for generating UUID version 4 (random) identifiers.
 * Provides globally unique identifiers for entity primary keys.
 * Delegates to Commons\Util for actual GUID generation.
 */
class GuidV4
{
  /**
   * Generates a new UUID v4 string.
   * 
   * @return string UUID v4 formatted string (e.g., "550e8400-e29b-41d4-a716-446655440000")
   */
  public static function get(
  ): string {
    /* Delegate to Util class for GUID generation */
    return Util::guidV4();
  }
}