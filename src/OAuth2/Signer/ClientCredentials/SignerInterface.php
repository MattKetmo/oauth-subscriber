<?php
namespace GuzzleHttp\Subscriber\OAuth2\Signer\ClientCredentials;

use GuzzleHttp\Message\RequestInterface;

interface SignerInterface
{
    /**
     * Signs the given request using the provided client ID and Secret.
     * 
     * @param RequestInterface $request
     * @param string           $clientId      OAuth client identifier
     * @param string           $clientSecret  OAuth client secret
     */
    public function sign(RequestInterface $request, $clientId, $clientSecret);
}
