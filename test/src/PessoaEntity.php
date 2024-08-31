<?php

namespace Websyspro\EntityTest
{
  use Websyspro\Entity\Decorations\Required;
  use Websyspro\Entity\Decorations\Varchar;
  use Websyspro\Entity\EntityDefault;

  class PessoaEntity extends EntityDefault
  {
    #[Varchar(255)]
    #[Required()]
    public string $Nome;

    #[Varchar(14)]
    public string $Fone;

    #[Varchar(64)]
    #[Required()]
    public string $Email;
  }
}