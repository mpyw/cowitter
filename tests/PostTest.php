<?php

require_once dirname(dirname(__FILE__)) . '/settings_for_tests_and_examples.php';

class PostTest extends PHPUnit_Framework_TestCase {
    
    public function testDuplicatedTweet() {
        // Try first tweet
        $text = '@re4k test ' . md5(mt_rand());
        $request = TwistRequest::postAuto(
            'statuses/update',
            array('status' => $text),
            new TwistCredential(CK, CS, AT, ATS)
        );
        $this->assertEquals($text, $request->execute()->text);
        // The next tweet will fail with duplicated reason
        $this->setExpectedException(
            'TwistException',
            'Status is a duplicate.',
            403
        );
        $request->execute();
    }
    
    public function testTweetWithMedia() {
        $path = dirname(dirname(__FILE__)) . '/sample_image_for_uploading.jpg';
        $request = TwistRequest::postAuto(
            'statuses/update_with_media',
            '',
            new TwistCredential(CK, CS, AT, ATS)
        );
        // Try uploading with image path
        $request
            ->setParams(array(
                'status' => '@re4k test ' . md5(mt_rand()),
                '@media[]' => $path,
            ))
            ->execute()
        ;
        // Try uploading with image binary data
        $request
            ->setParams(array(
                'status' => '@re4k test ' . md5(mt_rand()),
                'media[]' => file_get_contents($path),
            ))
            ->execute()
        ;
        $path = 'foo/bar/INVALID_FILENAME';
        // The next tweet will fail with the reason why file not found
        $this->setExpectedException(
            'InvalidArgumentException',
            "File not found: {$path}"
        );
        $request
            ->setParams(array(
                'status' => '@re4k test ' . md5(mt_rand()),
                '@media[]' => $path,
            ))
            ->execute()
        ;
    }
    
    public function testChangeAvatar() {
        $path = dirname(dirname(__FILE__)) . '/sample_image_for_uploading.jpg';
        $request = TwistRequest::postAuto(
            'account/update_profile_image',
            '',
            new TwistCredential(CK, CS, AT, ATS)
        );
        // Try uploading with image path
        $request
            ->setParams(array('@image' => $path))
            ->execute()
        ;
        // Try uploading with image binary data (base64_encode required)
        $request
            ->setParams(array('image' => base64_encode(file_get_contents($path))))
            ->execute()
        ;
        // The next request will fail with non-base64-encoded data
        $this->setExpectedException(
            'TwistException',
            'not recognized.',
            400
        );
        $request
            ->setParams(array('image' => file_get_contents($path)))
            ->execute()
        ;
    }
    
}