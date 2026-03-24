<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Constraints\Unique;
use Websyspro\Entity\Decorations\Columns\LongText;
use Websyspro\Entity\Core\Bases\AbstractEntity;
use Websyspro\Entity\Decorations\Columns\Text;

class AspNetRolesEntity
extends AbstractEntity
{
  #[Text(36)]
  #[Unique(1)]
  #[PrimaryKey()]
	public string $Id;
  
  #[Text(256)]
  public string $Name;

  #[Text(256)]
  public string $NormalizedName;

  #[LongText()]
  public string $ConcurrencyStamp;
}