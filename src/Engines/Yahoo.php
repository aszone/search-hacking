<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 23/04/16
 * Time: 01:43.
 */

namespace Aszone\SearchHacking\Engines;

use Aszone\SearchHacking\Utils;

class Yahoo extends Engine
{
    public function run()
    {
        $exit = false;
        $count = 0;
        $numPaginator = 0;
        $countProxyVirgin = rand(0, count($this->listOfVirginProxies) - 1);
        $resultFinal = array();

        while ($exit == false) {
            if ($count != 0) {
                $numPaginator = (100 * $count) + 1;
            }

            $urlOfSearch = 'https://search.yahoo.com/search?p='.urlencode($this->commandData['dork']).'&fr=yfp-t-707&pz=100&b='.$numPaginator;
            
            $this->output('Page '.$count."\n");

            if ($this->commandData['virginProxies']) {
                $body = Utils::getBodyByVirginProxies($urlOfSearch, $this->listOfVirginProxies[$countProxyVirgin], $this->proxy);

                $arrLinks = Utils::getLinks($body);

                if ($countProxyVirgin == count($this->listOfVirginProxies) - 1) {
                    $countProxyVirgin = 0;
                } else {
                    ++$countProxyVirgin;
                }
            } else {
                $body = Utils::getBody($urlOfSearch, $this->proxy);

                $arrLinks = Utils::getLinks($body);
            }

            $this->output("\n".$urlOfSearch."\n");

            $results = Utils::sanitazeLinks($arrLinks);
            
            if ((count($results) == 0 and $body != 'repeat')) {
                $exit = true;
            }
            
            $resultFinal = array_merge($resultFinal, $results);
            
            ++$count;
        }

        return $resultFinal;
    }
}

