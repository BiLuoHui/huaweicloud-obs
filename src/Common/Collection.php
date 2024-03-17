<?php

namespace Bihuohui\HuaweicloudObs\Common;

use ArrayAccess;
use Bihuohui\HuaweicloudObs\Contracts\Arrayable;

class Collection implements ArrayAccess, Arrayable
{
    public function __construct(protected array $payload = [])
    {
    }

    public function offsetExists($offset): bool
    {
        return isset($this->payload[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->payload[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->payload[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->payload[$offset]);
    }

    public function toArray(): array
    {
        return $this->payload;
    }

    public function add($key, $value): static
    {
        if (!array_key_exists($key, $this->payload)) {
            $this->payload[$key] = $value;
        } elseif (is_array($this->payload[$key])) {
            $this->payload[$key][] = $value;
        } else {
            $this->payload[$key] = [$this->payload[$key], $value];
        }

        return $this;
    }

    public function empty(): bool
    {
        return empty($this->payload);
    }
}