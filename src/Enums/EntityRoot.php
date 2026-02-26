<?php

namespace Websyspro\Entity\Enums;

enum EntityRoot {
  /** Entidade é raiz - pode ser usada como base para a consulta */
  case Yes;
  
  /** Entidade não é raiz - possui relacionamentos oneToMany no histórico */
  case No;
}