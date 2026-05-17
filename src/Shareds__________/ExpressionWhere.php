<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Collection;
use Closure;

class ExpressionWhere
{
  public Collection $tokens;

  public function __construct(
    public Closure $closure
  ){
    $this->startups();
  }

  private function startups(
  ): void {
    $this->tokens = ExtractFromFN::get( $this->closure )->tokens;
  }  
}