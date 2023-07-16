<?php

/**
 * Save exception,
 *
 * @package     Kytschi\Tengu\Exceptions\SaveException
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Exceptions;

use Kytschi\Tengu\Exceptions\GenericException;

class SaveException extends GenericException
{
    /**
     * Error message.
     */
    protected $message = 'Failed to save the data';

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
