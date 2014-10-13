<?php

namespace GuzzleHttp\Subscriber\OAuth2\Tests\Persistence;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Subscriber\OAuth2\RawToken;
use GuzzleHttp\Subscriber\OAuth2\Factory\GenericTokenFactory;

abstract class TokenPersistenceTestBase extends PHPUnit_Framework_TestCase
{
    abstract public function getInstance();

    public function testSaveToken()
    {
        $factory = new GenericTokenFactory();
        $token = $factory([
            'access_token' => 'abcdefghijklmnop',
            'refresh_token' => '0123456789abcdef',
            'expires_in' => 3600,
        ]);
        $this->getInstance()->saveToken($token);
    }

    public function testRestoreToken()
    {
        $factory = new GenericTokenFactory();
        $token = $factory([
            'access_token' => 'abcdefghijklmnop',
            'refresh_token' => '0123456789abcdef',
            'expires_in' => 3600,
        ]);
        $this->getInstance()->saveToken($token);

        $restored_token = $this->getInstance()->restoreToken($factory);
        $this->assertInstanceOf('\GuzzleHttp\Subscriber\OAuth2\RawToken', $restored_token);

        $token_before = $token->toArray();
        $token_after = $restored_token->toArray();

        $this->assertEquals($token_before, $token_after);
    }

    public function testDeleteToken()
    {
        $factory = new GenericTokenFactory();
        $token = $factory([
            'access_token' => 'abcdefghijklmnop',
            'refresh_token' => '0123456789abcdef',
            'expires_in' => 3600,
        ]);

        $persist = $this->getInstance();

        $persist->saveToken($token);

        $restored_token = $persist->restoreToken($factory);
        $this->assertInstanceOf('\GuzzleHttp\Subscriber\OAuth2\RawToken', $restored_token);        

        $persist->deleteToken();

        $restored_token = $persist->restoreToken($factory);
        $this->assertNull($restored_token);
    }

    public function testRestoreTokenCustomFactory()
    {
        $factory = function (array $data, RawToken $previousToken = null) {
            $access_token = strtoupper($data['access_token']);
            $expires_at = isset($data['expires_at'])? $data['expires_at']: time() + $data['expires_in'];
            return new RawToken($access_token, $data['refresh_token'], $expires_at);
        };

        $token = $factory([
            'access_token' => 'abcdefghijklmnop',
            'refresh_token' => '0123456789abcdef',
            'expires_in' => 3600,
        ]);

        $this->getInstance()->saveToken($token);

        $restored_token = $this->getInstance()->restoreToken($factory);
        $this->assertInstanceOf('\GuzzleHttp\Subscriber\OAuth2\RawToken', $restored_token);
        $this->assertEquals('ABCDEFGHIJKLMNOP', $restored_token->getAccessToken());
    }
}
