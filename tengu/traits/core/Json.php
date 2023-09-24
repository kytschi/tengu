<?php

/**
 * Json traits.
 *
 * @package     Kytschi\Tengu\Traits\Core\Json
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
