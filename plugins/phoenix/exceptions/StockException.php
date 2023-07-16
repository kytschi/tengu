<?php

/**
 * Stock exception,
 *
 * @package     Kytschi\Phoenix\Exceptions\StockException
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Phoenix\Exceptions;

use Kytschi\Tengu\Exceptions\GenericException;

class StockException extends GenericException
{
    /**
     * Error message.
     */
    protected $message = 'Stock error';

    /**
     * Construct.
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @param string $url
     */
    public function __construct(string $message, $data = null, int $code = 403, string $url = '')
    {
        /*
         * Trigger the parent construct.
         */
        parent::__construct($message, $data, $code, $url);
    }
}
