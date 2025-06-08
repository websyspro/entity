<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedPrimaryKey;
use Websyspro\Entity\Interfaces\IPersistedRequireds;

class PersistedPrimaryKeysList
{
  public function __construct(
    private DataList $primaryKeys
  ){}

  public function List(
  ): DataList {
    return $this->primaryKeys->Copy();
  }

  public function IsRequired(
    string $name
  ): bool {
    return (
      $this->List()->Where(
        fn(IPersistedPrimaryKey $persistedPrimaryKey) => (
          $persistedPrimaryKey->name === $name
        )
      )->Exist()
    );
  }
}