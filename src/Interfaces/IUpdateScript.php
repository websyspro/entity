<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Enums\ScriptType;

class IUpdateScript
{
  public function __construct(
    public string $sql,
    public string $message,
    public ScriptType $scriptType
  ){}
}