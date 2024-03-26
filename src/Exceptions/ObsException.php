<?php

namespace Bihuohui\HuaweicloudObs\Exceptions;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use RuntimeException;

class ObsException extends RuntimeException
{
    private ?Response $response;

    private ?Request $request;

    private string $requestId;

    private ExceptionType $type;

    public function getType(): ExceptionType
    {
        return $this->type;
    }

    public function setType(ExceptionType $type): void
    {
        $this->type = $type;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setCode(string|int $code): void
    {
        $this->code = $code;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function statusCode(): int
    {
        return $this->response ? $this->response->getStatusCode() : -1;
    }
}