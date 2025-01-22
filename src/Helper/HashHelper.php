<?php

declare(strict_types=1);

namespace App\Helper;

class HashHelper
{
    static public function getHash(string $message): string
    {
        $microtime = microtime(true);
        return hash('sha256', "$message $microtime");
    }
}
