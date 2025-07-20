<?php

namespace Websyspro\Entity\Core\Shareds;

use Websyspro\Entity\Core\StructureTable;

class OneToOneReferenceItem
{
  public StructureTable $structureTable;
  public string $table;
  public string $key;

  public function __construct(
    public string $reference
  ){
    $this->setStarteds();
    $this->setTable();
    $this->setKey();
    $this->setClear();
  }

  private function setStarteds(
  ): void {
    $this->structureTable = (
      new StructureTable(
        $this->reference
      )
    );
  }

  private function setTable(
  ): void {
    $this->table = $this->structureTable->table;
  }

  private function setKey(
  ): void {
    $this->structureTable
      ->primaryKeys()
      ->list()
      ->where(
        fn(string $primaryKeyName) => (
          $this->structureTable
            ->generations()
            ->listNames()
            ->where(
              fn(string $generationKey) => (
                $generationKey === $primaryKeyName
              )
            )
        )
      )
      ->forEach(
        fn(string $primaryKeyName) => (
          $this->key = $primaryKeyName
        )
      );
  }

  private function setClear(
  ): void {
    unset($this->structureTable);
    unset($this->reference);
  }
}