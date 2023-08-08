<?php

/**
 * Stats traits.
 *
 * @package     Kytschi\Tengu\Traits\Website\Stats
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Traits\Website;

use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Models\Website\SearchStats;
use Kytschi\Tengu\Models\Website\Stats as Model;
use Kytschi\Tengu\Models\Website\StatsExclude;
use Kytschi\Tengu\Traits\Core\User;

trait Stats
{
    use User;

    public $exclude_bots = [
        'android',
        'android 6.0.1',
        'cpu',
        'msie9.0',
        'msie',
        'google-site-verification',
        'msie 6.0',
        'windows',
        'konqueror'
    ];

    public $browsers = [
        'IE 9' => 'MSIE 9.0',
        'IE 10' => 'MSIE 10.0',
        'IE 8' => 'MSIE 8.0',
        'IE 7' => 'MSIE 7.0',
        'IE 6' => 'MSIE 6.0',
        'opera' => 'OPR',
        'opera ' => 'opera',
        'vivaldi',
        'edg',
        'safari',
        'chrome',
        'firefox',
        'dalvik',
        'lynx',
        'mozilla'
    ];

    public $operating_systems = [
        'Windows 10' => 'Windows NT 10.0',
        'Windows 7' => 'Windows NT 6.1',
        'Windows 8' => 'Windows NT 6.2',
        'Windows 8.1' => 'Windows NT 6.3',
        'Windows XP' => 'Windows NT 5.1',
        'Mac OS X 12.5' => 'Mac OS X 12_5',
        'Mac OS X 12.5' => 'Mac OS X 12.5',
        'Mac OS X 10.15.5' => 'Mac OS X 10_15_5',
        'Mac OS X 10.15.6' => 'Mac OS X 10_15_6',
        'Ubuntu/Linux' => 'Ubuntu',
        'Android 9.0' => 'Android 9.0',
        'Android 7.0' => 'Android 7.0',
        'Linux' => 'Linux'
    ];

    public $bots = [
        'Mail.RU_Bot' => 'https://help.mail.ru/webmaster/indexing/robots',
        'DnBCrawler-Analytics' => 'DnBCrawler-Analytics',
        'IonCrawl' => 'IonCrawl',
        'DuckDuckBot' => 'DuckDuckBot',
        'Sogou web spider' => 'Sogou web spider',
        'Twitterbot' => 'Twitterbot',
        'ZoominfoBot' => 'zoominfobot',
        'ZaldamoSearchBot' => 'ZaldamoSearchBot',
        'Xenu Link Sleuth' => 'Xenu Link Sleuth',
        'Who.is Bot' => 'Who.is Bot',
        'webprosbot' => 'webprosbot',
        'W3C Validator' => 'W3C_Validator',
        'TLS tester' => 'TLS tester',
        'SuperBot' => 'SuperBot',
        'SiteScoreBot' => 'SiteScoreBot',
        'serpstatbot' => 'serpstatbot',
        'Screaming Frog' => 'Screaming Frog SEO',
        'panscient.com' => 'panscient.com',
        'pantest' => 'pantest',
        'Pandalytics' => 'Pandalytics',
        'PetalBot' => 'petalbot',
        'P3P Validator' => 'P3P Validator',
        'okhttp' => 'okhttp',
        'Netwalk research scanner' => 'Netwalk research scanner',
        'NetSystemsResearch' => 'NetSystemsResearch',
        'netEstate' => 'netEstate',
        'Nicecrawler' => 'Nicecrawler',
        'googlebot' => 'Googlebot',
        'bingbot' => 'bingbot',
        'pdrlabs cloud mapping' => 'Cloud mapping experiment',
        '2ip bot' => '2ip bot',
        'Baiduspider' => 'Baiduspider',
        'CheckMarkNetwork' => 'CheckMarkNetwork',
        'colly' => 'colly',
        'expanseinc.com' => 'expanseinc.com',
        'facebookexternalhit' => 'facebookexternalhit',
        'FeedFetcher-Google' => 'FeedFetcher-Google',
        'Fuzz Faster U Fool' => 'Fuzz Faster U Fool',
        'got' => 'got (https://github.com/sindresorhus/got)',
        'HTTP Banner Detection' => 'HTTP Banner Detection',
        'Gather Analyze Provide' => 'https://gdnplus.com',
        'httpx' => 'github.com/projectdiscovery/httpx',
        'IDG/IT' => 'IDG/IT (http://spaziodati.eu/)',
        'Keybot' => 'Keybot Translation-Search-Machine',
        'Kryptos Logic Telltale' => 'Kryptos Logic Telltale - telltale.kryptoslogic.com',
        'l9tcpid' => 'l9tcpid',
        'LightspeedSystemsCrawler' => 'LightspeedSystemsCrawler',
        'LinkWalker' => 'LinkWalker/3.0 (http://www.brandprotect.com)',
        'masscan-ng' => 'masscan-ng',
        'crawlson' => 'Crawlson',
        'PetalBot' => 'PetalBot',
        'Applebot' => 'Applebot',
        'CCBot' => 'CCBot',
        'APIs-Ayeh' => 'APIs-Ayeh',
        'Google Site Verification' => 'Google-Site-Verification',
        'CommonCrawl' => 'CommonCrawl',
        'Bloglovin' => 'Bloglovin'
    ];

    public function addSearchStat($search_query, $type = 'internal')
    {
        $referer = null;
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $url = parse_url($_SERVER['HTTP_REFERER']);
            if (!empty($url['host'])) {
                if ($url['host'] != $_ENV['APP_SITE_DOMAIN']) {
                    $referer = trim(str_replace(['www.'], '', $url['host']));
                }
            }
        }

        $query = 'deleted_at IS NULL AND exclude IN (:ip:';
        $binds = [
            'ip' => $_SERVER['REMOTE_ADDR']
        ];

        if ($referer) {
            $query .= ', :referer:';
            $binds['referer'] = $referer;
            $type = 'external';
        }

        $query .= ')';

        if (
            $model = (new StatsExclude())->findFirst(
                [
                    'conditions' => $query,
                    'bind' => $binds
                ]
            )
        ) {
            return;
        }

        $stat = new SearchStats(
            [
                'visitor' => self::getUserIp(),
                'query' => urlencode($search_query),
                'type' => $type,
                'referer' => $referer
            ]
        );

        if ($stat->save() === false) {
            throw new SaveException(
                'Failed to save the stat entry',
                $stat->getMessages()
            );
        }
    }

    public function addStat($resource_id, $resource, $parent_id = '')
    {
        $referer = null;
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $url = parse_url($_SERVER['HTTP_REFERER']);
            if (!empty($url['host'])) {
                if ($url['host'] != $_ENV['APP_SITE_DOMAIN']) {
                    $referer = trim(str_replace(['www.'], '', $url['host']));
                }
            }
        }

        $bot = null;
        $browser = null;
        $operating_system = null;
        $agent = '';
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $agent = $_SERVER['HTTP_USER_AGENT'];

            foreach ($this->bots as $type => $check) {
                if (strpos(strtolower($agent), strtolower($check)) !== false) {
                    $bot = $type;
                    break;
                }
            }

            $browser_obj = @get_browser($agent);
            if (!empty($browser_obj)) {
                foreach ($this->browsers as $check) {
                    if (
                        strpos(strtolower($browser_obj->browser), str_replace(' ', '', strtolower($check))) !== false
                    ) {
                        $browser = trim($check);
                    }
                }
            }

            foreach ($this->operating_systems as $type => $check) {
                if (
                    strpos(str_replace(' ', '', strtolower($agent)), str_replace(' ', '', strtolower($check))) !== false
                ) {
                    $operating_system = trim($type);
                }
            }

            if (empty($bot)) {
                if (empty($referer) && strpos($agent, "(compatible;") !== false) {
                    $splits = explode(';', $agent);
                    if (!empty($splits[1])) {
                        $splits = explode(' ', strtolower(trim($splits[1])));
                        $splits = explode('/', $splits[0]);
                        $bot = str_replace([')'], '', $splits[0]);
                        if (in_array($bot, $this->exclude_bots)) {
                            $bot = null;
                        }
                    }
                }
            }
        }

        $query = 'exclude IN (:ip:';
        $binds = [
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        if ($referer) {
            $query .= ', :referer:';
            $binds['referer'] = $referer;
        }
        $query .= ')';

        if (
            $model = (new StatsExclude())->findFirst(
                [
                    'conditions' => $query,
                    'bind' => $binds
                ]
            )
        ) {
            return;
        }

        $stat = new Model(
            [
                'resource' => $resource,
                'resource_id' => $resource_id,
                'parent_id' => $parent_id,
                'visitor' => self::getUserIp(),
                'referer' => $referer,
                'bot' => $bot,
                'agent' => $agent,
                'browser' => $browser,
                'operating_system' => $operating_system
            ]
        );

        if ($stat->save() === false) {
            throw new SaveException(
                'Failed to save the stat entry',
                $stat->getMessages()
            );
        }
    }
}
