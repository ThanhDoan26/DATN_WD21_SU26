<?php

namespace App\Hashing;

use Illuminate\Hashing\BcryptHasher;

class LegacyBcryptHasher extends BcryptHasher
{
    /**
     * Check the given plain value against a hash.
     */
    public function check($value, $hashedValue, array $options = []): bool
    {
        if (empty($hashedValue)) {
            return false;
        }

        if ($this->isBcryptHash($hashedValue)) {
            return parent::check($value, $hashedValue, $options);
        }

        return $this->checkLegacyHash($value, $hashedValue);
    }

    /**
     * Determine if the hash needs to be rehashed.
     */
    public function needsRehash($hashedValue, array $options = []): bool
    {
        if (! $this->isBcryptHash($hashedValue)) {
            return true;
        }

        return parent::needsRehash($hashedValue, $options);
    }

    protected function isBcryptHash(string $hashedValue): bool
    {
        return str_starts_with($hashedValue, '$2');
    }

    protected function checkLegacyHash(string $value, string $hashedValue): bool
    {
        if ($hashedValue === $value) {
            return true;
        }

        if (preg_match('/^[a-f0-9]{32}$/i', $hashedValue)) {
            return md5($value) === $hashedValue || hash('md5', $value) === $hashedValue;
        }

        if (preg_match('/^[a-f0-9]{40}$/i', $hashedValue)) {
            return sha1($value) === $hashedValue || hash('sha1', $value) === $hashedValue;
        }

        if (preg_match('/^[a-f0-9]{64}$/i', $hashedValue)) {
            return hash('sha256', $value) === $hashedValue;
        }

        return false;
    }
}
