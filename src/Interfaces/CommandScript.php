<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Connection\Database;
use Websyspro\Utils\Collection;

class CommandScript
{
  public function __construct(
    public readonly Collection|string $command,
    public readonly string|null $message = null,
    public readonly array|null $args = null
  ){}

  public function execute(
  ): void {
    Database::execute(
      $this->command,
      $this->args
    );
  }
}