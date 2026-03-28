<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;

class EntityMeta
{
  private EntityStructure $entityStructure;

  public function __construct(
    private string $entityClass
  ){
    $this->entityStructure = $entityClass::meta();
  }

  public function columns(
  ): Collection {
    return $this->entityStructure->columns;
  }

  public function foreigns(
  ): Collection {
    return $this->entityStructure->foreigns;
  }

  public function primaryKey(
  ): Collection {
    return $this->entityStructure->primaryKey;
  }  
}