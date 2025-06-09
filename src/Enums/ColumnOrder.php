<?php

namespace Websyspro\Entity\Enums;

enum ColumnOrder: string {
  case Initial = "Id";
  case Base = "Id|Actived|ActivedBy|ActivedAt|CreatedBy|CreatedAt|UpdatedBy|UpdatedAt|Deleted|DeletedBy|DeletedAt";
  case End = "Actived|ActivedBy|ActivedAt|CreatedBy|CreatedAt|UpdatedBy|UpdatedAt|Deleted|DeletedBy|DeletedAt";
}