<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedGeneration;

class PersistedGenerationsList
{
  public function __construct(
    private DataList $generations
  ){}

  public function List(
  ): DataList {
    return $this->generations->Copy();
  }

  public function ListNames(
  ): DataList {
    return $this->List()->Mapper(
      fn(IPersistedGeneration $persistedGeneration) => (
        $persistedGeneration->name
      )
    );
  }

  public function IsGeneration(
    string $name
  ): bool {
    return (
      $this->List()->Where(
        fn(IPersistedGeneration $persistedGeneration) => (
          $persistedGeneration->name === $name
        )
      )->Exist()
    );    
  }
}