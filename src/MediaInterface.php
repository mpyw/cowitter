<?php

namespace mpyw\Cowitter;

interface MediaInterface
{
    /**
     * Return Content-Type.
     * @return string
     */
    public function getContentType();

    /**
     * Return binary data.
     * @return string
     */
    public function getBinaryString();

    /**
     * Return data URI, such as "data:image/png;base64,XXXXXXXXXXX"
     * @return string
     */
    public function getDataUri();

    /**
     * Save as specified path using file_put_contents().
     * @param string $path
     * @throws \RuntimeException
     */
    public function save($path);
}
