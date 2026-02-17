<?php

namespace Websyspro\Entity\Decorations\Columns;

use Websyspro\Entity\Shareds\AbstractColumn;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Commons\Collection;
use Attribute;
use UnitEnum;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Enum
extends AbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::longtext;

  public function __construct(
    public string $enum
  ){}

  public function sql(
  ): string {
    $enums = new Collection(
      $this->enum::cases()
    )->mapper(fn(UnitEnum $case) => "'{$case->value}'");

    return sprintf("enum(%s)", ...[
      $enums->joinWithComma()
    ]);
  } 
}