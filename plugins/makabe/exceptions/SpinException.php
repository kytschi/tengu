<?php

/**
 * Spin exception,
 *
 * @package     Kytschi\Makabe\Exceptions\SpinException
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Makabe\Exceptions;

use Kytschi\Tengu\Exceptions\GenericException;

class SpinException extends GenericException
{
    /**
     * Error message.
     */
    protected $message = 'Spin error';

    /**
     * Construct.
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @param string $url
     */
    public function __construct(string $message, $data = null, int $code = 400, string $url = '')
    {
        /*
         * Trigger the parent construct.
         */
        parent::__construct($message, $data, $code, $url);
    }
}
