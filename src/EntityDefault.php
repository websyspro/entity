<?php

namespace Websyspro\EntityApi;
use Websyspro\EntityApi\Decorations\AutoInc;
use Websyspro\EntityApi\Decorations\BigInt;
use Websyspro\EntityApi\Decorations\Datetime;
use Websyspro\EntityApi\Decorations\Required;
use Websyspro\EntityApi\Decorations\TinyInt;

abstract class EntityDefault
{
  #[BigInt()]
  #[AutoInc()]
  #[Required()]
  public int $Id;

  #[TinyInt()]
  #[Required()]
  public int $Actived;

  #[BigInt()]
  #[Required()]
  public int $ActivedBy;

  #[Datetime()]
  #[Required()]
  public string $ActivedAt;

  #[BigInt()]
  #[Required()]
  public string $CreatedBy;
  
  #[Datetime()]
  #[Required()]
  public string $CreatedAt;
  
  #[BigInt()]
  public int $UpdatedBy;
  
  #[Datetime()]
  public string $UpdatedAt;

  #[TinyInt()]
  public int $Deleted;
  
  #[BigInt()]
  public int $DeletedBy;
  
  #[Datetime()]
  public string $DeletedAt;  
}