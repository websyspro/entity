<?php

namespace Websyspro\Test\Crm\Entitys;

use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\Decimal;
use Websyspro\Entity\Decorations\Columns\Flag;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Shareds\AbstractEntity;

#[EntityName("ItemProposta")]
class ItemPropostaEntity
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

  #[Number()]
  #[NotNull()]
  public int $Quantidade;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $CustoGrafica;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $CustoPapel;

  #[Decimal(18,2)]
  #[NotNull()]
  public float $PrecoCapa;

  #[Decimal(18,2)]
  #[NotNull()]
  public float $Desconto;

  #[Text(36)]
  #[NotNull()]
  #[ForeignKey(ObraEntity::class)]
  public string $ObraId;

  #[Text(36)]
  #[NotNull()]
  #[ForeignKey(PropostaEntity::class)]
  public string $PropostaId;

  #[Text(36)]
  public string $ValoresObraId;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $Amortizacao;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $AvalPlatSistema;

  #[Decimal(25,15)]
  public float $BaixaEstoqueObsoleto;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $Comissao;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $ComissaoMercado;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $CreditoPisConfins;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $CustoMixagem;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $CustosRateadosTI;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $Folha;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $Frete;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $FretePrimario;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $JuridicoCompliance;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $LivroProfessor;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $MaterialEmbalagem;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $Mkt;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $Pcld;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $Pdd;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $PlataformaLivro;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $PlataformaSistema;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $ServicosAssessoria;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $Terceiros;

  #[Decimal(6,5)]
  #[NotNull()]
  public float $Viagens;

  #[Decimal(18,2)]
  #[NotNull()]
  public float $ValorUnitario;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $Da;

  #[Decimal(18,2)]
  #[NotNull()]
  public float $PrecoCapaDistribuidor;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $CustoEditorial;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $CustoAvaliacao;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $CustoPlataforma;

  #[Decimal(25,15)]
  #[NotNull()]
  public float $CustoProdutoAcabado;

  #[Decimal(18,2)]
  public float $CmvSomosPercentual;

  #[Text(100)]
  public string $ZohoIdRelacionado;

  public ObraEntity $obra;
}