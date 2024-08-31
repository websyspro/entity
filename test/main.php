<?php

use Websyspro\Entity\EntityColletion;
use Websyspro\Entity\Commons\EntityUtils;
use Websyspro\EntityTest\ClientEntity;
use Websyspro\EntityTest\PessoaEntity;

if( EntityUtils::IsMigrate()) {
  new EntityColletion(
    Items: [
      PessoaEntity::class,
      ClientEntity::class
    ]
  );
}