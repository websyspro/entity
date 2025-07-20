<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedOneToOnes;

class PersistedOneToOnesList
{
  public function __construct(
    private DataList $foreignKeys
  ){}

  public function list(
  ): DataList {
    return $this->foreignKeys->copy();
  }

  public function listNames(
  ): DataList {
    return $this->list();
  }

  public function isForeignKey(
    string $name
  ): bool {
    return (
      $this->list()->where(
        fn(IPersistedOneToOnes $iPersistedOneToOnes) => (
          $iPersistedOneToOnes->name === $name
        )
      )->exist()
    );    
  }
}