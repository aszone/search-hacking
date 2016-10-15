<?php

namespace Aszone\SearchHacking\Engines;

use Aszone\SearchHacking\Utils;
use Aszone\ProxyAvenger;

abstract class Engine implements EngineInterface
{
    protected $error;
    protected $listOfVirginProxies;
    protected $usingVirginProxies;
    protected $tor;
    protected $commandData;
    protected $proxies;
    protected $proxy;

    private $defaultCommandData = [
        'dork' => false,
        'pl' => false,
        'tor' => false,
        'virginProxies' => false,
        'proxyOfSites' => false
    ];

    public function __construct(array $data)
    {
        $this->commandData = array_merge($this->defaultCommandData, $data);

        if ($this->hasProxy()) {
            $this->proxies = new Proxies();
        }

        if ($this->commandData['tor']) {
            $this->proxy = $this->proxies->getTor();
        }

        if ($this->commandData['proxyOfSites']) {
            $this->proxy = $this->proxies->getProxyOfSites();
        }

        if ($this->commandData['virginProxies']) {
            $this->listOfVirginProxies = $this->proxies->getVirginSiteProxies();
            $this->usingVirginProxies = true;
        }
    }
    
    public function validate()
    {
        if ($this->commandData['virginProxies'] && !$this->proxies->checkVirginProxiesExist()) {
            $error['type'] = 'vp';
            $error['result'] = 'There is no list of botnets Virgin Proxy';

            $this->error = $error;

            return false;
        }

        return true;
    }

    public function hasProxy()
    {
        return ($this->commandData['virginProxies'] 
                || $this->commandData['proxyOfSites'] 
                || $this->commandData['tor']);
    }

    public function getError()
    {
        return $this->error;
    }

    public function output($value)
    {
        echo $value;
    }

    public function run()
    {}
}

