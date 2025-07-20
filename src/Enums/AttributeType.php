<?php

namespace Websyspro\Entity\Enums;

enum AttributeType: int
{
  case column = 1;
  case requireds = 2;
  case uniques = 3;
  case indexes = 4;
  case foreigns = 5;
  case oneToOne = 6;
  case oneToMany = 7;
  case primaryKey = 8;
  case generations = 9;
  case insert = 10;
  case update = 11;
  case delete = 12;
}