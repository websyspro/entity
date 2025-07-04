<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedRequireds;

class PersistedRequiredsList
{
  public function __construct(
    private DataList $requireds
  ){}

  public function list(
  ): DataList {
    return $this->requireds->copy();
  }

  public function isRequired(
    string $name
  ): bool {
    return (
      $this->list()->where(
        fn(IPersistedRequireds $persistedRequireds) => (
          $persistedRequireds->name === $name
        )
      )->exist()
    );
  }
}