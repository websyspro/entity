<?php

namespace Websyspro\Entity\Shareds;

class AbstractEntity
{
  public function include(
    callable|null $fn = null
  ): AbstractEntity {
    return $this;
  }
}