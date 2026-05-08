<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Commons\Collection;
use Websyspro\Entity\Shareds\WhereList;

class RepositoryStructure
{
  public function __construct(
    public Collection $includes,
    public WhereList $whereList
  ){}
}