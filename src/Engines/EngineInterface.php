<?php

namespace Aszone\SearchHacking\Engines;

interface EngineInterface
{
    public function run();

    public function getError();

    public function validate();

    public function output($value);
}
