<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedStatistics;

class PersistedStatisticsList
{
  public function __construct(
    private DataList $indexes
  ){} 
  
  public function List(
  ): DataList {
    return $this->indexes->Copy();
  }

  public function ListNames(
  ): DataList {
    return $this->List()->Mapper(
      fn(IPersistedStatistics $persistedStatistics) => (
        $persistedStatistics->name
      )
    );
  }

  public function IsIndex(
    string $name
  ): bool {
    return (
      $this->List()->Where(
        fn(IPersistedStatistics $persistedStatistics) => (
          $persistedStatistics->name === $name
        )
      )->Exist()
    );
  }
}