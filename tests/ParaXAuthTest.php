<?php

require_once dirname(dirname(__FILE__)) . '/settings_for_tests_and_examples.php';

class ParaXAuthTest extends PHPUnit_Framework_TestCase {
    
    /**
     * Para-xAuth authorization.
     */
    public function testLoginSuccess() {
        $tc = new TwistCredential(CK, CS, '', '', SN, PW);
        $to = new TwistOAuth($tc);
        $to->login();
        $this->assertEquals(AT, $tc->accessToken);
        $this->assertEquals(ATS, $tc->accessTokenSecret);
    }
    
    /**
     * @expectedException        TwistException
     * @expectedExceptionCode    401
     * @expectedExceptionMessage Failed to validate oauth signature and token
     */
    public function testLoginErrorInvalidOrExpiredToken() {
        $to = new TwistOAuth(new TwistCredential('A', 'B', '', '', SN, PW));
        $to->login();
    }
    
    /**
     * @expectedException        TwistException
     * @expectedExceptionCode    200
     * @expectedExceptionMessage Wrong screenName or password.
     */
    public function testLoginErrorWrongUsernameOrPassword() {
        $to = new TwistOAuth(new TwistCredential(CK, CS, '', '', 'A', 'B'));
        $to->login();
    }
    
}