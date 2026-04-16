<?php

namespace Websyspro\Entity\Interfaces;

class ItemForeignKey
{
  public function __construct(
    public string $table,
    public string $key,
    public string $referenceTable,
    public string $referenceKey,
  ){}
}