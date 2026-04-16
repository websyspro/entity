<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Shareds\AbstractEntity;

#[EntityName("Instituicao")]
class InstituicaoEntity
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
  #[NotNull()]
  public string $RazaoSocial;

  #[Text(20)]
  #[NotNull()]
  public string $CodigoMec;

  #[Text(11)]
  #[NotNull()]
  public string $Tipo;

  #[Text(250)]
  public string $Logradouro;

  #[Text(100)]
  public string $Numero;

  #[Text(100)]
  public string $Cep;

  #[Text(100)]
  public string $Bairro;

  #[Text(100)]
  public string $Latitude;

  #[Text(100)]
  public string $Longitude;

  #[Text(36)]
  #[NotNull()]
  #[ForeignKey(MunicipioEntity::class)]
  public string $MunicipioId;
  public MunicipioEntity $Municipio;

  #[Text(11)]
  #[NotNull()]
  public string $Rede;

  #[Text(18)]
  public string $CNPJ;

  #[Text(200)]
  public string $Complemento;

  #[Text(10)]
  public string $CodigoIBGE;

  #[Text(3)]
  public string $DDD;

  #[Text(11)]
  public string $Telefone;

  #[Text(200)]
  public string $Email;

  #[Text(2)]
  public string $Potencial;

  #[Text(11)]
  #[NotNull()]
  public string $Autonomia;

  #[Text(11)]
  #[NotNull()]
  public string $Localizacao;

  #[Flag()]
  #[NotNull()]
  public string $IsActive;

  #[Flag()]
  #[NotNull()]
  public string $IsDeleted;

  #[Flag()]
  #[NotNull()]
  public string $Sincronizado;

  #[Datetime()]
  public string $UltimaSincronizacao;
}