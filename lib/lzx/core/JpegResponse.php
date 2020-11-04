<?php

declare(strict_types=1);

namespace lzx\core;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\InjectContentTypeTrait;
use Laminas\Diactoros\Stream;

class JpegResponse extends Response
{
    use InjectContentTypeTrait;

    public function __construct(string $image, int $status = 200, array $headers = [])
    {
        parent::__construct(
            $this->createBody($image),
            $status,
            $this->injectContentType('image/jpeg', $headers)
        );
    }

    private function createBody($image): StreamInterface
    {
        if ($image instanceof StreamInterface) {
            return $image;
        }

        if (!is_string($image)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid content (%s) provided to %s',
                (is_object($image) ? get_class($image) : gettype($image)),
                __CLASS__
            ));
        }

        $body = new Stream('php://temp', 'wb+');
        $body->write($image);
        $body->rewind();
        return $body;
    }
}
