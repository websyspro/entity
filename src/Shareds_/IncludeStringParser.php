<?php

namespace Websyspro\Entity\Shareds_;

class IncludeStringParser
{
    private string $input;
    private int $pos = 0;
    private int $length;

    private ?string $rootEntity = null;

    public function parse(string $input, string $rootEntity): array
    {
        $this->input = $this->normalize($input);
        $this->length = strlen($this->input);
        $this->pos = 0;

        $this->rootEntity = $rootEntity;

        // pula fn(...) =>
        $arrowPos = strpos($this->input, '=>');
        if ($arrowPos !== false) {
            $this->pos = $arrowPos + 2;
        }

        $this->skipWhitespace();

        return $this->parseChain($this->rootEntity);
    }

    private function normalize(string $input): string
    {
        return preg_replace('/\s+/', ' ', trim($input));
    }

    /**
     * CORE RECURSIVO
     */
    private function parseChain(string $currentEntity): array
    {
        $node = $this->parseProperty($currentEntity);

        while (true) {
            $this->skipWhitespace();

            if (!$this->match('->')) {
                break;
            }

            $this->skipWhitespace();
            $method = $this->parseIdentifier();

            if ($method === 'where') {
                $args = $this->parseArguments();
                $args = $this->stripArrowFunction($args);

                $node['where'][] = $args;
                continue;
            }

            if ($method === 'include') {
                $args = $this->parseArguments();

                // extrai entidade do include (fn(Type $i) => ...)
                $childEntity = $this->extractEntityFromArrowFunction($args);

                // fallback seguro
                if (!$childEntity) {
                    $childEntity = $currentEntity;
                }

                $node['includes'][] = $this->parse($args, $childEntity);

                continue;
            }
        }

        return $node;
    }

    /**
     * NODE PRINCIPAL
     */
    private function parseProperty(string $entity): array
    {
        $this->skipWhitespace();

        $this->consume('$');
        $this->skipWhitespace();

        $this->parseIdentifier(); // variável ($i)

        $this->skipWhitespace();
        $this->consume('->');

        $this->skipWhitespace();
        $relation = $this->parseIdentifier();

        return [
            'type' => 'relation',
            'entity' => $entity,          // 🔥 FIX PRINCIPAL
            'relation' => $relation,
            'where' => [],
            'includes' => []
        ];
    }

    /**
     * Remove fn(...) => deixando só expressão
     */
    private function stripArrowFunction(string $input): string
    {
        $pos = strpos($input, '=>');

        if ($pos !== false) {
            return trim(substr($input, $pos + 2));
        }

        return trim($input);
    }

    /**
     * Extract entity from fn(Type $i)
     */
    private function extractEntityFromArrowFunction(string $input): ?string
    {
        if (preg_match('/fn\s*\(\s*([a-zA-Z0-9_\\\\]+)\s+\$/', $input, $match)) {
            return $match[1];
        }

        return null;
    }

    /**
     * ARGUMENT PARSER
     */
    private function parseArguments(): string
    {
        $this->consume('(');

        $depth = 1;
        $start = $this->pos;

        while ($this->pos < $this->length && $depth > 0) {
            if ($this->input[$this->pos] === '(') $depth++;
            if ($this->input[$this->pos] === ')') $depth--;
            $this->pos++;
        }

        return substr($this->input, $start, $this->pos - $start - 1);
    }

    /**
     * IDENTIFIER
     */
    private function parseIdentifier(): string
    {
        $this->skipWhitespace();

        $start = $this->pos;

        while (
            $this->pos < $this->length &&
            preg_match('/[a-zA-Z0-9_]/', $this->input[$this->pos])
        ) {
            $this->pos++;
        }

        return substr($this->input, $start, $this->pos - $start);
    }

    /**
     * WHITESPACE
     */
    private function skipWhitespace(): void
    {
        while (
            $this->pos < $this->length &&
            ctype_space($this->input[$this->pos])
        ) {
            $this->pos++;
        }
    }

    /**
     * MATCH TOKEN
     */
    private function match(string $value): bool
    {
        $this->skipWhitespace();

        if (substr($this->input, $this->pos, strlen($value)) === $value) {
            $this->pos += strlen($value);
            return true;
        }

        return false;
    }

    /**
     * CONSUME TOKEN
     */
    private function consume(string $value): void
    {
        if (!$this->match($value)) {
            throw new \Exception("Expected '{$value}' at position {$this->pos}");
        }
    }
}