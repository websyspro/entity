<?php

namespace Websyspro\EntityTest
{
  use Websyspro\Entity\Decorations\Varchar;
  use Websyspro\Entity\EntityDefault;

  class ClientEntity extends EntityDefault {
    #[Varchar(64)]
    public string $Endereco;
  
    #[Varchar(32)]
    public string $Bairro;
  }
}