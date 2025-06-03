<?php

namespace Websyspro\Entity\Enums;

enum TAttributeType: int
{
  case Column = 1;
  case Requireds = 2;
  case Uniques = 3;
  case Indexes = 4;
  case Foreigns = 5;
  case PrimaryKey = 6;
  case Generations = 7;
  case Insert = 8;
  case Update = 9;
  case Delete = 10;
}