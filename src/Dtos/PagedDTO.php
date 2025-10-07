<?php

namespace Websyspro\Entity\Dtos;

class PagedDTO
{
  public function __construct(
    public int $page,
    public int $rowsPerPage
  ){}
}