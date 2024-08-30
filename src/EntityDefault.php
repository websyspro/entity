<?php

namespace Websyspro\Entity
{
  use Websyspro\Entity\Decorations\AutoInc;
  use Websyspro\Entity\Decorations\BigInt;
  use Websyspro\Entity\Decorations\Datetime;
  use Websyspro\Entity\Decorations\Required;
  use Websyspro\Entity\Decorations\TinyInt;
  
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
}