<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Decimal;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\EntityList;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Shareds\AbstractEntity;
use Websyspro\Entity\Types\Varchar;

#[EntityName("Proposta")]
class PropostaEntity
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

  #[Text(11)]
  #[NotNull()]
  public string $Status;

  #[Text(36)]
  public string $ContatoId;

  #[Text(36)]
  #[NotNull()]
  #[ForeignKey(InstituicaoEntity::class)]
  public string $InstituicaoId;
  public InstituicaoEntity $Instituicao;

  #[Text(36)]
  #[ForeignKey(DistribuidorEntity::class)]
  public string $DistribuidorId;
  public DistribuidorEntity $Distribuidor;

  #[Text(36)]
  #[ForeignKey(ConsultorVendasEspeciaisEntity::class)]
  public string $ConsultorVendasEspeciaisId;
  public ConsultorVendasEspeciaisEntity $ConsultorVendasEspeciais;

  #[Text(3000)]
  public string $Observacao;

  #[Text(100)]
  public string $PrazoFaturamento;

  #[Text(100)]
  public string $NomeContato;

  #[Text(100)]
  public Varchar $NomeProposta;

  #[Decimal(18,2)]
  public string $DescontoFinalCliente;

  #[Flag()]
  #[NotNull()]
  public string $Arquivada;

  #[Text(3000)]
  public string $ComentarioArquivamento;

  #[Text(11)]
  #[NotNull()]
  public string $Versao;

  #[Text(100)]
  public string $IdPipelineZoho;

  #[Flag()]
  #[NotNull()]
  public string $Frete;

  #[EntityList(ItemPropostaEntity::class)]
  public EntityList $itemsProposta;
}