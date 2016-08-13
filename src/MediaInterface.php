<?php

namespace mpyw\Cowitter;

interface MediaInterface
{
    public function getContentType();
    public function getBinaryString();
    public function getDataUri();
    public function save($path);
}
