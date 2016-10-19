<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 23/04/16
 * Time: 01:43.
 */

namespace Aszone\SearchHacking\Engines;

use Aszone\SearchHacking\Utils;

class Yandex extends Engine
{
    public function run()
    {
        $exit = false;
        $count = 0;
        $numPaginator = 0;
        $countProxyVirgin = rand(0, count($this->listOfVirginProxies) - 1);
        $resultFinal = array();

        $countProxyFail = array();

        while ($exit == false) {
            if ($count != 0) {
                $numPaginator = $count;
            }

            $urlOfSearch = 'https://yandex.ru/search/?text='.urlencode($this->commandData['dork']).'&p='.$numPaginator.'&lr=10136';

            $this->output('Page '.$count."\n");

            if ($this->commandData['virginProxies']) {
                $body = Utils::getBodyByVirginProxies($urlOfSearch, $this->listOfVirginProxies[$countProxyVirgin], $this->proxy);

                //Check if next group of return data or not
                $arrLinks = array();
                if (!$this->checkCaptcha($body) and $body != 'repeat') {
                    $arrLinks = Utils::getLinks($body);
                } else {
                    --$count;
                    //Count the proxys with fail and all fail proxys, finish action
                    $countProxyFail[$countProxyVirgin] = $this->listOfVirginProxies[$countProxyVirgin];
                    $this->output("You has a problem with proxy, probaly you estress the engenier ...\n");
                }

                //Check if next virgin proxy or repeat of 0
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

            if (((count($results) == 0 && $body != 'repeat') && !$this->checkCaptcha($body))
                || (count($countProxyFail) == count($this->listOfVirginProxies))) {
                $exit = true;
            }

            $resultFinal = array_merge($resultFinal, $results);

            ++$count;
        }

        return $resultFinal;
    }

    private function checkCaptcha($body)
    {
        return preg_match("/https:\/\/yandex.ru\/showcaptcha\?/", $body, $matches, PREG_OFFSET_CAPTURE);
    }
}
