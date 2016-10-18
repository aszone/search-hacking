<?php

namespace Aszone\SearchHacking;

use Aszone\SearchHacking\Engines\EngineInterface;

class SearchHacking
{
    private $engine;

    public function __construct(EngineInterface $engine, $proxyPath = null)
    {
        $this->engine = $engine;

        if (file_exists($proxyPath)) {
            unlink($this->proxyPath);
        }
    }

    public function run()
    {
        if ($this->engine->getError()) {
            return $this->engine;
        }

        return $this->engine->run();
    }
}

