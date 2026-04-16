<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Decimal;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Shareds\AbstractEntity;

#[EntityName("ValoresObra")]
class ValoresObraEntity
extends AbstractEntity
{
  #[Text(36)]
  #[PrimaryKey()]
  public string $Id;

  #[Flag()]
  #[NotNull()]
  public string $IsActive;

  #[Flag()]
  #[NotNull()]
  public string $IsDeleted;

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

  #[Decimal(25,15)]
  #[NotNull()]
  public string $CustoUnitario;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $CustoProdutoAcabado;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $PrecoCapa;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $CustoPapel;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $CustoGrafica;

  #[Text(36)]
  #[NotNull()]
  #[ForeignKey(ObraEntity::class)]
  public string $ObraId;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $Da;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $PrecoCapaDistribuidor;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $CustoEditorial;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $CustoAvaliacao;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $CustoLivroProfessor;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $CustoPlataforma;

  #[Decimal(25,15)]
  #[NotNull()]
  public string $CmvSomosPercentual;
}