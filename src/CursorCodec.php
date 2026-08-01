<?php

declare(strict_types=1);

namespace Pam\Native\LaravelSync;

use InvalidArgumentException;

final readonly class CursorCodec
{
    public function __construct(private string $key)
    {
        if ($key === '') {
            throw new InvalidArgumentException('The sync cursor key cannot be empty.');
        }
    }

    public function encode(int $sequence): string
    {
        $payload = $this->base64Url((string) max(0, $sequence));
        return $payload.'.'.$this->base64Url(hash_hmac('sha256', $payload, $this->key, true));
    }

    public function decode(?string $cursor): int
    {
        if ($cursor === null || $cursor === '') return 0;
        $parts = explode('.', $cursor, 2);
        if (count($parts) !== 2 || !hash_equals($this->base64Url(hash_hmac('sha256', $parts[0], $this->key, true)), $parts[1])) {
            throw new InvalidArgumentException('Invalid or expired sync cursor.');
        }
        $decoded = base64_decode(strtr($parts[0], '-_', '+/'), true);
        if ($decoded === false || preg_match('/^(0|[1-9][0-9]*)$/D', $decoded) !== 1) throw new InvalidArgumentException('Invalid sync cursor payload.');
        return (int) $decoded;
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
