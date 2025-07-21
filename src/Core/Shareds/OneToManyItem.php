<?php

namespace Websyspro\Entity\Core\Shareds;

class OneToManyItem
{
  public string $name;

  public function __construct(
    public string $table,
    public string $key,
    public OneToManyReferenceItem $oneToManyReferenceItem
  ){
    $this->setName();
  }

  private function setName(
  ): void {
    $this->name = "{$this->key}";
  }
}