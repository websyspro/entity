<?php

use Websyspro\Test\Crm\Entitys\UserRolesEntity;
use Websyspro\Test\Crm\Entitys\UsersEntity;
use Websyspro\Entity\Repository;

$start = microtime( true );

$repository = new Repository(
  UsersEntity::class
);

$repository->where( 
  fn( UsersEntity $user ) => 
    $user->EmailConfirmed === true &&
    $user->specialSalesConsultant->Id === $user->Id &&
    $user->specialSalesConsultant->IsActive === true &&
    $user->specialSalesConsultant->IsDeleted === false &&
    $user->UserRoles->where( fn( UserRolesEntity $userRoles ) =>
      $userRoles->UserId === $user->Id &&
      $userRoles->Role->Id === $userRoles->RoleId
    )
  );
$repository->orderByAsc( fn( UsersEntity $user ) => [ $user->Id ]);
$repository->paged( 2, 4 );

$rows = $repository->get();

$leftTimer = number_format(( microtime( true ) - $start ) * 1000, 6, ",", "." );
echo "Execute timer: {$leftTimer}(ms)" . PHP_EOL . PHP_EOL;

print_r( "ROWS: " . sizeof($rows) . PHP_EOL . PHP_EOL );
print_r( $rows );