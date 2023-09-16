<?php

/**
 * Stock exception,
 *
 * @package     Kytschi\Phoenix\Exceptions\StockException
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

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
