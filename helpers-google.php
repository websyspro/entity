<?php

public function analysisLexicalOrigins(): void 
  {
    if (isset($this->cacheClass) === false) {
      $scopeClass = $this->reflectionFunction->getClosureScopeClass();
      $this->cacheClass = $scopeClass ? $scopeClass->getShortName() : 'Global';
    }
    
    if (isset($this->cacheMethod) === false) {
      // Retorna o nome da função/método atual onde a closure reside
      $this->cacheMethod = $this->reflectionFunction->getShortName(); 
    }
    
    calcTimer("[PRE-CACHE] Search Class/Method via Reflection Native");
  }

    public function analysisLexicalTokens(): void 
  {
    $this->reflectionFunction = new ReflectionFunction($this->closure);
    calcTimer("[PRE-CACHE] Create ReflectionFunction ExpressionWhere::Tokens");
    
    if ($this->reflectionFunction instanceof ReflectionFunction) {
      $fileName = $this->reflectionFunction->getFileName();
      $startLine = $this->reflectionFunction->getStartLine() - 1; // Ajusta índice zero
      $endLine = $this->reflectionFunction->getEndLine();
      
      $this->tokens = [];
      
      // Lê APENAS as linhas da closure, ignorando o resto do arquivo
      $file = new \SplFileObject($fileName);
      $file->seek($startLine);
      
      while ($file->key() < $endLine && !$file->eof()) {
        $token = $file->current();
        
        // Remove comentários de linha cheia ou inline
        $strPos = strpos($token, '//');
        $this->tokens[] = $strPos !== false ? substr($token, 0, $strPos) : $token;
        
        $file->next();
      }
      
      calcTimer("[PRE-CACHE] Ler apenas linhas necessárias da ExpressionWhere::Tokens");
    }
  }


private function getUseStatements(string $fileName): array
{
    $useStatements = [];
    $file = new \SplFileObject($fileName);
    $headerCode = "";

    // Lê linha por linha APENAS o cabeçalho do arquivo
    while (!$file->eof()) {
        $line = $file->fgets();
        $headerCode .= $line;

        // Se encontrou a declaração da classe, para a leitura do disco na hora!
        if (str_contains($line, 'class ') || str_contains($line, 'abstract class ')) {
            break;
        }
    }

    // Tokeniza nativamente apenas o cabeçalho extraído
    $tokens = \PhpToken::tokenize($headerCode);
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        // Procura pela palavra-chave T_USE (mas ignora se for use de closure/arrow function)
        if ($tokens[$i]->id === T_USE) {
            $fullClass = "";
            $alias = "";
            $i++; // Pula o token 'use'

            // Coleta o Namespace + Nome da Classe importada
            while ($i < $count && $tokens[$i]->id !== T_AS && $tokens[$i]->text !== ';' && $tokens[$i]->text !== ',') {
                if (!$tokens[$i]->isWhitespace()) {
                    $fullClass .= $tokens[$i]->text;
                }
                $i++;
            }

            // Se houver um "as Alias"
            if ($i < $count && $tokens[$i]->id === T_AS) {
                $i++; // Pula o 'as'
                while ($i < $count && $tokens[$i]->text !== ';' && $tokens[$i]->text !== ',') {
                    if (!$tokens[$i]->isWhitespace()) {
                        $alias .= $tokens[$i]->text;
                    }
                    $i++;
                }
            }

            // Se não definiu alias, o alias padrão é o próprio nome curto da classe
            if (empty($alias)) {
                $parts = explode('\\', $fullClass);
                $alias = end($parts);
            }

            $useStatements[$alias] = ltrim($fullClass, '\\');
        }
    }

    return $useStatements;
}


// Na sua classe Repository (Segunda requisição em diante)
$hash = md5($signature);

// apcu_fetch é centenas de vezes mais rápido que file_exists + require
if (apcu_exists($hash)) { 
    return apcu_fetch($hash); 
}

// Primeira vez: processa os tokens e depois salva na RAM
$expression = todoOProcessoPesado();
apcu_store($hash, $expression);


$cacheFile = BASEDIR_APP . DIRECTORY_SEPARATOR . "Cache" . DIRECTORY_SEPARATOR . "ast-{$hash}.php";

// O '@' silencia o aviso caso o arquivo ainda não exista no disco
$cachedData = @include $cacheFile;

if ($cachedData !== false && is_array($cachedData)) {
    // 🟢 SEGUNDA VEZ EM DIANTE: O OPcache trouxe direto da memória RAM!
    $expressionWhere = new ExpressionWhere($ref, $hash, $cachedData['tokens'], $cachedData['uses']);
    $expressionWhere->updateDynamicScopes($ref->getStaticVariables());
    
    $this->applyWhereExpression($expressionWhere);
    return $this;
}

// 🔴 PRIMEIRA VEZ: Se o include retornou false, significa que o cache não existe.
// Execute aqui todo o seu processo pesado de ler o arquivo original e gerar o cache...
