<?php

namespace Websyspro\Entity\Enums;

enum AttributeType: int
{
  case column = 1;
  case requireds = 2;
  case uniques = 3;
  case indexes = 4;
  case foreigns = 5;
  case primaryKey = 6;
  case generations = 7;
  case insert = 8;
  case update = 9;
  case delete = 10;
}