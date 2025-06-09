<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedForeignKeys;

class PersistedForeignKeysList
{
  public function __construct(
    private DataList $foreignKeys
  ){}

  public function List(
  ): DataList {
    return $this->foreignKeys->Copy();
  }

  public function ListNames(
  ): DataList {
    return $this->List();
  }

  public function IsForeignKey(
    string $name
  ): bool {
    return (
      $this->List()->Where(
        fn(IPersistedForeignKeys $persistedForeignKeys) => (
          $persistedForeignKeys->name === $name
        )
      )->Exist()
    );    
  }
}