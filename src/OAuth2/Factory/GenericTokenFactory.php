<?php

namespace GuzzleHttp\Subscriber\OAuth2\Factory;

use GuzzleHttp\Subscriber\OAuth2\RawToken;

class GenericTokenFactory
{
    public function __invoke(array $data, RawToken $previousToken = null)
    {
        $accessToken = null;
        $refreshToken = null;
        $expiresAt = null;

        // Read "access_token" attribute
        if (isset($data['access_token'])) {
            $accessToken = $data['access_token'];
        }

        // Read "refresh_token" attribute
        if (isset($data['refresh_token'])) {
            $refreshToken = $data['refresh_token'];
        } elseif ($previousToken !== null) {
            // When requesting a new access token with a refresh token, the
            // server may not resend a new refresh token. In that case we
            // should keep the previous refresh token as valid.
            //
            // See http://tools.ietf.org/html/rfc6749#section-6
            $refreshToken = $previousToken->getRefreshToken();
        }

        if (isset($data['expires_at'])) {
            $expiresAt = (int)$data['expires_at'];

        } else {

            // Read the "expires_in" attribute
            $expiresIn = isset($data['expires_in'])? (int)$data['expires_in']: null;

            // Facebook unfortunately breaks the spec by using 'expires' instead of 'expires_in'
            if (!$expiresIn && isset($data['expires'])) {
                $expiresIn = (int)$data['expires'];
            }

            // Set the absolute expiration if a relative expiration was provided
            if ($expiresIn) {
                $expiresAt = time() + $expiresIn;
            }

        }

        return new RawToken($accessToken, $refreshToken, $expiresAt);
    }
}
