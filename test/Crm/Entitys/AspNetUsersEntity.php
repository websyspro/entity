<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Core\Bases\AbstractEntity;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\LongText;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\OneToMany;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Requireds\NotNull;

class AspNetUsersEntity
extends AbstractEntity
{
  #[Text(36)]
  #[Unique(1)]
  #[PrimaryKey()]
	public string $Id;

  #[OneToMany(AspNetUserRolesEntity::class)]
  public Collection $Roles;

  #[Text(256)]
  public string $UserName;

  #[Text(256)]
  public string $NormalizedUserName;

  #[Text(256)]
  public string $Email;

  #[Text(256)]
  public string $NormalizedEmail;

  #[Flag()]
  #[NotNull()]
  public string $EmailConfirmed;

  #[LongText()]
  public string $PasswordHash;

  #[LongText()]
  public string $SecurityStamp;

  #[LongText()]
  public string $ConcurrencyStamp;

  #[LongText()]
  public string $PhoneNumber;

  #[Flag()]
  #[NotNull()]
  public string $PhoneNumberConfirmed;

  #[Flag()]
  #[NotNull()]
  public string $TwoFactorEnabled;

  #[Datetime()]
  #[NotNull()]
  public string $LockoutEnd;

  #[Flag()]
  #[NotNull()]
  public string $LockoutEnabled;

  #[Flag()]
  #[NotNull()]
  public string $AccessFailedCount;
}