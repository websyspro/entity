<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedGeneration;

class PersistedGenerationsList
{
  public function __construct(
    private DataList $generations
  ){}

  public function list(
  ): DataList {
    return $this->generations->copy();
  }

  public function listNames(
  ): DataList {
    return $this->list()->mapper(
      fn(IPersistedGeneration $persistedGeneration) => (
        $persistedGeneration->name
      )
    );
  }

  public function isGeneration(
    string $name
  ): bool {
    return (
      $this->list()->where(
        fn(IPersistedGeneration $persistedGeneration) => (
          $persistedGeneration->name === $name
        )
      )->exist()
    );    
  }
}