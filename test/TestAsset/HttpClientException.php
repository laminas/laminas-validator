<?php

declare(strict_types=1);

namespace LaminasTest\Validator\TestAsset;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

class HttpClientException extends Exception implements ClientExceptionInterface
{
}
