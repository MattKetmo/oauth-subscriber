<?php

namespace GuzzleHttp\Subscriber\OAuth2\Tests\Signer\ClientCredentials;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Subscriber\OAuth2\Signer\ClientCredentials\PostFormData;

class PostFormDataTest extends PHPUnit_Framework_TestCase
{    
    public function testSign()
    {
    	$client_id = 'foo';
        $client_secret = 'bar';

        $client_id_field_name = 'client_id';
        $client_secret_field_name = 'client_secret';

        $request = new Request('GET', '/');
        $request->setBody(new PostBody());

        $signer = new PostFormData();
        $signer->sign($request, $client_id, $client_secret);

        $this->assertEquals($client_id, $request->getBody()->getField($client_id_field_name));
        $this->assertEquals($client_secret, $request->getBody()->getField($client_secret_field_name));
    }

    public function testSignCustomFields()
    {
		$client_id = 'foo';
        $client_secret = 'bar';

        $client_id_field_name = 'foo_id';
        $client_secret_field_name = 'foo_secret';

        $request = new Request('GET', '/');
        $request->setBody(new PostBody());

        $signer = new PostFormData($client_id_field_name, $client_secret_field_name);
        $signer->sign($request, $client_id, $client_secret);

        $this->assertEquals($client_id, $request->getBody()->getField($client_id_field_name));
        $this->assertEquals($client_secret, $request->getBody()->getField($client_secret_field_name));
    }
}
