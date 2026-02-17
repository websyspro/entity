<?php

namespace Websyspro\Entity\Enums;

/**
 * Enumeration indicating whether a column is virtual (computed/transient) or physical (persisted).
 * Virtual columns are not mapped to database tables and used for computed properties.
 * Physical columns are persisted in database and included in schema generation.
 */
enum ColumnVirtual {
  case Yes;
  case Not;
}