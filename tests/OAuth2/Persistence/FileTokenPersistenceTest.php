<?php

namespace GuzzleHttp\Subscriber\OAuth2\Tests\Persistence;

use GuzzleHttp\Subscriber\OAuth2\Persistence\FileTokenPersistence;

class FileTokenPersistenceTest extends TokenPersistenceTestBase
{

	protected $test_file;

	public function getInstance()
	{
		return new FileTokenPersistence($this->test_file);
	}

	public function setUp()
	{
		$this->test_file = tempnam(sys_get_temp_dir(), "guzzle_phpunit_test_");
		if (file_exists($this->test_file)) {
			unlink($this->test_file);
		}
	}

	public function tearDown()
	{
		if (file_exists($this->test_file)) {
			unlink($this->test_file);
		}
	}
}