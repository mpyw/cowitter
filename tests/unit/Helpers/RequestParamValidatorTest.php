<?php

use mpyw\Cowitter\Helpers\RequestParamValidator;

use mpyw\Privator\Proxy;
use mpyw\Privator\ProxyException;

/**
 * @requires PHP 7.0
 */
class RequestParamValidatorTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function testValidateParams()
    {
        $source = [
            'a' => '1',
            'b' => 2,
            'c' => new SimpleXMLElement('<x>3</x>'),
            'd' => null,
            'e' => new \CURLFile(__FILE__),
            'f' => true,
            'g' => false,
            'h' => STDIN,
        ];
        $expected = [
            'a' => '1',
            'b' => '2',
            'c' => '3',
            'e' => base64_encode(file_get_contents(__FILE__)),
            'f' => '1',
            'g' => '',
            'h' => 'Resource id #1',
        ];
        $this->assertSame($expected, RequestParamValidator::validateParams($source));
    }

    public function testValidateMultipartParams()
    {
        $source = [
            'a' => '1',
            'b' => 2,
            'c' => new SimpleXMLElement('<x>3</x>'),
            'd' => null,
            'e' => $e = new \CURLFile(__FILE__),
            'f' => true,
            'g' => false,
            'h' => STDIN,
        ];
        $expected = [
            'a' => '1',
            'b' => '2',
            'c' => '3',
            'e' => $e,
            'f' => '1',
            'g' => '',
            'h' => 'Resource id #1',
        ];
        $this->assertSame($expected, RequestParamValidator::validateMultipartParams($source));
    }

    public function testInvalidFile()
    {
        $this->setExpectedException(\RuntimeException::class);
        RequestParamValidator::validateStringable('xyz', new \CURLFile(__FILE__ . __FILE__));
    }

    public function testArray()
    {
        $this->setExpectedException(\InvalidArgumentException::class, '"xyz" must be stringable, array given.');
        RequestParamValidator::validateStringable('xyz', []);
    }

    public function testObject()
    {
        $this->setExpectedException(\InvalidArgumentException::class, '"xyz" must be stringable, object given.');
        RequestParamValidator::validateStringable('xyz', new \stdClass);
    }
}
