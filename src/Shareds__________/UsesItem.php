<?php

namespace Websyspro\Entity\Shareds;

use Websyspro\Commons\Util;

class UsesItem
{
  public string $alias;
  public string $path;
  public function __construct(
    public string $use
  ){
    $this->startups();
    $this->startupsClear();
  }

  public function getEntityStructure(
  ): EntityStructure|null {
    return ClosureUtil::getEntityStructure(
      $this->path
    );
  }

  private function isAlias(
  ): bool {
    return Util::split( "#\s*as\s*#", $this->use )->count() >= 2;
  }

  private function getAlias(
  ): string {
    return Util::replace( "#^.*as\s*#", $this->use );
  }

  private function getPathWithAlias(
  ): string {
    return Util::replace( "#\s*as.*$#", $this->use );
  }  

  private function startups(
  ): void {
    if( $this->isAlias()){
      $this->alias = $this->getAlias();
      $this->path = $this->getPathWithAlias();
    } else {
      $useList = Util::split( "#\\\#", $this->use );
      [ $this->path, $this->alias ] = [
        $useList->join( "\\" ), $useList->slice( 
          $useList->count() - 1, 1 
        )->join( "\\" )
      ];
    }
  }
  
  private function startupsClear(
  ): void {
    unset( $this->use );
  }
}