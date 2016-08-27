<?php

namespace mpyw\Cowitter\Helpers;

use mpyw\Co\CURLException;
use mpyw\Co\CoInterface;
use mpyw\Cowitter\Response;
use mpyw\Cowitter\Helpers\ResponseBodyDecoder;

class ResponseYielder
{
    public static function syncExec($ch)
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
            return static::syncExec($ch);
        }
        return $response;
    }

    public static function asyncExec($ch)
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
            yield CoInterface::RETURN_WITH => static::syncExec($ch);
        }
        yield CoInterface::RETURN_WITH => $response;
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public static function syncExecDecoded($ch, $return_response_object = false)
    {
        $response = static::syncExec($ch);
        $response = ResponseBodyDecoder::getDecodedResponse($response);
        if (!$return_response_object) {
            $response = $response->hasContent() ? $response->getContent() : null;
        }
        return $response;
    }

    public static function asyncExecDecoded($ch, $return_response_object = false)
    {
        $response = (yield static::asyncExec($ch));
        $response = ResponseBodyDecoder::getDecodedResponse($response);
        if (!$return_response_object) {
            $response = $response->hasContent() ? $response->getContent() : null;
        }
        yield CoInterface::RETURN_WITH => $response;
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
