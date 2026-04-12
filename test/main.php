<?php

use Websyspro\Commons\Util;
use Websyspro\Test\Crm\Entitys\UserRolesEntity;
use Websyspro\Test\Crm\Entitys\UsersEntity;
use Websyspro\Entity\Repository;
use Websyspro\Test\Edocente\Entitys\PostEntity;
use Websyspro\Test\Edocente\Entitys\PostMetaEntity;
use Websyspro\Test\Edocente\Enums\PostStatus;
use Websyspro\Test\Edocente\Enums\PostType;

$start = microtime( true );

// $repository = new Repository(
//   UsersEntity::class
// );

// $repository->where( 
//   fn( UsersEntity $user ) => 
//     $user->EmailConfirmed === true &&
//     $user->specialSalesConsultant->Id === $user->Id &&
//     $user->specialSalesConsultant->IsActive === true &&
//     $user->specialSalesConsultant->IsDeleted === false &&
//     $user->UserRoles->where( fn( UserRolesEntity $userRoles ) =>
//       $userRoles->UserId === $user->Id &&
//       $userRoles->Role->Id === $userRoles->RoleId
//     )
//   );
// $repository->orderByAsc( fn( UsersEntity $user ) => [ $user->Id ]);
// $repository->paged( 1, 12 );
// $rows = $repository->get();

$repository = new Repository(PostEntity::class);
$repository->where(
  fn( PostEntity $post ) => (
    $post->postStatus === PostStatus::Publish &&
    $post->postType === PostType::Obra &&
    $post->postMetas->where( fn( PostMetaEntity $postMeta ) => 
      $postMeta->postId === $post->Id 
      // &&$postMeta->metaKey === 'link'
    )
  )
);
$repository->orderByAsc( fn( PostEntity $post ) => [ $post->postDate ]);
$repository->paged( 1, 12 );
$rows = $repository->get();

$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

// print_r( "ROWS: " . sizeof($rows) . PHP_EOL . PHP_EOL );

// var_dump( Util::hydrate( $rows, UsersEntity::class ));
print_r( $rows );