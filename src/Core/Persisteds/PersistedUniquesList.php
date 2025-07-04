<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedUnique;

class PersistedUniquesList
{
  public function __construct(
    private DataList $uniques
  ){} 
  
  public function list(
  ): DataList {
    return $this->uniques->copy();
  }

  public function listNames(
  ): DataList {
    return $this->list()->mapper(
      fn(IPersistedUnique $persistedUnique) => (
        $persistedUnique->name
      )
    );
  }

  public function isUnique(
    string $name
  ): bool {
    return (
      $this->list()->where(
        fn(IPersistedUnique $persistedUnique) => (
          $persistedUnique->name === $name
        )
      )->exist()
    );
  }
}