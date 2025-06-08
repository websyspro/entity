<?php

namespace Websyspro\Entity\Core\Designs\MySql;

use Websyspro\Commons\DataList;
use Websyspro\Entity\Core\StructureTable;

class MySqlUpdatePrimaryKeys
{
  public DataList $updateScripts;

  public function __construct(
    public DataList $persistedPrimaryKeys,
    public StructureTable $structureTable
  ){}

  public function SetColumnInicial(
  ): void {
    $this->updateScripts = DataList::Create();
  }

  public function StartUpdates(
  ): MySqlUpdatePrimaryKeys {
    $this->SetColumnInicial();
    return $this;
  }

  public function UpdateScripts(
  ): DataList {
    return $this->updateScripts;
  }  
}