<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Connection\Database;
use Websyspro\Utils\Collection;
use function sprintf;

class CommandScript
{
  public function __construct(
    public readonly Collection|string $command,
    public readonly string|null $message = null
  ){}

  public function execute(
  ): void {
    Database::execute(
      $this->command, []
    );
  }
}