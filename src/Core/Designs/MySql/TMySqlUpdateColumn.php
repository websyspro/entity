<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\TList;
use Websyspro\Entity\Core\TStructureTable;

class TMySqlUpdateColumn
{
  public function __construct(
    public TList $persistedColumn,
    public TStructureTable $structureTable
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
  ): TList {
    return new TList();
  }
}