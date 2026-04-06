<?php

use Websyspro\Test\Edocente\Entitys\PostMetaEntity;
use Websyspro\Test\Edocente\Entitys\PostEntity;
use Websyspro\Test\Edocente\Enums\PostStatus;
use Websyspro\Entity\Repository;

$start = microtime( true );

$repository = new Repository(
  PostEntity::class
);

$repository->where( fn( PostEntity $post ) => 
  $post->Id === 10181 && 
  $post->postStatus === PostStatus::Publish && 
  $post->postMetas->where( fn( PostMetaEntity $postMeta ) =>
    $postMeta->postId === $post->Id
  )
);

$repository->queryBuilder();
$rows = $repository->get();

print_r( $rows );

$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo PHP_EOL . PHP_EOL . "Execute timer: {$leftTimer}(ms)" . PHP_EOL;
