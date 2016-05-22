<?php

namespace Riimu\Braid\Application;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * HttpResponse.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016, Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class HttpResponse
{
    private $response;
    private $omitBody;
    private $chunkSize;

    public function __construct(Response $response)
    {
        $this->response = $response;
        $this->omitBody = false;
        $this->chunkSize = 8192;
    }

    public function setResponseChunkSize($bytes)
    {
        $this->chunkSize = max((int) $bytes, 1);
    }

    public function omitBody($omit = true)
    {
        $this->omitBody = (bool) $omit;
    }

    public function send()
    {
        if (!headers_sent()) {
            $this->sendHeaders();
        }

        if (!$this->omitBody && $this->hasResponseBody()) {
            $this->sendBody();
        }
    }

    private function hasResponseBody()
    {
        return !in_array($this->response->getStatusCode(), [204, 205, 304]);
    }

    private function sendHeaders()
    {
        header(sprintf(
            'HTTP/%s %d %s',
            $this->response->getProtocolVersion(),
            $this->response->getStatusCode(),
            $this->response->getReasonPhrase()
        ));

        foreach ($this->response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value));
            }
        }
    }

    private function sendBody()
    {
        $body = $this->response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        if ($this->response->hasHeader('Content-Length')) {
            $length = (int) current($this->response->getHeader('Content-Length'));
        } else {
            $length = $body->getSize();

            if ($length !== null) {
                $this->response = $this->response->withHeader('Content-Length', (string) $length);
            }
        }

        if ($length === null) {
            while (!$body->eof()) {
                echo $body->read($this->chunkSize);

                if (connection_status() !== CONNECTION_NORMAL) {
                    break;
                }
            }
        } else {
            for ($read = 0; $read < $length && !$body->eof(); $read += strlen($bytes)) {
                echo $bytes = $body->read(min($this->chunkSize, $length - $read));

                if (connection_status() !== CONNECTION_NORMAL) {
                    break;
                }
            }
        }
    }
}
