<?php

namespace Bihuohui\HuaweicloudObs\Common;

use Bihuohui\HuaweicloudObs\Exceptions\ExceptionType;
use Bihuohui\HuaweicloudObs\Exceptions\ObsException;
use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;

class ObsStream implements StreamInterface
{
    use StreamDecoratorTrait;

    private int $readCount = 0;

    public function __construct(protected StreamInterface $stream, protected float $expectedLen)
    {
    }

    public function read(int $length): string
    {
        $string = $this->stream->read($length);
        if ($this->expectedLen !== null) {
            $this->readCount += strlen($string);
            if ($this->stream->eof() && floatval($this->readCount) !== $this->expectedLen) {
                $this->throwObsException($this->expectedLen, $this->readCount);
            }
        }

        return $string;
    }

    public function throwObsException(float $expectedLen, float $receivedLen)
    {
        $e = new ObsException(
            "'premature end of Content-Length delimiter message body (expected:$expectedLen; received:$receivedLen)",
        );
        $e->setType(ExceptionType::SERVER);

        throw $e;
    }

    public function getContents(): string
    {
        $contents = $this->stream->getContents();
        $len = strlen($contents);
        if ($this->expectedLen !== null && floatval($len) !== $this->expectedLen) {
            $this->throwObsException($this->expectedLen, $len);
        }

        return $contents;
    }

    public function getMetadata(?string $key = null)
    {
        // TODO: Implement getMetadata() method.
    }
}