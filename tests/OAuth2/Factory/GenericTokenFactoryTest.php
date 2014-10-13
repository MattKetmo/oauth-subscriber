<?php

namespace GuzzleHttp\Subscriber\OAuth2\Tests\Factory;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Subscriber\OAuth2\RawToken;
use GuzzleHttp\Subscriber\OAuth2\Factory\GenericTokenFactory;

class GenericTokenFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $tokenData = [
            'access_token' => '01234567890123456789abcdef',
            'refresh_token' => '01234567890123456789abcdef',
            'expires_in' => 3600,
        ];

        $factory = new GenericTokenFactory();
        $token = $factory($tokenData);

        $this->assertInstanceOf('\GuzzleHttp\Subscriber\OAuth2\RawToken', $token);
        $this->assertEquals($tokenData['access_token'], $token->getAccessToken());
        $this->assertEquals($tokenData['refresh_token'], $token->getRefreshToken());

        // Due to timing issues, this could be something other than 0
        $this->assertLessThan(2, $token->getExpiresAt() - (time() + $tokenData['expires_in']));
    }

    public function testPrefersExpiresAt()
    {
        $tokenData = [
            'access_token' => '01234567890123456789abcdef',
            'refresh_token' => '01234567890123456789abcdef',
            'expires_at' => time()+3600,
            'expires_in' => -3600,
        ];

        $factory = new GenericTokenFactory();
        $token = $factory($tokenData);

        $this->assertEquals($tokenData['expires_at'], $token->getExpiresAt());
        $this->assertFalse($token->isExpired());
    }

    public function testIsExpired()
    {
        $tokenData = [
            'access_token' => '01234567890123456789abcdef',
            'refresh_token' => '01234567890123456789abcdef',
            'expires_in' => -3600,
        ];

        $factory = new GenericTokenFactory();
        $token = $factory($tokenData);

        $this->assertTrue($token->isExpired());

        $tokenData['expires_in'] = 3600;
        $this->assertFalse($factory($tokenData)->isExpired());

        unset($tokenData['expires_in']);
        $tokenData['expires_at'] = time()-3600;
        $this->assertTrue($factory($tokenData)->isExpired());

        $tokenData['expires_at'] = time()+3600;
        $this->assertFalse($factory($tokenData)->isExpired());

        unset($tokenData['expires_at']);
        $tokenData['expires'] = -3600;
        $this->assertTrue($factory($tokenData)->isExpired());

        $tokenData['expires'] = 3600;
        $this->assertFalse($factory($tokenData)->isExpired());
    }

    public function testPreviousRefreshToken()
    {
        $factory = new GenericTokenFactory();

        $first_token = $factory([
            'access_token' => 'first_access',
            'refresh_token' => 'first_refresh',
        ]);

        $second_token = $factory([
            'access_token' => 'second_access',
        ], $first_token);

        // The "first_refresh" token has been saved from the first RawToken object
        $this->assertEquals('first_refresh', $second_token->getRefreshToken());
    }
}
