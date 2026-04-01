<?php

namespace Websyspro\Entity\Interfaces;

use Websyspro\Entity\Consts\Patterns;

class UsePath
{
  public string $namespace;
  public string $name;
  public string $alias;
  public string $entity;

  public function __construct(
    string $use
  ){
    $this->namespaceParsed( $use );
  }

  private function namespaceParsed(
    string $use
  ): void {
    if( (bool)preg_match( Patterns::PATTERN_NAMESPACE_ALIAS, $use ) === true ){
      $namespaceWithAliasArr = preg_split(
        Patterns::PATTERN_NAMESPACE_ALIAS, $use
      );

      if( empty( $namespaceWithAliasArr ) === false ){
        [ $this->namespace, $this->alias ] = [
          implode( "", array_slice( $namespaceWithAliasArr, 0, -1 )),
          implode( "", array_slice( $namespaceWithAliasArr, -1 ))
        ];
      }
    } else {
      $this->namespace = $use;
    }

    $namespacePaths = preg_split( 
      Patterns::PATTERN_NAMESPACE_BREAKS, $this->namespace
    );

    [ $this->namespace, $this->name ] = [
      join( Patterns::PATTERN_NAMESPACE_SEPARETOR, array_slice( $namespacePaths, 0, -1 )),
      join( Patterns::PATTERN_NAMESPACE_SEPARETOR, array_slice( $namespacePaths, -1 ))
    ];

    $this->entity = implode( 
      Patterns::PATTERN_NAMESPACE_SEPARETOR, [
        $this->namespace, $this->name
      ]
    );
  }
}