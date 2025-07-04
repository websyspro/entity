<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedColumn;

class PersistedColumnsList
{
  public function __construct(
    private DataList $columns
  ){}

  public function columns(
  ): DataList {
    return $this->columns->copy();
  }

  public function exist(
  ): bool {
    return $this->columns()->exist();
  }  

  public function columnExist(
    string $name
  ): bool {
    return (
      $this->columns()->where(
        fn(IPersistedColumn $column) => (
          $column->name === $name
        )
      )->exist()
    );
  }
  
  public function type(
    string $name
  ): string {
    return (
      $this->columns()->where(
        fn(IPersistedColumn $column) => (
          $column->name === $name
        )
      )->first()->type
    );
  }
}