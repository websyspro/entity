<?php

namespace Websyspro\Entity\Decorations\Columns;

use Attribute;
use UnitEnum;
use Websyspro\Commons\DataList;
use Websyspro\Entity\Enums\ColumnType;
use Websyspro\Entity\Enums\AttributeType;
use Websyspro\Entity\Interfaces\IAbstractColumn;

#[Attribute( Attribute::TARGET_PROPERTY )]
class Enum
extends IAbstractColumn
{
  public AttributeType $attributeType = AttributeType::column;
  public ColumnType $columnType = ColumnType::longtext;

  public function __construct(
    public string $enum
  ){}

  public function sql(
  ): string {
    $enums = DataList::create(
      $this->enum::cases()
    )->mapper(fn(UnitEnum $case) => "'{$case->value}'");

    return sprintf("enum(%s)", ...[
      $enums->joinWithComma()
    ]);
  } 
}