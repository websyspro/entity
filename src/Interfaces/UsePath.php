<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Consts\Patterns;
use Websyspro\Commons\Util;

class UsePath
{
  public string $namespace;
  public string $entity;
  public string $alias;
  public string $path;

  public function __construct(
    string $use
  ){
    $this->namespaceParsed( $use );
  }

  private function namespaceParsed(
    string $use
  ): void {
    if( Util::match( Patterns::PATTERN_NAMESPACE_ALIAS, $use )){
      $namespaceWithAliasArr = Util::split(
        Patterns::PATTERN_NAMESPACE_ALIAS, $use
      );

      if( $namespaceWithAliasArr->exist()){
        [ $this->namespace, $this->alias ] = [
          $namespaceWithAliasArr->slice( 0, -1 )->toString(),
          $namespaceWithAliasArr->slice( -1 )->toString()
        ];
      }
    } else {
      $this->namespace = $use;
    }

    $namespacePaths = Util::split( 
      Patterns::PATTERN_NAMESPACE_BREAKS, $this->namespace
    );

    [ $this->namespace, $this->entity ] = [
      $namespacePaths->slice( 0, -1 )->join( Patterns::PATTERN_NAMESPACE_SEPARETOR ),
      $namespacePaths->slice( -1 )->join( Patterns::PATTERN_NAMESPACE_SEPARETOR ),
    ];

    $this->path = Util::join( Patterns::PATTERN_NAMESPACE_SEPARETOR, [ $this->namespace, $this->entity ]);
  }
}