<?php

require '../settings_for_tests_and_examples.php';

class HeaderConstructionTest extends PHPUnit_Framework_TestCase {
    
    public function testWithoutCredential() {
        try {
            TwistRequest::get('statuses/user_timeline', array('count' => 3))->buildHeaders();
            $this->fail();
        } catch (BadMethodCallException $e) {
            $this->assertEquals(
                'Headers cannot be built without TwistCredential instance.',
                $e->getMessage()
            );
        }
    }
    
    private static function scrapeGetParams($headers) {
        $path = current(array_slice(explode(' ', $headers, 3), 1, 1));
        if (false !== $pos = strpos($path, '?')) {
            parse_str(substr($path, $pos + 1), $params);
            foreach ($params as $key => $value) {
                if (strpos($key, 'oauth_') === 0) {
                    unset($params[$key]);
                }
            }
            return $params;
        } else {
            return array();
        }
    }
    
    public function testVariousDataTypes() {
        $tmp = fopen('php://temp', 'r+b');
        $this->assertEquals(
            array(
                'a' => '',
                'b' => '', // without errors
                'c' => '', // without errors
                'd' => '0',
                'e' => '1',
                'f' => '0',
                'g' => '1',
                'h' => 'TEST',
                'i' => (string)$tmp,
            ),
            self::scrapeGetParams(TwistRequest::get('foo/bar', array(
                'a' => '',
                'b' => new stdClass,
                'c' => array(),
                'd' => 0,
                'e' => 1,
                'f' => false,
                'g' => true,
                'h' => new SimpleXMLElement('<a>TEST</a>'),
                'i' => $tmp,
                'j' => null, // ignored
            ), new TwistCredential)->buildHeaders())
        );
    }
    
    public function testExtraParamsPriority() {
        $request = TwistRequest::get('foo/bar?a=X', array('a' => 'Y'), new TwistCredential);
        $this->assertEquals(
            array('a' => 'Y'),
            self::scrapeGetParams($request->buildHeaders())
        );
        $request->setParams(array());
        $this->assertEquals(
            array('a' => 'X'),
            self::scrapeGetParams($request->buildHeaders())
        );
        $request->setParams(array('a' => 'Z'));
        $this->assertEquals(
            array('a' => 'Z'),
            self::scrapeGetParams($request->buildHeaders())
        );
    }
    
}