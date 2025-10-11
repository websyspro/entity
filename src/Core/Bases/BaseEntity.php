<?php

namespace Websyspro\Entity\Core\Bases;

use Websyspro\Commons\Mapper;
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
  public int $id;

  #[Flag()]
  #[NotNull()]
  #[Insert(1)]
  public bool $actived;

  #[NotNull()]
  #[Number()]
  #[Insert(1)]
  public int $activedBy;

  #[NotNull()]
  #[Datetime()]
  #[Insert(Now::class)]
  public string $activedAt;

  #[NotNull()]
  #[Number()]
  #[Insert(1)] 
  public int $createdBy;

  #[NotNull()]
  #[Datetime()]
  #[Insert(Now::class)]
  public string $createdAt;

  #[Number()]
  #[Update(1)]
  public ?int $updatedBy;

  #[Datetime()]
  #[Update(Now::class)]
  public ?string $updatedAt;

  #[Flag()]
  #[Insert(0)]
  #[Delete(1)]
  public ?bool $deleted;

  #[Number()]
  #[Delete(1)]
  public ?int $deletedBy;

  #[Datetime()]
  #[Delete(Now::class)]
  public ?string $deletedAt;

  public function exist(
  ): bool {
    return empty(get_object_vars($this)) === false;
  }

  public function mapper(
    string $toEntity
  ): mixed {
    return Mapper::to($toEntity)->from($this);
  }
}