<?php

namespace Websyspro\Entity\Core\Bases;

use Websyspro\Entity\Core\Commons\Now;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Events\Delete;
use Websyspro\Entity\Decorations\Events\Insert;
use Websyspro\Entity\Decorations\Events\Update;
use Websyspro\Entity\Decorations\Generations\AutoIncrement;
use Websyspro\Entity\Decorations\Requireds\NotNull;

class BaseEntity
{
  #[NotNull()]
  #[Number()]
  #[PrimaryKey()]
  #[AutoIncrement()]    
  public int $Id;

  #[Flag()]
  #[NotNull()]
  #[Insert(1)]
  public bool $Actived;

  #[NotNull()]
  #[Number()]
  #[Insert(1)]
  public int $ActivedBy;

  #[NotNull()]
  #[Datetime()]
  #[Insert(Now::class)]
  public string $ActivedAt;

  #[NotNull()]
  #[Number()]
  #[Insert(1)] 
  public int $CreatedBy;

  #[NotNull()]
  #[Datetime()]
  #[Insert(Now::class)]
  public string $CreatedAt;

  #[Number()]
  #[Update(1)]
  public int $UpdatedBy;

  #[Datetime()]
  #[Update(Now::class)]
  public ?string $UpdatedAt;

  #[Flag()]
  #[Delete(1)]
  public ?bool $Deleted;

  #[Number()]
  #[Delete(1)]
  public ?int $DeletedBy;

  #[Datetime()]
  #[Delete(Now::class)]
  public ?string $DeletedAt;
}