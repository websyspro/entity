<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\EntityList;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Shareds\AbstractEntity;

#[EntityName("Obra")]
class ObraEntity
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

  #[Text(300)]
  public string $Titulo;

  #[Text(36)]
  #[NotNull()]
  #[ForeignKey(ColecaoEntity::class)]
  public string $ColecaoId;
  public ColecaoEntity $Colecao;

  #[Text(36)]
  #[NotNull()]
  #[ForeignKey(EditoraEntity::class)]
  public string $EditoraId;
  public EditoraEntity $Editora;

  #[Flag()]
  #[NotNull()]
  public string $IsActive;

  #[Flag()]
  #[NotNull()]
  public string $IsDeleted;

  #[Text(100)]
  public string $CodigoISBN;

  #[Text(36)]
  #[NotNull()]
  #[ForeignKey(SegmentoEntity::class)]
  public string $SegmentoId;
  public SegmentoEntity $Segmento;

  #[Text(11)]
  #[NotNull()]
  public string $SKU;

  #[Text(500)]
  public string $ObrasIdRelacionadas;

  #[Text(11)]
  #[NotNull()]
  public string $TipoObra;

  #[Text(36)]
  #[ForeignKey(EmpresaEntity::class)]
  public string $EmpresaId;
  public EmpresaEntity $Empresa;

  #[Text(100)]
  public string $ZohoId;

  #[EntityList(ValoresObraEntity::class)]
  public EntityList $ValoresObra;
}