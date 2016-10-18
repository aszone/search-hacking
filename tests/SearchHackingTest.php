<?php

namespace Aszone\SearchHacking\Test;

use Aszone\SearchHacking\Engines\EngineInterface;
use Aszone\SearchHacking\SearchHacking;

class SearchHackingTest extends \PHPUnit_Framework_TestCase
{
    private $instance;

    private $engine;

    public function setUp()
    {
        $this->engine   = $this->createMock(EngineInterface::class);
        $this->instance = new SearchHacking($this->engine);
    }

    public function testRun()
    {
        $this->engine->method('run')
                     ->willReturn(true);

        $result = $this->instance->run();

        $this->assertTrue($result);
    }
}

