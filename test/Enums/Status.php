<?php

namespace Websyspro\Test\Enums;

enum Status: int
{
  case EnvioGerentePendente = 1;
  case ContestadoPeloGerente = 2;
  case RevisaoGerentePendente = 3;
  case ContestadoPeloPlanejamento = 4;
  case RevisaoPlanejamentoPendente = 5;
  case Aprovada = 7;
  case EnvioDiretorPendente = 8;
  case RevisaoDiretorPendente = 9;
  case ContestadoPeloDiretor = 10;
}