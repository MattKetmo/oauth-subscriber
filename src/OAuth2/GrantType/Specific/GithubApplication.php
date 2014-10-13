<?php

namespace GuzzleHttp\Subscriber\OAuth2\GrantType\Specific;

use GuzzleHttp\Subscriber\OAuth2\GrantType\ClientCredentials;
use GuzzleHttp\Subscriber\OAuth2\GrantType\GrantTypeInterface;
use GuzzleHttp\Subscriber\OAuth2\Signer\ClientCredentials\SignerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Collection;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Stream\Stream;

/**
 * GitHub Application-specific grant type.  Like ClientCredentials, but uses
 * github.com username/password via basic auth and client_id/client_secret via JSON
 * to create an access_token.
 */
class GithubApplication implements GrantTypeInterface
{
    /**
     * The token endpoint client.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Configuration settings.
     *
     * @var Collection
     */
    protected $config;

    /**
     * @param ClientInterface $client
     * @param array           $config
     */
    public function __construct(ClientInterface $client, array $config)
    {
        $this->client = $client;
        $this->config = Collection::fromConfig($config,
            // Defaults
            [
                'client_secret' => '',
                'scope' => '',
            ],
            // Required
            [
                'client_id',
                'note',
                'username',
                'password',
            ]
        );
    }

    public function getRawData(SignerInterface $clientCredentialsSigner, $refreshToken = null)
    {
        $request = $this->client->createRequest('POST', null);
        $request->setBody($this->getPostBody());

        $clientCredentialsSigner->sign(
            $request,
            $this->config['username'],
            $this->config['password']
        );

        $response = $this->client->send($request);

        // Restructure some fields from the GitHub response
        /* Example Response:
        {
          "id": 00913101,
          "url": "https://api.github.com/authorizations/00913101",
          "app": 
          {
            "name": "OAuthTestApplication",
            "url": "http://localhost/test",
            "client_id": "042c2d7a8a216e2bbf82",
          },
          "token": "ab3758bd55c324cfee74c87fcc704656af6d98f6",
          "note": "OAuth Test Token",
          "note_url": NULL,
          "created_at": "2014-10-10T19:05:00Z",
          "updated_at": "2014-10-10T19:05:00Z",
          "scopes": 
          [
            "public_repo",
            "repo",
            "user",
          ],
        }
        */
        $data = $response->json();
        $data['access_token'] = $data['token'];
        unset($data['token']);

        return $data;
    }

    protected function getPostBody()
    {
        $postBody = [
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'note'          => $this->config['note'],
            'scopes'        => [],
        ];

        if ($this->config['scope']) {
            // In github's API, "scope" is called "scopes" and is passed as a JSON array
            $postBody['scopes'] = explode(',', $this->config['scope']);
        }

        if ($this->config['note_url']) {
            $postBody['note_url'] = $this->config['note_url'];
        }

        return Stream::factory(json_encode($postBody));
    }

    /**
     * Helper function to parse the GitHub HTTP Response header "Link", which 
     * contains links to next and/or previous "pages" of data
     * 
     * @param  GuzzleHttpMessageResponse $response
     * @return array Array containing keys: next, prev, first, last
     */
    public static function parseLinkHeader(\GuzzleHttp\Message\Response $response) {
        $linkHeader = $response->getHeader('Link');
        if (!strpos($linkHeader, 'rel')) {
            return null;
        }

        $out = [
            "next" => null,
            "last" => null,
            "prev" => null,
            "first" => null,
        ];

        $links = explode(',', $linkHeader);
        foreach ($links as $link) {
            $parts = explode(';', $link);
            if (count($parts) < 2)
                continue;

            // Get the URL
            $url = trim(array_shift($parts), '<> ');
            $rel_parts = explode('=', trim(array_shift($parts)));

            if (count($rel_parts) !== 2 || $rel_parts[0] != 'rel')
                continue;

            // Get the rel="" value (next, prev, first, last)
            $rel = trim($rel_parts[1], ' "\'');
            $out[$rel] = $url;
        }

        return $out;
    }

    /**
     * Helper function to retrieve all the "pages" of results from a GitHub API call
     * and returns them as a single array
     * 
     * @param  ClientInterface $client
     * @param  string $url
     * @return array
     */
    public static function getAllResults(ClientInterface $client, $url) {
        $data = [];
        do {
            $response = $client->get($url);
            $data = array_merge($data, $response->json());
            $url = GithubApplication::parseLinkHeader($response)['next'];
        } while ($url);
        return $data;
    }
}
