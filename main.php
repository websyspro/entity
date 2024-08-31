<?php

use Websyspro\Entity\Commons\EntityUtils;
use Websyspro\Entity\Decorations\Required;
use Websyspro\Entity\Decorations\Varchar;
use Websyspro\Entity\EntityColletion;
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

class ClientEntity extends EntityDefault {
  #[Varchar(64)]
  public string $Endereco;

  #[Varchar(32)]
  public string $Bairro;
}

if( EntityUtils::IsMigrate()) {
  new EntityColletion(
    Items: [
      PessoaEntity::class,
      ClientEntity::class
    ]
  );
}