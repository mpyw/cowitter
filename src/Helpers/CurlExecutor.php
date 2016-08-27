<?php

namespace mpyw\Cowitter\Helpers;

use mpyw\Co\CURLException;
use mpyw\Co\CoInterface;
use mpyw\Cowitter\Response;
use mpyw\Cowitter\Helpers\ResponseBodyDecoder;

class CurlExecutor
{
    public static function exec($ch)
    {
        if (false === $buffer = curl_exec($ch)) {
            throw new CURLException(curl_error($ch), curl_errno($ch), $ch);
        }
        $response = new Response($buffer, $ch);
        if ($response->getStatusCode() >= 300 &&
            $response->getStatusCode() < 400 &&
            false !== $url = curl_getinfo($ch, CURLINFO_REDIRECT_URL)) {
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPGET => true,
                CURLOPT_REFERER => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
            ]);
            return static::exec($ch);
        }
        return $response;
    }

    public static function execAsync($ch)
    {
        $response = new Response((yield $ch), $ch);
        if ($response->getStatusCode() >= 300 &&
            $response->getStatusCode() < 400 &&
            false !== $url = curl_getinfo($ch, CURLINFO_REDIRECT_URL)) {
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPGET => true,
                CURLOPT_REFERER => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
            ]);
            yield CoInterface::RETURN_WITH => static::exec($ch);
        }
        yield CoInterface::RETURN_WITH => $response;
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public static function execDecoded($ch, $return_response_object = false)
    {
        $response = static::exec($ch);
        $response = ResponseBodyDecoder::getDecodedResponse($response);
        if (!$return_response_object) {
            $response = $response->hasContent() ? $response->getContent() : null;
        }
        return $response;
    }

    public static function execDecodedAsync($ch, $return_response_object = false)
    {
        $response = (yield static::execAsync($ch));
        $response = ResponseBodyDecoder::getDecodedResponse($response);
        if (!$return_response_object) {
            $response = $response->hasContent() ? $response->getContent() : null;
        }
        yield CoInterface::RETURN_WITH => $response;
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
