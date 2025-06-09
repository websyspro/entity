<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedUnique;

class PersistedUniquesList
{
  public function __construct(
    private DataList $uniques
  ){} 
  
  public function List(
  ): DataList {
    return $this->uniques->Copy();
  }

  public function ListNames(
  ): DataList {
    return $this->List()->Mapper(
      fn(IPersistedUnique $persistedUnique) => (
        $persistedUnique->name
      )
    );
  }

  public function IsUnique(
    string $name
  ): bool {
    return (
      $this->List()->Where(
        fn(IPersistedUnique $persistedUnique) => (
          $persistedUnique->name === $name
        )
      )->Exist()
    );
  }
}