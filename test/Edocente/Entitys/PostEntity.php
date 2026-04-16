<?php

namespace Websyspro\Test\Edocente\Entitys;

use Websyspro\Entity\Decorations\ColumnName;
use Websyspro\Entity\Decorations\Columns\Datetime;
use Websyspro\Entity\Decorations\Columns\LongText;
use Websyspro\Entity\Decorations\Columns\Number;
use Websyspro\Entity\Decorations\Columns\Text;
use Websyspro\Entity\Decorations\Constraints\PrimaryKey;
use Websyspro\Entity\Decorations\EntityName;
use Websyspro\Entity\Decorations\Generations\AutoIncrement;
use Websyspro\Entity\Decorations\Requireds\NotNull;
use Websyspro\Entity\Shareds\AbstractEntity;
use Websyspro\Entity\Shareds\EntityList;

#[EntityName( "wp_posts" )]
class PostEntity
extends AbstractEntity
{
  #[Number()]
  #[PrimaryKey()]
  #[AutoIncrement()]  
  #[ColumnName( "ID" )]
  public int $Id;

  #[Number()]
  #[NotNull()]
  #[ColumnName( "post_author" )]
  public int $Author;

  #[Datetime()]
  #[NotNull()]
  #[ColumnName( "post_date" )]
  public string $Date;

  #[Datetime()]
  #[NotNull()]  
  #[ColumnName( "post_date_gmt" )]
  public string $DateGmt;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "post_content" )]
  public string $Content;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "post_title" )]
  public string $Title;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "post_excerpt" )]
  public string $Excerpt;

  #[NotNull()]
  #[Text(20)]
  #[ColumnName( "post_status" )]
  public string $Status;

  #[NotNull()]
  #[Text(20)]
  #[ColumnName( "comment_status" )]
  public string $CommentStatus;

  #[NotNull()]
  #[Text(20)]
  #[ColumnName( "ping_status" )]
  public string $PingStatus;

  #[NotNull()]
  #[Text(255)]
  #[ColumnName( "post_password" )]
  public string $Password;

  #[NotNull()]
  #[Text(200)]
  #[ColumnName( "post_name" )]
  public string $Name;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "to_ping" )]
  public string $ToPing;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "pinged" )]
  public string $Pinged;

  #[NotNull()]
  #[Datetime()]
  #[ColumnName( "post_modified" )]
  public string $Modified;

  #[NotNull()]
  #[Datetime()]
  #[ColumnName( "post_modified_gmt" )]
  public string $ModifiedGmt;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "post_content_filtered" )]
  public string $ContentFiltered;

  #[NotNull()]
  #[Number()]
  #[ColumnName( "post_parent" )]
  public string $Parent;

  #[NotNull()]
  #[Text(255)]
  #[ColumnName( "guid" )]
  public string $Guid;

  #[NotNull()]
  #[Number()]
  #[ColumnName( "menu_order" )]
  public string $Order;

  #[NotNull()]
  #[Text(20)]
  #[ColumnName( "post_type" )]
  public string $Type;

  #[NotNull()]
  #[Text(100)]
  #[ColumnName( "post_mime_type" )]
  public string $MimeType;

  #[NotNull()]
  #[Number]
  #[ColumnName( "comment_count" )]
  public string $CommentCount;

  public EntityList $Metas;
}