<?php

namespace mpyw\Cowitter;

class Media implements MediaInterface
{
    protected $contentType;
    protected $data;

    public function __construct($content_type, $data)
    {
        $this->contentType = $content_type;
        $this->data = $data;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getBinaryString()
    {
        return $this->data;
    }

    public function getDataUri()
    {
        return 'data:' . $this->contentType . ';base64,' . base64_encode($this->data);
    }

    public function save($path)
    {
        if (@file_put_contents($path, $this->data) === false) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }
    }
}
