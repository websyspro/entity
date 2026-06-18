<?php

namespace Websyspro\Entity\Shareds;

class ExpressionSelect
extends Utils
{
  public function __construct(
    public string $signary,
    public array $scopes = [],
    public array $tokens = [],
  ){}  
}