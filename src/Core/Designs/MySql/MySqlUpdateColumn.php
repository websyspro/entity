<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\StructureTable;

class MySqlUpdateColumn
{
  public function __construct(
    public DataList $persistedColumn,
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
  ): DataList {
    return new DataList();
  }
}