<?php

namespace mpyw\Cowitter;

interface ResponseInterface
{
    /**
     * Return HTTP protocol version string.
     * @return string
     */
    public function getVersion();

    /**
     * Return HTTP status code.
     * @return int
     */
    public function getStatusCode();

    /**
     * Return HTTP reason pharse.
     * @return string
     */
    public function getReasonPhrase();

    /**
     * Return all HTTP header groups.
     * @return array
     */
    public function getHeaders();

    /**
     * Return HTTP header group specified by $name.
     * @param string $name Header name.
     * @return array
     */
    public function getHeader($name);

    /**
     * Return HTTP header group concatinated by $delimiter.
     * @param string $name Header name.
     * @param string $delimiter
     * @return string
     */
    public function getHeaderLine($name, $delimiter = ', ');

    /**
     * Return cURL handle used for sending request.
     * @return resource
     */
    public function getHandle();

    /**
     * Return raw body.
     * @return string
     */
    public function getRawContent();

    /**
     * Return decoded body if available.
     * @return \stdClass|array
     * @throws \UnderflowException
     */
    public function getContent();

    /**
     * Is decoded body available?
     * @return bool
     */
    public function hasContent();
}
