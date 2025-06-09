<?php

namespace Websyspro\Entity\Core\Shareds;

use Websyspro\Entity\Core\StructureTable;

class ForeignKeyReferenceItem
{
  public StructureTable $structureTable;
  public string $table;
  public string $key;

  public function __construct(
    public string $reference
  ){
    $this->SetStarteds();
    $this->SetTable();
    $this->SetKey();
    $this->SetClear();
  }

  private function SetStarteds(
  ): void {
    $this->structureTable = (
      new StructureTable(
        $this->reference
      )
    );
  }

  private function SetTable(
  ): void {
    $this->table = $this->structureTable->table;
  }

  private function SetKey(
  ): void {
    $this->structureTable->PrimaryKeys()->List()
      ->Where(
        fn(string $primaryKeyName) => (
          $this->structureTable->Generations()->ListNames()->Where(
            fn(string $generationKey) => $generationKey === $primaryKeyName
          )
        )
      )
      ->ForEach(
        fn(string $primaryKeyName) => (
          $this->key = $primaryKeyName
        )
      );
  }

  private function SetClear(
  ): void {
    unset($this->structureTable);
    unset($this->reference);
  }
}