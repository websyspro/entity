<?php

namespace Websyspro\Entity\Enums;

enum DriverType:string {
  case MySql = "mysql";
  case SqlServer = 'sqlsrv';
  case PostgreSQL = 'pgsql';
}