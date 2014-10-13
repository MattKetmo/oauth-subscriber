<?php

namespace GuzzleHttp\Subscriber\OAuth2\Tests\Signer\ClientCredentials;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Subscriber\OAuth2\Signer\ClientCredentials\BasicAuth;

class BasicAuthTest extends PHPUnit_Framework_TestCase
{
    public function testSign()
    {
        $client_id = 'foo';
        $client_secret = 'bar';

        $request = new Request('GET', '/');
        $signer = new BasicAuth();
        $signer->sign($request, $client_id, $client_secret);

        $this->assertEquals('Basic '.base64_encode($client_id.':'.$client_secret), $request->getHeader('Authorization'));
    }
}
