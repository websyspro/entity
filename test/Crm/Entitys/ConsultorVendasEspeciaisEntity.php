<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\LongText;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Shareds\AbstractEntity;

#[EntityName( "ConsultorVendasEspeciais" )]
class ConsultorVendasEspeciaisEntity
extends AbstractEntity
{
  #[Text(36)]
  #[PrimaryKey()]
  public string $Id;

  #[Datetime()]
  #[NotNull()]
  public string $Created;

  #[Text(36)]
  #[NotNull()]
  public string $CreatedById;

  #[Datetime()]
  public string $Updated;

  #[Text(36)]
  public string $UpdatedById;

  #[Text(100)]
  public string $Nome;

  #[Text(100)]
  public string $Email;

  #[Text(100)]
  public string $Ddd;

  #[Text(100)]
  public string $Celular;

  #[Text(100)]
  public string $Chapa;

  #[Text(100)]
  public string $Apelido;

  #[Text(36)]
  #[NotNull()]
  #[ForeignKey(GerenteVendasEspeciaisEntity::class)]
  public string $GerenteVendasEspeciaisId;
  public GerenteVendasEspeciaisEntity $GerenteVendasEspeciais;

  #[Flag()]
  #[NotNull()]
  public string $IsActive;

  #[Flag()]
  #[NotNull()]
  public string $IsDeleted;

  #[LongText()]
  public string $Imagem;

  #[Flag()]
  #[NotNull()]
  public string $Sincronizado;

  #[Datetime()]
  public string $UltimaSincronizacao;

  #[Flag()]
  #[NotNull()]
  public string $Especialista;

}