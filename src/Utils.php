<?php
/**
 * Created by PhpStorm.
 * User: lenon
 * Date: 03/04/16
 * Time: 19:24.
 */

namespace Aszone\SearchHacking;

use Symfony\Component\DomCrawler\Crawler;
use Aszone\FakeHeaders\FakeHeaders;
use GuzzleHttp\Client;

class Utils
{
    public static function sanitazeLinks($links = array())
    {
        $hrefs = array();

        if (!empty($links)) {
            foreach ($links as $keyLink => $valueLink) {
                $url = static::clearLink($valueLink->getAttribute('href'));
                $validResultOfBlackList = static::checkBlacklist($url);

                if (!$validResultOfBlackList and $url) {
                    $hrefs[] = $url;
                }
            }

            $hrefs = array_unique($hrefs);
        }

        return $hrefs;
    }

    public static function checkBlacklist($url = '')
    {
        if (!empty($url)) {
            $validXmlrpc = preg_match("/(https?\:\/\/|^)(.+?)\//", $url, $matches, PREG_OFFSET_CAPTURE);
            $url = '';

            if (isset($matches[2][0])) {
                $url = $matches[2][0];
            }

            $ini_blakclist = parse_ini_file(__DIR__.'/../resources/Blacklist.ini');

            $key = array_search($url, $ini_blakclist);

            if ($key != false) {
                return true;
            }
        }

        return false;
    }

    public static function clearLink($url = '')
    {
        if (!empty($url)) {
            $validXmlrpc = preg_match('/search%3Fq%3Dcache:.+?:(.+?)%252B/', $url, $matches, PREG_OFFSET_CAPTURE);

            if (isset($matches[1][0])) {
                return $matches[1][0];
            }

            $validXmlrpc = preg_match("/search\?q=cache:.+?:(.+?)\+/", $url, $matches, PREG_OFFSET_CAPTURE);

            if (isset($matches[1][0])) {
                return $matches[1][0];
            }

            $validXmlrpc = preg_match('/url=(.*?)&tld/', $url, $matches, PREG_OFFSET_CAPTURE);

            if (isset($matches[1][0])) {
                return urldecode($matches[1][0]);
            }

            //Msn Bing
            $validXmlrpc = preg_match("/^((http|https):\/\/|www).+?\/?ld=.+?\&u=(.+?)\n/", $url, $matches, PREG_OFFSET_CAPTURE);

            if (isset($matches[1][0])) {
                return urldecode($matches[1][0]);
            }

            $validXmlrpc = preg_match("/^((http|https):\/\/|www)(.+?)\//", $url, $matches, PREG_OFFSET_CAPTURE);

            if (isset($matches[0][0])) {
                $check[] = strpos($url, 'www.blogger.com');
                $check[] = strpos($url, 'youtube.com');
                $check[] = strpos($url, '.google.');
                $check[] = strpos($url, 'yandex.ru');
                $check[] = strpos($url, 'microsoft.com');
                $check[] = strpos($url, 'microsofttranslator.com');
                $check[] = strpos($url, '.yahoo.com');
                $check[] = strpos($url, 'yahoo.uservoice.com');
                $check[] = strpos($url, 'www.mozilla.org');
                $check[] = strpos($url, 'www.facebook.com');
                $check[] = strpos($url, 'go.mail.ru');
                $check[] = strpos($url, '/search/srpcache?p=');
                $check[] = strpos($url, 'flickr.com');

                $tmp = array_filter($check);

                if (empty($tmp)) {
                    return trim($url);
                }
            }
        }

        return false;
    }

    public static function getLinks($body)
    {
        $crawler = new Crawler($body);

        return $crawler->filter('a');
    }

    public static function getBody($urlOfSearch, $proxy)
    {
        $header = new FakeHeaders();
        $valid = true;

        try {
            $client = new Client([
                'defaults' => [
                    'headers' => ['User-Agent' => $header->getUserAgent()],
                    'proxy' => $proxy,
                    'timeout' => 60,
                ],
            ]);

            return $client->get($urlOfSearch)->getBody()->getContents();
        } catch (\Exception $e) {
            $message = 'ERROR : '.$e->getMessage()."\n";

            if ($proxy == false) {
                $message .= "Your ip is blocked, we are using proxy at now...\n";
            }

            return $message;
        }

        return false;
    }

    public static function getBodyByVirginProxies($urlOfSearch, $urlProxie, $proxy)
    {
        $header = new FakeHeaders();

        echo 'Proxy : '.$urlProxie."\n";

        $dataToPost = ['body' => ['url' => $urlOfSearch]];

        $valid = true;
        while ($valid == true) {
            try {
                $client = new Client([
                    'defaults' => [
                        'headers' => ['User-Agent' => $header->getUserAgent()],
                        'proxy' => $proxy,
                        'timeout' => 60,
                    ],
                ]);

                $res = $client->post($urlProxie, $dataToPost);
                $body = $res->getBody()->getContents();

                //check if change new tor ip
                $valid = false;
            } catch (\Exception $e) {
                echo 'ERROR : '.$e->getMessage()."\n";
                if ($proxy == false) {
                    echo "This ip of virgin proxy is blocked, we are using proxy at now...\n";
                }

                return 'repeat';
            }
        }

        return $body;
    }
}
