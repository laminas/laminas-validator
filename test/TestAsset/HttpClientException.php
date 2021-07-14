<?php

namespace LaminasTest\Validator\TestAsset;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

class HttpClientException extends Exception implements ClientExceptionInterface
{
}
