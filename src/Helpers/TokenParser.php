<?php

namespace mpyw\Cowitter\Helpers;

use mpyw\Cowitter\ResponseInterface;
use mpyw\Cowitter\HttpException;

class TokenParser
{
    public static function parseAuthenticityToken(ResponseInterface $response) {
        static $pattern = '@<input name="authenticity_token" type="hidden" value="([^"]++)">@';
        if (!preg_match($pattern, $response->getRawContent(), $matches)) {
            throw new HttpException(
                'Failed to get authenticity_token.',
                -1,
                $response
            );
        }
        return $matches[1];
    }

    public static function parseVerifier(ResponseInterface $response) {
        static $pattern = '@<code>([^<]++)</code>@';
        if (!preg_match($pattern, $response->getRawContent(), $matches)) {
            throw new HttpException(
                'Wrong username or password. Otherwise, you may have to verify your email address.',
                -1,
                $response
            );
        }
        return $matches[1];
    }
}
