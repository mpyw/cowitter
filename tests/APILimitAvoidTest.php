<?php

require_once dirname(dirname(__FILE__)) . '/settings_for_tests_and_examples.php';

class APILimitAvoidTest extends PHPUnit_Framework_TestCase {
    
    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Specified credential is already registered as main credential.
     */
    public function testDuplicatedWithMain() {
        $main = $sub = new TwistCredential(CK, CS, AT, ATS);
        $to = new TwistOAuth($main);
        $to->setSub($sub);
    }
    
    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Specified credential is already registered as sub credential.
     */
    public function testDuplicatedWithSub() {
        $main = new TwistCredential(CK, CS, AT, ATS);
        $sub1 = $sub2 = new TwistCredential(CK, CS, AT, ATS);
        $to = new TwistOAuth($main);
        $to->setSub($sub1, $sub2);
    }
    
}