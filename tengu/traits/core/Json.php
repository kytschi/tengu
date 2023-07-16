<?php

/**
 * Json traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Json
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Traits\Core;

use Kytschi\Tengu\Traits\Core\HttpStatus;

trait Json
{
    use HttpStatus;
    
    public function jsonError($err)
    {
        $obj = new \stdClass();
        $obj->code = $err->getCode();
        $obj->message = $err->getMessage();

        $obj->message = method_exists($err, 'getData') ? $err->getData() : null;

        $this->outputJson($obj);
    }

    public function outputJson($data, $code = 200)
    {
        $this->setHttpStatus($code);

        header('Content-Type: application/json; charset=UTF-8');
        echo \json_encode($data);
        die();
    }
}
