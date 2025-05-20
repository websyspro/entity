<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Core\StructureTable;

class TMySqlUpdateColumn
{
  public function __construct(
    public Collection $persistedColumn,
    public StructureTable $structureTable
  ){}

  private function GetHasCreateds(
  ): void {}

  private function GetHasUpdate(
  ): void {}
  
  private function GetHasDelete(
  ): void {}
  
  public function StartUpdates(
  ): void{
    $this->GetHasCreateds();
    $this->GetHasUpdate();
    $this->GetHasDelete();
  }

  public function GetSqlList(
  ): Collection {
    return new Collection();
  }
}