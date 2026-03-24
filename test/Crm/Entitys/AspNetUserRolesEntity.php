<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Core\Bases\AbstractEntity;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\OneToOne;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Constraints\Unique;

class AspNetUserRolesEntity
extends AbstractEntity
{
  #[Text(36)]
  #[Unique(1)]
  #[PrimaryKey()]
  #[ForeignKey( AspNetUsersEntity::class )]
	public string $UserId;
  
  #[Text(36)]
  #[Unique(1)]
  #[PrimaryKey()]
  #[ForeignKey( AspNetRolesEntity::class )]
	public string $RoleId;

  #[OneToOne(AspNetRolesEntity::class)]
  public AspNetRolesEntity $aspNetRoles;
}