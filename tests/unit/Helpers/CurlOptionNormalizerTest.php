<?php

use mpyw\Cowitter\Helpers\CurlOptionNormalizer;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class CurlOptionNormalizerTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function testStringify()
    {
        $valids = [
            'CURLOPT_USERAGENT' => [
                CURLOPT_USERAGENT,
                'CURLOPT_USERAGENT',
            ],
            'CURLOPT_RETURNTRANSFER' => [
                CURLOPT_RETURNTRANSFER,
                'CURLOPT_RETURNTRANSFER',
            ],
        ];
        $invalids = [
            'curlopt_useragent',
            'curlopt_returntransfer',
            114514,
        ];
        foreach($valids as $expected => $values) {
            foreach ($values as $value) {
                $this->specify("'$value' is recognized as '$expected'",
                function () use ($expected, $value){
                    $this->assertEquals($expected, CurlOptionNormalizer::stringify($value));
                });
            }
        }
        foreach($invalids as $value) {
            $this->specify(
                "'$value' is unrecognized",
                function () use ($value) {
                    CurlOptionNormalizer::stringify($value);
                },
                ['throws' =>
                    is_int($value)
                    ? new \DomainException("Invalid cURL option number: $value")
                    : new \DomainException("Invalid cURL option name: $value")
                ]
            );
        }
    }

    public function testStringifyAll()
    {
        $this->assertEquals(
            ['CURLOPT_URL' => 'A', 'CURLOPT_RETURNTRANSFER' => 'B'],
            CurlOptionNormalizer::stringifyAll([CURLOPT_URL => 'A', CURLOPT_RETURNTRANSFER => 'B'])
        );
        $this->setExpectedException(\DomainException::class, 'Invalid cURL option number: 114514');
        CurlOptionNormalizer::stringifyAll([114514 => 'A']);
    }

    public function testNumerify()
    {
        $valids = [
            CURLOPT_USERAGENT => [
                CURLOPT_USERAGENT,
                'CURLOPT_USERAGENT',
            ],
            CURLOPT_RETURNTRANSFER => [
                CURLOPT_RETURNTRANSFER,
                'CURLOPT_RETURNTRANSFER',
            ],
        ];
        $invalids = [
            'curlopt_useragent',
            'curlopt_returntransfer',
            114514,
        ];
        foreach($valids as $expected => $values) {
            foreach ($values as $value) {
                $this->specify("'$value' is recognized as '$expected'",
                function () use ($expected, $value){
                    $this->assertEquals($expected, CurlOptionNormalizer::numerify($value));
                });
            }
        }
        foreach($invalids as $value) {
            $this->specify(
                "'$value' is unrecognized",
                function () use ($value) {
                    CurlOptionNormalizer::numerify($value);
                },
                ['throws' =>
                    is_int($value)
                    ? new \DomainException("Invalid cURL option number: $value")
                    : new \DomainException("Invalid cURL option name: $value")
                ]
            );
        }
    }

    public function testNumerifyAll()
    {
        $this->assertEquals(
            [CURLOPT_URL => 'A', CURLOPT_RETURNTRANSFER => 'B'],
            CurlOptionNormalizer::numerifyAll(['CURLOPT_URL' => 'A', 'CURLOPT_RETURNTRANSFER' => 'B'])
        );
        $this->setExpectedException(\DomainException::class, 'Invalid cURL option name: CURLOPT_YJMURKMR');
        CurlOptionNormalizer::numerifyAll(['CURLOPT_YJMURKMR' => 'A']);
    }
}
