<?php

use Websyspro\Commons\Collection;
use Websyspro\Entity\Enums\MetaType;
use Websyspro\Entity\Repository;
use Websyspro\Entity\Shareds\StructureFile;
use Websyspro\Test\Edocente\Entitys\PostEntity;
use Websyspro\Test\Edocente\Entitys\PostMetaEntity;
use Websyspro\Test\Edocente\Enums\PostStatus;
use Websyspro\Test\Entitys\DocumentEntity;

// use Websyspro\Entity\Repository;
use Websyspro\Test\Entitys\BoxEntity;
// use Websyspro\Test\Entitys\DocumentEntity;
use Websyspro\Test\Entitys\DocumentItemEntity;

// $start = microtime( true );

$staticTest = "THIAGO"; 

// $repository = new Repository(
//   BoxEntity::class
// );

// $repository->where( fn( BoxEntity $box ) => 
//   $box->Actived === true &&
//   $box->OperatorId === 6 &&
//   $box->CreatedAt >= '12/05/2024' &&
//   $box->Operador->Id === 6 || (
//     $box->Name === $staticTest &&
//     $box->Actived === false
//   ) && 
//   $box->Documents->where( fn( DocumentEntity $document ) =>
//     $document->Actived === true &&
//     $document->DocumentItems->where( fn( DocumentItemEntity $documentItem ) => 
//       $documentItem->Actived === true && 
//       $documentItem->Product->ProductGroup->Name === 'ELETRONICOS'
//     )
//   ) &&
//   $box->Name === 'EMERSON' &&
//   $box->CreatedAt <= '18/05/2024'
// );

// $repository->queryBuilder();

// $leftTimer = number_format((microtime( true ) - $start) * 1000, 6, ",", "." );
// echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

$start = microtime( true );

$repository = new Repository(
  PostEntity::class
);

$repository->where(
  fn( PostEntity $post ) => 
    $post->postStatus === PostStatus::Publish && 
    $post->Id === 8 && 
    $post->postMetas->where( fn( PostMetaEntity $PostMeta ) => 
      $PostMeta->postId === $post->Id
    )
);

for( $x=0; $x<1; $x++ ){
  $repository->queryBuilder();
  // $rows = $repository->get();

  print_r( $repository->structureFile->joins );

  print_r( $rows );
}

$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo PHP_EOL . PHP_EOL . "Execute timer: {$leftTimer}(ms)" . PHP_EOL;
