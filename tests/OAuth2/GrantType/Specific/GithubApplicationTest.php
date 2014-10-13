<?php

namespace GuzzleHttp\Subscriber\OAuth2\Tests\GrantType;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock as MockResponder;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Subscriber\OAuth2\GrantType\Specific\GithubApplication;
use GuzzleHttp\Subscriber\OAuth2\Signer\ClientCredentials\BasicAuth;

class GithubApplicationTest extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $grant = new GithubApplication(new Client(), [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'username' => 'bilbo',
            'password' => 'baggins',
            'note' => 'github test',
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Config is missing the following keys
     */
    public function testConstructThrowsForMissing()
    {
        $grant = new GithubApplication(new Client(), []);
    }

    public function testGetRawData()
    {
        $response_data = [
            'foo' => 'bar',
            // GitHub responds with "token" instead of "access_token"
            'token' => '0123456789abcdef',
        ];
        $response = new Response(200, [], Stream::factory(json_encode($response_data)));

        $mock_responder = new MockResponder([$response]);

        $client = new Client();
        $client->getEmitter()->attach($mock_responder);

        $grant = new GithubApplication($client, [
            'client_id' => 'foo',
            'client_secret' => 'bar',
            'username' => 'bilbo',
            'password' => 'baggins',
            'note' => 'github test',
        ]);

        // We'll use this to rip out the request body from a callback
        $inspect = new \stdClass();

        $signer = $this->getMockBuilder('\GuzzleHttp\Subscriber\OAuth2\Signer\ClientCredentials\BasicAuth')
            ->setMethods(['sign'])
            ->getMock();

        $signer->expects($this->once())
            ->method('sign')
            ->with($this->callback(function ($request) use ($inspect) {
                $inspect->body = $request->getBody();
                return true;
            }), 'bilbo', 'baggins');

        // Verify response data
        $raw_data = $grant->getRawData($signer);
        $this->assertEquals('bar', $raw_data['foo']);
        $this->assertEquals('0123456789abcdef', $raw_data['access_token']);

        // Verify request body data
        $request_body = json_decode($inspect->body, true);
        $this->assertEquals('foo', $request_body['client_id']);
        $this->assertEquals('bar', $request_body['client_secret']);
        $this->assertEquals('github test', $request_body['note']);
    }
}
