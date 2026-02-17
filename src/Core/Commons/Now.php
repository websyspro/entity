<?php

namespace Websyspro\Entity\Core\Commons;

/**
 * Utility class for generating current timestamp strings.
 * Provides formatted datetime values for audit trail fields (created_at, updated_at, deleted_at).
 * Returns datetime in Brazilian format (dd/mm/yyyy HH:ii:ss).
 */
class Now
{
  /**
   * Returns current datetime as formatted string.
   * 
   * @return string Current datetime in format "dd/mm/yyyy HH:ii:ss"
   */
  public static function get(
  ): string {
    /* Generate current datetime in Brazilian format */
    return date( "d/m/Y H:i:s" );
  }
}