<?php

namespace Aszone\SearchHacking\Engines;

use Aszone\SearchHacking\Utils;
use Aszone\FakeHeaders\FakeHeaders;
use GuzzleHttp\Client;

class Google extends Engine
{
    private $siteGoogle;

    private function loadGoogleSite()
    {
        $ini_google_sites = parse_ini_file(__DIR__.'/../../resources/AllGoogleSites.ini');
        $this->siteGoogle = $ini_google_sites[array_rand($ini_google_sites)];
    }

    public function run()
    {
        $this->loadGoogleSite();

        $exit = false;
        $count = 0;
        $paginator = '';
        $countProxyVirgin = rand(0, count($this->listOfVirginProxies) - 1);
        $resultFinal = array();
        $countProxyFail = array();

        while ($exit == false) {
            if ($count != 0) {
                $numPaginator = 100 * $count;
                $paginator = '&start='.$numPaginator;
            }

            $urlOfSearch = 'https://'.$this->siteGoogle.'/search?q='.urlencode($this->commandData['dork']).'&num=100&btnG=Search&pws=1'.$paginator;

            $this->output('Page '.$count."\n");

            if ($this->commandData['virginProxies']) {
                $body = Utils::getBodyByVirginProxies($urlOfSearch, $this->listOfVirginProxies[$countProxyVirgin], $this->proxy);

                //Check if exist captcha
                //Check if next group of return data or not
                $arrLinks = array();
                if (!$this->checkCaptcha($body) and $body != 'repeat') {
                    $arrLinks = Utils::getLinks($body);
                } else {
                    --$count;
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
                $body = $this->getBody($urlOfSearch);
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

    private function getBody($urlOfSearch)
    {
        $header = new FakeHeaders();
        $valid = true;

        try {
            $client = new Client([
                'defaults' => [
                    'headers' => ['User-Agent' => $header->getUserAgent()],
                    'proxy' => $this->proxy,
                    'timeout' => 60,
                ],
            ]);

            return $client->get($urlOfSearch)->getBody()->getContents();
        } catch (\Exception $e) {
            $this->output('ERROR : '.$e->getMessage()."\n");

            if ($this->proxy == false) {
                $this->output("Your ip is blocked, we are using proxy at now...\n");
            }
        }

        return false;
    }

    private function checkCaptcha($body)
    {
        return preg_match('/CaptchaRedirect/', $body, $matches, PREG_OFFSET_CAPTURE);
    }
}
