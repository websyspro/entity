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
    $this->setName();
  }

  private function setName(
  ): void {
    $this->name = "FOREIGNKEY_{$this->table}_{$this->key}_In_{$this->foreignKeyReferenceItem->table}_{$this->foreignKeyReferenceItem->key}";
  }
}