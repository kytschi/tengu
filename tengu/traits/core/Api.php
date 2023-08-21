<?php

/**
 * Api traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Api
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Exceptions\AuthorisationException;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Traits\Core\Json;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Paginator\Adapter\QueryBuilder;

trait Api
{
    use Json;
    use User;

    private $version = 'v1';

    public function apiError($err, $code = 400)
    {
        if (is_object($err)) {
            $this->apiResponse($err->getMessage(), null, $code);
        }

        $this->apiResponse($err, null, $code);
    }
    
    public function apiResponse($message, $data = null, $code = 200, $query = '')
    {
        $output = new \stdClass();
        $output->copyright = '(c)' . date('Y') . ' Kytschi';
        $output->website = 'https://kytschi.com';
        $output->verion = $this->version;
        $output->code = $code;
        $output->message = $message;
        $output->query = $query;
        $output->data = $data;
        $this->outputJson($output, $code);
    }

    public function apiSecure()
    {
        if ($this->request->getHeader('TENGU-API-KEY') != $_ENV['APP_KEY']) {
            throw new AuthorisationException('Invalid API key');
        }
    }
}
