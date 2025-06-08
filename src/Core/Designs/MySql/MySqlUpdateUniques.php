<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\StructureTable;

class MySqlUpdateUniques
{
  public DataList $updateScripts;

  public function __construct(
    public DataList $persistedUniques,
    public StructureTable $structureTable
  ){}

  public function SetInicial(
  ): void {
    $this->updateScripts = DataList::Create();
  }

  public function SetAdd(
  ): void {}

  public function SetModify(
  ): void {}

  public function SetDrops(
  ): void {}

  public function StartUpdates(
  ): MySqlUpdateUniques {
    $this->SetInicial();
    $this->SetAdd();
    $this->SetModify();
    $this->SetDrops();  
    return $this;
  }

  public function UpdateScripts(
  ): DataList {
    return $this->updateScripts;
  }  
}