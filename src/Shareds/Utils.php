<?php

namespace Websyspro\Entity\Shareds;

use Closure;
use function array_slice;

class Utils
{
  public function inc(
    int $number
  ): int {
    return ++$number;
  }

  public function dec(
    int $number,
    int $extraDec = 0
  ): int {
    return ( --$number ) - $extraDec;
  }

  public function slice(
    array $items,
    int $offset,
    int|null $length = null
  ): array {
    return array_slice( $items, $offset, $length );
  }  

  public function mapper(
    array $items,
    Closure $closure
  ): array {
    return array_map( $closure, $items, array_keys( $items ));
  }

  public function filter(
    array $items,
    Closure $closure
  ): array {
    return array_values( array_filter( $items, $closure ));
  }  
}