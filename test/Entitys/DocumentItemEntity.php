<?php

namespace Websyspro\Test\Entitys;

use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Decorations\Columns\Decimal;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\BaseEntity;

class DocumentItemEntity
extends BaseEntity
{
  #[Number()]
  #[ForeignKey(DocumentEntity::class)]
  public string $DocumentId;
  
  public DocumentEntity $Document;
  
  #[Number()]
  #[ForeignKey(ProductEntity::class)]
  public string $ProductId;

  public ProductEntity $Product;

  #[Decimal(10,2)]
  public float $Value;

  #[Decimal(10,2)]
  public float $Amount;

  #[Decimal(10,2)]
  public float $Discount;

  #[Decimal(10,2)]
  public float $TotalValue;
}