<?php

class StringColumn
{
    private ?string $value = null;

    public function __construct(?string $value = null)
    {
        $this->value = $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Magic Methods
    |--------------------------------------------------------------------------
    */

    public function __get(string $name): mixed
    {
        return match ($name) {
            'value' => $this->value,
            'length' => strlen($this->value ?? ''),
            default => null
        };
    }

    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'value' => $this->value = $value !== null
                ? (string)$value
                : null,
            default => null
        };
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | Value Methods
    |--------------------------------------------------------------------------
    */

    public function set(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function get(): ?string
    {
        return $this->value;
    }

    /*
    |--------------------------------------------------------------------------
    | Expression Methods
    |--------------------------------------------------------------------------
    */

    public function equal(string $value): CompareExpression
    {
        return new CompareExpression(
            column: $this,
            operator: '=',
            value: $value
        );
    }

    public function startsWith(string $value): StartsWithExpression
    {
        return new StartsWithExpression(
            source: $this,
            value: $value
        );
    }

    public function contains(string $value): ContainsExpression
    {
        return new ContainsExpression(
            source: $this,
            value: $value
        );
    }

    public function trim(): TrimExpression
    {
        return new TrimExpression($this);
    }

    public function lower(): LowerExpression
    {
        return new LowerExpression($this);
    }

    public function upper(): UpperExpression
    {
        return new UpperExpression($this);
    }
}