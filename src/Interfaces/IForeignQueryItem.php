<?php

namespace Websyspro\Entity\Interfaces;

class IForeignQueryItem
{
  public function __construct(
    public string $table,
    public string $tableKey,
    public string $reference,
    public string $referenceKey
  ){}
}