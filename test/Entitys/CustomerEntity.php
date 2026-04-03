<?php

namespace Websyspro\Test\Entitys;

use Websyspro\Entity\Decorations\BaseEntity;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\OneToMany;
use Websyspro\Entity\Decorations\Constraints\Unique;

class CustomerEntity
extends BaseEntity
{
  #[Text(255)]
  public string $Name;

  #[Text(14)]
  #[Unique()]
  public string $Cpf;
 
  #[OneToMany(DocumentEntity::class)]
  public DocumentEntity $Document;  

  #[Datetime()]
  public string $LastPurchaseAt;
}