<?php

namespace mpyw\Cowitter\Traits;

trait UploaderTrait
{
    public function uploadFileAsync(\SplFileObject $file, array $params = [])
    {
        yield;
        throw new \BadMethodCallException('Not yet implemented');
    }

    public function uploadLargeFileAsync(\SplFileObject $file, array $params = [], callable $on_progress = null)
    {
        yield;
        throw new \BadMethodCallException('Not yet implemented');
    }

    public function uploadFile(\SplFileObject $file, array $params = [])
    {
        throw new \BadMethodCallException('Not yet implemented');
    }

    public function uploadLargeFile(\SplFileObject $file, array $params = [], callable $on_progress = null)
    {
        throw new \BadMethodCallException('Not yet implemented');
    }
}
