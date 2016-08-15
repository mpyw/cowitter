<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Cowitter\HttpException;
use mpyw\Co\Co;
use mpyw\Cowitter\ResponseInterface;

trait UploaderTrait
{
    abstract public function getAsync($endpoint, array $params = [], $return_response_object = false);
    abstract public function postAsync($endpoint, array $params = [], $return_response_object = false);
    abstract public function postMultipartAsync($endpoint, array $params = [], $return_response_object = false);

    protected static function validateChunkSize($value)
    {
        if (false === $value = filter_var($value, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Chunk size must be integer.');
        }
        if ($value < 10000) {
            throw new \LengthException('Chunk size must be no less than 10000 bytes.');
        }
        return $value;
    }

    protected static function getMimeType(\SplFileObject $file)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->buffer($file->fread(1024));
        $file->rewind();
        return $type;
    }

    public function uploadAsync(\SplFileObject $file, $media_category = null, callable $on_progress = null, $chunk_size = 300000)
    {
        $response = (yield $this->uploadStep1($file, $media_category, $chunk_size));
        if (!isset($response->getContent()->processing_info)) {
            yield Co::RETURN_WITH => $response->getContent();
        }
        yield Co::RETURN_WITH => (yield $this->uploadStep2($response, $on_progress));
    }

    protected function uploadStep1(\SplFileObject $file, $media_category = null, $chunk_size = 300000)
    {
        $chunk_size = static::validateChunkSize($chunk_size);
        $info = (yield $this->postAsync('media/upload', [
            'command' => 'INIT',
            'media_type' => static::getMimeType($file),
            'total_bytes' => $file->getSize(),
            'media_category' => $media_category,
        ]));
        $tasks = [];
        for ($i = 0; '' !== $buffer = $file->fread($chunk_size); ++$i) {
            $tasks[] = $this->postMultipartAsync('media/upload', [
                'command' => 'APPEND',
                'media_id' => $info->media_id_string,
                'segment_index' => $i,
                'media' => $buffer,
            ]);
        }
        yield $tasks;
        yield Co::RETURN_WITH => (yield $this->postAsync('media/upload', [
            'command' => 'FINALIZE',
            'media_id' => $info->media_id_string,
        ], true));
    }

    protected function uploadStep2(ResponseInterface $response, callable $on_progress = null)
    {
        $info = $response->getContent();
        $canceled = false;
        while ($info->processing_info->state === 'pending' || $info->processing_info->state === 'in_progress') {
            $percent = isset($info->processing_info->progress_percent)
                ? $info->processing_info->progress_percent
                : null
            ;
            if ($on_progress && (new \ReflectionFunction($on_progress))->isGenerator()) {
                Co::async(function () use ($on_progress, $percent, $response, &$canceled) {
                    if (false === (yield $on_progress($percent, $response))) {
                        $canceled = true;
                    }
                });
            } elseif ($on_progress) {
                if (false === $on_progress($percent, $response)) {
                    yield Co::RETURN_WITH => $info;
                }
            }
            if ($canceled) yield Co::RETURN_WITH => $info;
            yield Co::DELAY => $info->processing_info->check_after_secs;
            if ($canceled) yield Co::RETURN_WITH => $info;
            $response = (yield $this->getAsync('media/upload', [
                'command' => 'STATUS',
                'media_id' => $info->media_id_string,
            ], true));
            $info = $response->getContent();
        }

        if ($info->processing_info->state === 'failed') {
            throw new HttpException(
                isset($info->processing_info->error->message)
                    ? $info->processing_info->error->message
                    : $info->processing_info->error->name,
                $info->processing_info->error->code,
                $response->getHandle(),
                $response
            );
        }

        yield Co::RETURN_WITH => $info;
    }

    public function uploadImageAsync(\SplFileObject $file, callable $on_progress = null, $chunk_size = 300000)
    {
        return $this->uploadAsync($file, 'tweet_image', $on_progress, $chunk_size);
    }

    public function uploadAnimeGifAsync(\SplFileObject $file, callable $on_progress = null, $chunk_size = 300000)
    {
        return $this->uploadAsync($file, 'tweet_gif', $on_progress, $chunk_size);
    }

    public function uploadVideoAsync(\SplFileObject $file, callable $on_progress = null, $chunk_size = 300000)
    {
        return $this->uploadAsync($file, 'tweet_video', $on_progress, $chunk_size);
    }
}
