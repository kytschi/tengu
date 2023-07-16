<?php

/**
 * Postcodes controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\PostcodesController
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
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

    public function searchAction()
    {
        $this->apiSecure();

        if ($_ENV['APP_ENV'] == 'production') {
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
                '/postcodes/' . $this->dispatcher->getParam('postcode')
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
        }

        $return = [];

        for ($iLoop = 1; $iLoop < 50; $iLoop++) {
            $output = new \stdClass();
            $output->postcode = 'NE123QW';
            $output->address_line_1 = $iLoop . ' Sample St';
            $output->address_line_2 = '';
            $output->town = 'Sample Town';
            $output->county = 'Sample County';
            $output->country = 'Unitied Kingdom';
            $output->building = '';

            $return[] = $output;
        }

        $this->apiResponse('found postcode', $return);
    }
}
