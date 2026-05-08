<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Shareds\IncludeList;
use Websyspro\Entity\Shareds\WhereList;

class RepositoryStructure
{
  public function __construct(
    public IncludeList $includeList,
    public WhereList $whereList
  ){}
}