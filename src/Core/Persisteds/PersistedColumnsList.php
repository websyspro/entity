<?php

namespace Websyspro\Entity\Core\Persisteds;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Interfaces\IPersistedColumn;

class PersistedColumnsList
{
  public function __construct(
    private DataList $columns
  ){}

  public function Columns(
  ): DataList {
    return $this->columns->Copy();
  }

  public function Exist(
  ): bool {
    return $this->Columns()->Exist();
  }  

  public function ColumnExist(
    string $name
  ): bool {
    return (
      $this->Columns()->Where(
        fn(IPersistedColumn $column) => (
          $column->name === $name
        )
      )->Exist()
    );
  }
  
  public function Type(
    string $name
  ): string {
    return (
      $this->Columns()->Where(
        fn(IPersistedColumn $column) => (
          $column->name === $name
        )
      )->First()->type
    );
  }
}