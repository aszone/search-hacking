<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 23/04/16
 * Time: 01:43.
 */

namespace Aszone\SearchHacking\Engines;

use Aszone\SearchHacking\Utils;

class Bing extends Engine
{
    public function run()
    {
        $exit = false;
        $count = 0;
        $numPaginator = 0;
        $countProxyVirgin = rand(0, count($this->listOfVirginProxies) - 1);
        $resultFinal = array();
        $totalOutProxy = 5;
        $countOutProxy = 0;

        while ($exit == false) {
            if ($count != 0) {
                $numPaginator = 10 * $count;
            }

            $urlOfSearch = 'http://www.bing.com/search?q='.urlencode($this->commandData['dork']).'&filt=rf&first='.$numPaginator;
            
            $this->output('Page ' . $count . "\n");

            if ($this->commandData['virginProxies']) {
                
                $this->output('*' . $countProxyVirgin . '*');

                $this->output('&' . $this->listOfVirginProxies[$countProxyVirgin] . '&');

                $body = Utils::getBodyByVirginProxies($urlOfSearch, $this->listOfVirginProxies[$countProxyVirgin], $this->proxy);

                $arrLinks = Utils::getLinks($body);

                //Check if next virgin proxy or repeat of 0
                if ($countProxyVirgin == count($this->listOfVirginProxies) - 1) {
                    $countProxyVirgin = 0;
                } else {
                    ++$countProxyVirgin;
                }
            } else {
                $body = Utils::getBody($urlOfSearch, $this->proxy);

                $arrLinks = Utils::getLinks($body);

                ++$countOutProxy;
            }

            $this->output("\n" . $urlOfSearch . "\n");

            $results = Utils::sanitazeLinks($arrLinks);
            
            if ((count($results) == 0 && $body != 'repeat') || ($countOutProxy == $totalOutProxy)) {
                $exit = true;
            }

            $resultFinal = array_merge($resultFinal, $results);
            ++$count;
        }

        return $resultFinal;
    }
}

