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
  public int $postAuthor;

  #[Datetime()]
  #[NotNull()]
  #[ColumnName( "post_date" )]
  public string $postDate;

  #[Datetime()]
  #[NotNull()]  
  #[ColumnName( "post_date_gmt" )]
  public string $postDateGmt;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "post_content" )]
  public string $postContent;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "post_title" )]
  public string $postTitle;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "post_excerpt" )]
  public string $postExcerpt;

  #[NotNull()]
  #[Text(20)]
  #[ColumnName( "post_status" )]
  public string $postStatus;

  #[NotNull()]
  #[Text(20)]
  #[ColumnName( "comment_status" )]
  public string $commentStatus;

  #[NotNull()]
  #[Text(20)]
  #[ColumnName( "ping_status" )]
  public string $pingStatus;

  #[NotNull()]
  #[Text(255)]
  #[ColumnName( "post_password" )]
  public string $postPassword;

  #[NotNull()]
  #[Text(200)]
  #[ColumnName( "post_name" )]
  public string $postName;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "to_ping" )]
  public string $toPing;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "pinged" )]
  public string $pinged;

  #[NotNull()]
  #[Datetime()]
  #[ColumnName( "post_modified" )]
  public string $postModified;

  #[NotNull()]
  #[Datetime()]
  #[ColumnName( "post_modified_gmt" )]
  public string $postModifiedGmt;

  #[NotNull()]
  #[LongText()]
  #[ColumnName( "post_content_filtered" )]
  public string $postContentFiltered;

  #[NotNull()]
  #[Number()]
  #[ColumnName( "post_parent" )]
  public string $postParent;

  #[NotNull()]
  #[Text(255)]
  #[ColumnName( "guid" )]
  public string $guid;

  #[NotNull()]
  #[Number()]
  #[ColumnName( "menu_order" )]
  public string $menuOrder;

  #[NotNull()]
  #[Text(20)]
  #[ColumnName( "post_type" )]
  public string $postType;

  #[NotNull()]
  #[Text(100)]
  #[ColumnName( "post_mime_type" )]
  public string $postMimeType;

  #[NotNull()]
  #[Number]
  #[ColumnName( "comment_count" )]
  public string $commentCount;

  public EntityList $postMetas;
}