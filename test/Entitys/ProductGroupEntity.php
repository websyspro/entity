<?php

namespace Websyspro\Test\Entitys;

use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\BaseEntity;

class ProductGroupEntity
extends BaseEntity
{
  #[Text(64)]
  public string $Name;
}