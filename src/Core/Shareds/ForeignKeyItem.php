<?php

namespace Websyspro\Entity\Core\Shareds;

class ForeignKeyItem
{
  public string $name;

  public function __construct(
    public string $table,
    public string $key,
    public ForeignKeyReferenceItem $foreignKeyReferenceItem
  ){
    $this->SetName();
  }

  private function SetName(
  ): void {
    $this->name = "FOREIGNKEY_{$this->table}_{$this->key}_In_{$this->foreignKeyReferenceItem->table}_{$this->foreignKeyReferenceItem->key}";
  }
}