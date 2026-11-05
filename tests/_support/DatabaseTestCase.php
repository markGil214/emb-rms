<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

abstract class DatabaseTestCase extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\ExampleSeeder';

	protected $basePath = SUPPORTPATH . 'Database/';

	protected $namespace = 'Tests\Support';
}
