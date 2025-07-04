<?php

namespace Websyspro\Entity\Enums;

enum ColumnOrder: string {
  case initial = "Id";
  case base = "Id|Actived|ActivedBy|ActivedAt|CreatedBy|CreatedAt|UpdatedBy|UpdatedAt|Deleted|DeletedBy|DeletedAt";
  case end = "Actived|ActivedBy|ActivedAt|CreatedBy|CreatedAt|UpdatedBy|UpdatedAt|Deleted|DeletedBy|DeletedAt";
}