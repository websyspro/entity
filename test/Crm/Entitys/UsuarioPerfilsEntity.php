<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\ColumnName;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Shareds\AbstractEntity;

#[EntityName( "AspNetUserRoles" )]
class UsuarioPerfilsEntity
extends AbstractEntity
{
  #[Text(36)]
  #[Unique(1)]
  #[PrimaryKey()]
  #[ForeignKey( UsuarioEntity::class )]
	public string $UserId;
  
  #[Text(36)]
  #[Unique(1)]
  #[PrimaryKey()]
  #[ForeignKey( PerfilEntity::class )]
	public string $RoleId;

  public PerfilEntity $Perfil;
}