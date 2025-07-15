<?php

namespace Websyspro\Entity\Enums;

enum RelationshipType: int
{
  case oneToOne = 1;
  case oneToMany = 2;
}