<?php

require '../settings_for_tests_and_examples.php';

class VariousExceptionHadlingTest extends PHPUnit_Framework_TestCase {
    
    public function testCredential() {
        return new TwistCredential('A', 'B', 'C', 'D');
    }
    
    public function testOAuth() {
        return new TwistOAuth(new TwistCredential('A', 'B', 'C', 'D'));
    }
    
    /**
     * Basic usage with Try-Catch statement.
     * 
     * @depends                  testOAuth
     * @expectedException        TwistException
     * @expectedExceptionCode    401
     * @expectedExceptionMessage Invalid or expired token
     */
    public function testTwistOAuthAutoHandling(TwistOAuth $to) {
        $to->getAuto('statuses/home_timeline', array('count' => 3));
    }
    
    /**
     * Basic usage with Try-Catch statement.
     * 
     * @depends                  testCredential
     * @expectedException        TwistException
     * @expectedExceptionCode    401
     * @expectedExceptionMessage Invalid or expired token
     */
    public function testTwistRequestAutoHandling(TwistCredential $tc) {
        $request = TwistRequest::getAuto('statuses/home_timeline', array('count' => 3), $tc);
        $request->execute();
    }
    
    /**
     * Manual handling.
     * 
     * @depends testOAuth
     */
    public function testTwistOAuthManualHandling(TwistOAuth $to) {
        $response = $to->get('statuses/home_timeline', array('count' => 3));
        $this->assertTrue($response instanceof TwistException);
        $this->assertEquals(401, $response->getCode());
        $this->assertEquals('Invalid or expired token', $response->getMessage());
    }
    
    /**
     * Manual handling.
     * 
     * @depends testCredential
     */
    public function testTwistRequestManualHandling(TwistCredential $tc) {
        $request = TwistRequest::get('statuses/home_timeline', array('count' => 3), $tc);
        $response = $request->execute();
        $this->assertTrue($response instanceof TwistException);
        $this->assertEquals(401, $response->getCode());
        $this->assertEquals('Invalid or expired token', $response->getMessage());
    }
    
}