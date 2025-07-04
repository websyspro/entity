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

  public function list(
  ): DataList {
    return $this->primaryKeys->copy()->mapper(
      fn(IPersistedPrimaryKey $persistedPrimaryKey) => (
        $persistedPrimaryKey->name
      )
    );
  }

  public function isRequired(
    string $name
  ): bool {
    return (
      $this->list()->where(
        fn(IPersistedPrimaryKey $persistedPrimaryKey) => (
          $persistedPrimaryKey->name === $name
        )
      )->exist()
    );
  }
}