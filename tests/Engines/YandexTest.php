<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 23/04/16
 * Time: 01:43.
 */

namespace Aszone\SearchHacking\Engines\Test;

use Aszone\SearchHacking\Engines\Yandex;

class YandexTest extends \PHPUnit_Framework_TestCase
{
    private $instance;

    public function setUp()
    {
        $this->instance = new Yandex(['dork' => 'site:com.ar ext:sql password']);
    }

    public function testRun()
    {
        $links = $this->instance->run();

        $this->assertTrue($this->hasArrayOfLinks($links));
    }

    private function hasArrayOfLinks($links)
    {
        return (bool) array_filter($links, function ($link) {
            return strpos($link, 'http') === 0;
        });
    }
}
