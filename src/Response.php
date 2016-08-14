<?php

namespace mpyw\Cowitter;

use mpyw\Cowitter\Helpers\ResponseBodyNormalizer;

class Response implements ResponseInterface
{
    protected $version;
    protected $statusCode;
    protected $reasonPhrase;
    protected $headers = [];
    protected $handle;
    protected $rawContent;
    protected $content;

    public function __construct($buffer, $ch)
    {
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header_buffer = substr($buffer, 0, $header_size - 4);
        $body_buffer = substr($buffer, $header_size);
        $header_buffer = current(array_slice(explode("\r\n\r\n", $header_buffer), -1));
        $lines = explode("\r\n", $header_buffer);
        if (!preg_match('@\AHTTP/([\d.]+)\s+(\d{3})\s+(.+)\z@i', array_shift($lines), $matches)) {
            throw new \UnexpectedValueException('Invalid response line.');
        }
        $this->version = $matches[1];
        $this->statusCode = (int)$matches[2];
        $this->reasonPhrase = $matches[3];
        foreach ($lines as $line) {
            list($key, $value) = explode(':', $line, 2) + [1 => ''];
            $this->headers[strtolower(trim($key))][] = trim($value);
        }
        $this->handle = $ch;
        $this->rawContent = $body_buffer;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($name)
    {
        $name = strtolower($name);
        return isset($this->headers[$name]) ? $this->headers[$name] : [];
    }

    public function getHeaderLine($name, $delimiter = ', ')
    {
        $name = strtolower($name);
        return implode($delimiter, $this->getHeader($name));
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function getRawContent()
    {
        return $this->rawContent;
    }

    public function getContent()
    {
        if ($this->content === null) {
            throw new \UnderflowException('Decoded content has not created yet.');
        }
        return $this->content;
    }

    public function hasContent()
    {
        return $this->content !== null;
    }

    public function withDecodedContent($content)
    {
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }
}
