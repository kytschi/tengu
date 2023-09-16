<?php

/**
 * Postcodes controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\PostcodesController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Traits\Core\Api;
use Kytschi\Tengu\Traits\Core\Logs;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class PostcodesController extends ControllerBase
{
    use Api;
    use Logs;

    public $access = [
        'super-user'
    ];

    public $global_url = '/postcodes';
    public $resource = 'postcodes';

    /*
     * Pull out any post/zip codes
     * thanks to http://www.pixelenvision.com/1708/zip-postal-code-validation-regex-php-code-for-12-countries/
     */
    private $code_regs = array(
        "United States" => "^\d{5}([\-]?\d{4})?$",
        "United Kingdom" => "[A-Z]{1,2}[0-9A-Z]{1,2} ?[0-9][A-Z]{2}",
        "Germany" => "\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
        "Canada" => "^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])\s*(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$",
        "France" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$",
        "Italy" => "^(V-|I-)?[0-9]{5}$",
        "Australia" => "^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$",
        "Netherlands" => "^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$",
        "Spain" => "^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$",
        "Denmark" => "^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$",
        "Sweden" => "^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$",
        "Belgium" => "^[1-9]{1}[0-9]{3}$"
    );

    public function getCoordinates($postcode)
    {
        $guzzle = new Guzzle(
            [
                'base_uri' => 'https://api.postcodes.io',
                'verify' => false,
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]
        );

        $res = $guzzle->request(
            'GET',
            '/postcodes/' . $postcode
        );

        $contents = json_decode($res->getBody()->getContents());

        if (empty($contents)) {
            throw new RequestException('Failed to find the postcode, empty response');
        }

        if (empty($contents->status)) {
            throw new RequestException('Failed to find the postcode, invalid response');
        }

        if ($contents->status != 200) {
            throw new RequestException('Failed to find the postcode, invalid response code');
        }

        return [
            'latitude' => $contents->result->latitude,
            'longitude' => $contents->result->longitude
        ];
    }

    public function getReg($country = 'United Kingdom')
    {
        return !empty($this->code_regs[$country]) ?
            $this->code_regs[$country] :
            $this->code_regs['United Kingdom'];
    }

    public function hasPostcode($postcode, $country = 'United Kingdom')
    {
        preg_match_all("/" . $this->getReg($country) . "/i", $postcode, $matches);
        if (!empty($matches[0])) {
            return $matches[0];
        }

        return null;
    }

    public function searchAction($postcode, $api = true)
    {
        if ($api) {
            $this->apiSecure();
        }

        $return = [];

        if ($_ENV['APP_ENV'] != 'local') {
            $guzzle = new Guzzle(
                [
                    'base_uri' => 'https://api.postcodes.io',
                    'verify' => false,
                    'headers' => [
                        'Accept' => 'application/json'
                    ]
                ]
            );

            $res = $guzzle->request(
                'GET',
                '/postcodes/' . $postcode
            );

            $contents = json_decode($res->getBody()->getContents());

            if (empty($contents)) {
                throw new RequestException('Failed to find the postcode, empty response');
            }

            if (empty($contents->status)) {
                throw new RequestException('Failed to find the postcode, invalid response');
            }

            if ($contents->status != 200) {
                throw new RequestException('Failed to find the postcode, invalid response code');
            }

            $output = new \stdClass();
            $output->postcode = $postcode;
            $output->address_line_1 = '';
            $output->address_line_2 = '';
            $output->town = $contents->result->admin_district;
            $output->county = $contents->result->region;
            $output->country = $contents->result->country;
            $output->building = '';
            $output->longitude = $contents->result->longitude;
            $output->latitude = $contents->result->latitude;

            $return[] = $output;
        } else {
            for ($iLoop = 1; $iLoop < 50; $iLoop++) {
                $output = new \stdClass();
                $output->postcode = 'NE123QW';
                $output->address_line_1 = $iLoop . ' Sample St';
                $output->address_line_2 = '';
                $output->town = 'Sample Town';
                $output->county = 'Sample County';
                $output->country = 'Unitied Kingdom';
                $output->building = '';
                $output->longitude = '-4.802022';
                $output->latitude = '50.30964';

                $return[] = $output;
            }
        }

        if ($api) {
            $this->apiResponse('found postcode', $return);
        } else {
            return $return;
        }
    }
}
