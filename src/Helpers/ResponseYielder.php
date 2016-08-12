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
            '' !== $url = $response->getHeaderLine('Location')) {
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
            '' !== $url = $response->getHeaderLine('Location')) {
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPGET => true,
                CURLOPT_REFERER => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
            ]);
            yield CoInterface::RETURN_WITH => static::syncExec($ch);
        }
        yield CoInterface::RETURN_WITH => $response;
    }

    public static function syncExecDecoded($ch)
    {
        $response = static::syncExec($ch);
        return ResponseBodyDecoder::getDecodedResponse($response, $ch);
    }

    public static function asyncExecDecoded($ch)
    {
        $response = (yield static::asyncExec($ch));
        yield CoInterface::RETURN_WITH => ResponseBodyDecoder::getDecodedResponse($response, $ch);
    }
}
