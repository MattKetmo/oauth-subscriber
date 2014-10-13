<?php

namespace GuzzleHttp\Subscriber\OAuth2\Signer\AccessToken;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Message\Request;

class BasicAuthTest extends PHPUnit_Framework_TestCase
{
    public function testSign()
    {
        $request = new Request('GET', '/');

        $signer = new BasicAuth();
        $signer->sign($request, 'foobar');

        $this->assertEquals('Bearer foobar', $request->getHeader('Authorization'));
    }
}
