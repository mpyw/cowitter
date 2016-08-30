<?php

use mpyw\Cowitter\Helpers\CredentialNormalizer;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class CredentialNormalizerTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;
    private static $CredentialNormalizer;

    public function _before()
    {
        self::$CredentialNormalizer = Proxy::get(CredentialNormalizer::class);
    }

    public function testSnake()
    {
        $this->assertEquals('foo_bar_baz', self::$CredentialNormalizer::snake('FooBarBaz'));
        $this->assertEquals('foo_bar_baz', self::$CredentialNormalizer::snake('fooBarBaz'));
        $this->assertEquals('01foo02bar_yay03baz04', self::$CredentialNormalizer::snake('01Foo02BarYay03Baz04'));
        $this->assertEquals('01foo02bar_yay03baz04', self::$CredentialNormalizer::snake('01foo02barYay03baz04'));
    }

    public function testNormalizeCredentialParamName()
    {
        $valids = [
            'consumer_key' => [
                '0',
                'consumer_key',
                'consumerKey',
                'CONSUMER KEY',
                'oauth_consumer_key',
                'ck',
            ],
            'consumer_secret' => [
                '1',
                'consumer_secret',
                'consumerSecret',
                'CONSUMER SECRET',
                'oauth_consumer_secret',
                'cs',
            ],
            'token' => [
                '2',
                'token',
                'oauth_token',
                'oauthToken',
                'OAUTH TOKEN',
                'request_token',
                'access_token',
                'ot',
                'at',
                'rt',
            ],
            'token_secret' => [
                '3',
                'token_secret',
                'oauth_token_secret',
                'oauthTokenSecret',
                'OAUTH TOKEN SECRET',
                'request_token_secret',
                'access_token_secret',
                'request_secret',
                'access_secret',
                'ots',
                'ats',
                'rts',
                'os',
                'as',
                'rs',
            ],
        ];
        $invalids = [
            'ToKeN',
            'secret',
            'key',
            'OAUTH      TOKEN',
        ];
        foreach($valids as $expected => $values) {
            foreach ($values as $value) {
                $this->specify("'$value' is recognized as '$expected'",
                function () use ($expected, $value){
                    $this->assertEquals($expected, CredentialNormalizer::normalizeCredentialParamName($value));
                });
            }
        }
        foreach($invalids as $value) {
            $this->specify(
                "'$value' is unrecognized",
                function () use ($value) {
                    CredentialNormalizer::normalizeCredentialParamName($value);
                },
                ['throws' => new \DomainException('Unknown credential parameter: "' . $value . '"')]
            );
        }
    }

    public function testNormalizeCredentialParamNames()
    {
        $this->assertEquals(
            ['consumer_key' => 'A', 'consumer_secret' => 'B', 'token' => 'C', 'token_secret' => 'D'],
            CredentialNormalizer::normalizeCredentialParamNames([0 => 'A', 'cs' => 'B', 'token' => 'C', 3 => 'D'])
        );
        $this->setExpectedException(\DomainException::class, 'Unknown credential parameter: "XXX"');
        CredentialNormalizer::normalizeCredentialParamNames([0 => 'A', 'cs' => 'B', 'XXX' => 'C', 3 => 'D']);
    }
}
