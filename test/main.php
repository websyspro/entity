<?php

use Websyspro\Entity\Interfaces\Parameter;
use Websyspro\Test\Edocente\Entitys\PostMetaEntity;
use Websyspro\Test\Edocente\Entitys\PostEntity;
use Websyspro\Test\Edocente\Enums\PostStatus;
use Websyspro\Entity\Repository;
use Websyspro\Test\Crm\Entitys\RolesEntity;
use Websyspro\Test\Crm\Entitys\UserRolesEntity;
use Websyspro\Test\Crm\Entitys\UsersEntity;

$start = microtime( true );

$repository = new Repository(
  UsersEntity::class
);

// $repository->where( fn( PostEntity $post ) => 
//   $post->Id === 10181 && 
//   $post->postStatus === PostStatus::Publish && 
//   $post->postMetas->where( fn( PostMetaEntity $postMeta ) =>
//     $postMeta->postId === $post->Id
//   )
// );

$repository->where( fn( UsersEntity $user ) => 
  $user->EmailConfirmed === true &&
  $user->specialSalesConsultant->Id === $user->Id &&
  $user->specialSalesConsultant->IsActive === true &&
  $user->specialSalesConsultant->IsDeleted === false &&
  $user->UserRoles->where( fn( UserRolesEntity $userRoles ) =>
    $userRoles->UserId === $user->Id &&
    $userRoles->Role->Id === $userRoles->RoleId
  )
);

$repository->queryBuilder();
$rows = $repository->get();

print_r( $repository->structureFile->tokens );
// print_r( $rows );


$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo PHP_EOL . PHP_EOL . "Execute timer: {$leftTimer}(ms)" . PHP_EOL;
