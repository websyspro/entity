<?php

namespace Websyspro\Entity\Consts;

class Patterns
{ 
  public const string PATTERN_REMOVE_COMMENT_LINE = "#^.*//#";
  public const string PATTERN_TOKEN = "#'[^']*'|\"[^\"]*\"|\\S+#";
  public const array  PATTERN_HYDRATE_BODY = [
    [ "#/\*.*?\*/#",
      "#\r#",
      "#\n\s*#",
      "#^.*\\{.*return\s*#",
      "#\s*;\s*\\}\s*#",
      "#^[^(]*(fn|function)\s*\(#",
      "#\s*\);\s*$#",
      "#^.*?\)\s*=>\s*#s",
      "#\\[\s*#s",
      "#\s*\\]#s",
      "#,\s*#s",
      "#\"#s",
      "#\s{2,}#",
      "#&&#",
      "#\|\|#",
      "#(!==|!=)#",
      "#(===|==|=)#",
      "#true#",
      "#false#"
      ], [ 
      "",     // Remove /* ... */
      "",     // Remove carriage return
      " ",    // Remove quebras de linha
      "",     // Remove abertura de função
      "",     // Remove fechamento de função
      "fn(",  // Normaliza declaração de função
      "",     // Remove fechamento de parênteses
      "",     // Remove arrow function
      "(",    // Converte colchetes em parênteses
      ")",    // Converte colchetes em parênteses
      ",",    // Normaliza vírgulas
      "'",    // Converte aspas duplas em simples
      " ",     // Remove espaços em brancos
      "And",  // Converte && em And
      "Or",   // Converte || em Or
      "<>",   // Normaliza operador diferente
      "=",    // Normaliza operador igual
      "1",    // Converte true em 1
      "0"     // Converte false em 0
    ]
  ];

  public const string PATTERN_NAMESPACE_ALIAS = "#\s+as\s+#";

  public const array  PATTERN_NAMESPACE_HYDRATE = [ "#^use\s*#", "#;\s*$#" ];
  public const string PATTERN_NAMESPACE_BREAKS = "\\";
  public const string PATTERN_NAMESPACE_WHERES = "#^.*use\s*#";
}