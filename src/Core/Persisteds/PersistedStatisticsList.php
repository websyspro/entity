<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedStatistics;

class PersistedStatisticsList
{
  public function __construct(
    private DataList $indexes
  ){} 
  
  public function list(
  ): DataList {
    return $this->indexes->copy();
  }

  public function listNames(
  ): DataList {
    return $this->List()->mapper(
      fn(IPersistedStatistics $persistedStatistics) => (
        $persistedStatistics->name
      )
    );
  }

  public function isIndex(
    string $name
  ): bool {
    return (
      $this->list()->where(
        fn(IPersistedStatistics $persistedStatistics) => (
          $persistedStatistics->name === $name
        )
      )->exist()
    );
  }
}