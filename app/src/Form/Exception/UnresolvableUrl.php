<?php

declare(strict_types=1);

namespace Form\Exception;

use RuntimeException;

class UnresolvableUrl extends RuntimeException
{
    public function __construct(string $url, int $statusCode)
    {
        parent::__construct(sprintf(
            'Could not resolve %1$s to a final URL. Status-Code was %2$s',
            $url,
            $statusCode
        ), 1);
    }
}
