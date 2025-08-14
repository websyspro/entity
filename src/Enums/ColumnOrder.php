<?php

namespace Websyspro\Entity\Enums;

enum ColumnOrder: string {
  case initial = "id";
  case base = "id|actived|activedBy|activedAt|createdBy|createdAt|updatedBy|updatedAt|deleted|deletedBy|deletedAt";
  case end = "actived|activedBy|activedAt|createdBy|createdAt|updatedBy|updatedAt|deleted|deletedBy|deletedAt";
}