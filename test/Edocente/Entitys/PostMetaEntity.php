<?php

namespace Websyspro\Test\Edocente\Entitys;

use Websyspro\Entity\Decorations\Generations\AutoIncrement;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Decorations\Columns\LongText;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\ColumnName;
use Websyspro\Entity\Decorations\Constraints\ForeignKey;
use Websyspro\Entity\Shareds\AbstractEntity;

#[EntityName( "wp_postmeta" )]
class PostMetaEntity 
extends AbstractEntity 
{
  #[Number()]
  #[NotNull()]
  #[PrimaryKey()]
  #[AutoIncrement()]
  #[ColumnName( "meta_id" )]
  public int $metaId;

  #[Number()]
  #[NotNull()]
  #[ColumnName( "post_id" )]
  public int $postId;

  #[NotNull()]
  #[Text(255)]
  #[ColumnName( "meta_key" )]
  public string $metaKey;

  #[LongText()]
  #[ColumnName( "meta_value" )]
  public string $metaValue;
}
