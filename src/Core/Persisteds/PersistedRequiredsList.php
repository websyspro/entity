<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedRequireds;

class PersistedRequiredsList
{
  public function __construct(
    private DataList $requireds
  ){}

  public function List(
  ): DataList {
    return $this->requireds->Copy();
  }

  public function IsRequired(
    string $name
  ): bool {
    return (
      $this->List()->Where(
        fn(IPersistedRequireds $persistedRequireds) => (
          $persistedRequireds->name === $name
        )
      )->Exist()
    );
  }
}