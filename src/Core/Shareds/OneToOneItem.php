<?php

namespace Websyspro\Entity\Core\Shareds;

class OneToOneItem
{
  public string $name;

  public function __construct(
    public string $table,
    public string $key,
    public OneToOneReferenceItem $oneToOneReferenceItem
  ){
    $this->setName();
  }

  private function setName(
  ): void {
    $this->name = "FOREIGNKEY_{$this->table}_{$this->key}_In_{$this->oneToOneReferenceItem->table}_{$this->oneToOneReferenceItem->key}";
  }
}