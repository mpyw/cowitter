<?php

namespace mpyw\Cowitter\Traits;

use mpyw\Cowitter\HttpException;
use mpyw\Co\Co;
use mpyw\Co\CURLException;
use mpyw\Cowitter\ResponseInterface;

trait UploaderTrait
{
    abstract public function getAsync($endpoint, array $params = [], $return_response_object = false);
    abstract public function postAsync($endpoint, array $params = [], $return_response_object = false);
    abstract public function postMultipartAsync($endpoint, array $params = [], $return_response_object = false);
    abstract public function withOptions(array $options);

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

    public function uploadAsync(\SplFileObject $file, $media_category = null, callable $on_uploading = null, callable $on_processing = null, $chunk_size = 300000)
    {
        $response = (yield $this->uploadStep1($file, $media_category, $chunk_size, $on_uploading));
        if (!$response instanceof ResponseInterface) {
            yield Co::RETURN_WITH => $response;
        }
        if (!isset($response->getContent()->processing_info)) {
            yield Co::RETURN_WITH => $response->getContent();
        }
        yield Co::RETURN_WITH => (yield $this->uploadStep2($response, $on_processing));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    protected function uploadStep1(\SplFileObject $file, $media_category = null, $chunk_size = 300000, callable $on_uploading = null)
    {
        $chunk_size = static::validateChunkSize($chunk_size);
        $info = (yield $this->postAsync('media/upload', [
            'command' => 'INIT',
            'media_type' => static::getMimeType($file),
            'total_bytes' => $file->getSize(),
            'media_category' => $media_category,
        ]));
        try {
            yield $this->uploadBuffers($file, $info, $chunk_size, $on_uploading);
        } catch (CURLException $e) {
            if ($e->getCode() === CURLE_ABORTED_BY_CALLBACK) {
                yield Co::RETURN_WITH => $info;
            }
            // @codeCoverageIgnoreStart
            throw $e;
            // @codeCoverageIgnoreEnd
        }
        yield Co::RETURN_WITH => (yield $this->postAsync('media/upload', [
            'command' => 'FINALIZE',
            'media_id' => $info->media_id_string,
        ], true));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    protected function uploadBuffers(\SplFileObject $file, \stdClass $info, $chunk_size, callable $on_uploading = null)
    {
        $tasks = [];
        $whole_uploaded = 0;
        $first = true;
        $canceled = false;
        for ($i = 0; '' !== $buffer = $file->fread($chunk_size); ++$i) {
            $client = $on_uploading ? $this->withOptions([
                CURLOPT_NOPROGRESS => false,
                CURLOPT_PROGRESSFUNCTION => static::getProgressHandler($file, $on_uploading, $whole_uploaded, $first, $canceled),
            ]) : $this;
            $tasks[] = $client->postMultipartAsync('media/upload', [
                'command' => 'APPEND',
                'media_id' => $info->media_id_string,
                'segment_index' => $i,
                'media' => $buffer,
            ]);
        }
        yield $tasks;
    }

    protected static function getProgressHandler(\SplFileObject $file, callable $on_uploading, &$whole_uploaded, &$first, &$canceled)
    {
        // NOTE: File size doesn't include other parameters' size.
        //       It is calculated for approximate usage.
        return function ($ch, $dl_total, $dl_now, $up_total, $up_now) use ($file, $on_uploading, &$whole_uploaded, &$first, &$canceled) {
            static $previous_up_now = 0;
            if ($canceled) {
                return 1;
            }
            if ($dl_now !== 0 || ($up_now === $previous_up_now && !$first)) {
                return 0;
            }
            $whole_uploaded_percent_before = (int)(min(100, (int)(($whole_uploaded / $file->getSize()) * 100)) / 5) * 5;
            $whole_uploaded += $up_now - $previous_up_now;
            $previous_up_now = $up_now;
            $whole_uploaded_percent_after  = (int)(min(100, (int)(($whole_uploaded / $file->getSize()) * 100)) / 5) * 5;
            if ($whole_uploaded_percent_before === $whole_uploaded_percent_after && !$first) {
                return 0;
            }
            $first = false;
            if ((new \ReflectionFunction($on_uploading))->isGenerator()) {
                Co::async(function () use ($on_uploading, $whole_uploaded_percent_after, &$canceled) {
                    if (false === (yield $on_uploading($whole_uploaded_percent_after))) {
                        $canceled = true;
                    }
                });
            }
            return (int)(false === $on_uploading($whole_uploaded_percent_after));
        };
    }

    protected function uploadStep2(ResponseInterface $response, callable $on_processing = null)
    {
        $info = $response->getContent();
        $canceled = false;
        $previous_percent = 0;
        while ($info->processing_info->state === 'pending' || $info->processing_info->state === 'in_progress') {
            $percent = isset($info->processing_info->progress_percent)
                ? $info->processing_info->progress_percent
                : $previous_percent
            ;
            $previous_percent = $percent;
            if ($on_processing) {
                if ((new \ReflectionFunction($on_processing))->isGenerator()) {
                    Co::async(function () use ($on_processing, $percent, $response, &$canceled) {
                        if (false === (yield $on_processing($percent, $response))) {
                            $canceled = true;
                        }
                    });
                } elseif (false === $on_processing($percent, $response)) {
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
            $message = isset($info->processing_info->error->message)
                ? $info->processing_info->error->message
                : $info->processing_info->error->name;
            throw new HttpException(
                $message,
                $info->processing_info->error->code,
                $response
            );
        }

        yield Co::RETURN_WITH => $info;
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function uploadImageAsync(\SplFileObject $file, callable $on_uploading = null, callable $on_processing = null, $chunk_size = 300000)
    {
        yield Co::RETURN_WITH => ($this->uploadAsync($file, 'tweet_image', $on_uploading, $on_processing, $chunk_size));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function uploadAnimeGifAsync(\SplFileObject $file, callable $on_uploading = null, callable $on_processing = null, $chunk_size = 300000)
    {
        yield Co::RETURN_WITH => ($this->uploadAsync($file, 'tweet_gif', $on_uploading, $on_processing, $chunk_size));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function uploadVideoAsync(\SplFileObject $file, callable $on_uploading = null, callable $on_processing = null, $chunk_size = 300000)
    {
        yield Co::RETURN_WITH => ($this->uploadAsync($file, 'tweet_video', $on_uploading, $on_processing, $chunk_size));
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
