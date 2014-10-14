<?php

namespace GuzzleHttp\Subscriber\OAuth2\Token;

interface Serializable
{
	public function serialize();
	public function unserialize(array $data);
}