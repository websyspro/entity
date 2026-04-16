<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Shareds\AbstractEntity;

#[EntityName("Empresa")]
class EmpresaEntity
extends AbstractEntity
{
  #[Text(36)]
  #[PrimaryKey()]
  public string $Id;

  #[Text(100)]
  public string $Nome;

  #[Text(11)]
  #[NotNull()]
  public string $Ordem;

  #[Text(100)]
  public string $CorDestaque;

  #[Flag()]
  #[NotNull()]
  public string $IsDeleted;

  #[Text(11)]
  #[NotNull()]
  public string $OrdemParaCalculos;
}