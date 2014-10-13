<?php

namespace GuzzleHttp\Subscriber\OAuth2;

class RawToken
{
    /**
     * Access Token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Refresh Token.
     *
     * @var string
     */
    protected $refreshToken;

    /**
     * Expiration timestamp.
     *
     * @var int
     */
    protected $expiresAt;

    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @param int    $expiresAt
     */
    public function __construct($accessToken, $refreshToken = null, $expiresAt = null)
    {
        $this->accessToken  = (string) $accessToken;
        $this->refreshToken = (string) $refreshToken;
        $this->expiresAt    = (int) $expiresAt;
    }

    /**
     * Dump this object to an normalized array data.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'access_token'  => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_at'    => $this->expiresAt,
        ];
    }

    /**
     * @return string The access token
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string|null The refresh token
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return int The expiration timestamp
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expiresAt && $this->expiresAt < time();
    }
}
