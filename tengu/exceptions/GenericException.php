<?php

/**
 * Generic exception.
 *
 * @package     Kytschi\Tengu\Exceptions\GenericException
 * @copyright   2022 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

namespace Kytschi\Tengu\Exceptions;

use Exception;
use Kytschi\Tengu\Traits\Core\Logs;

class GenericException extends Exception
{
    use Logs;

    /**
     * HTTP Error code.
     * @var int $code
     */
    protected $code = 400;

    /**
     * The data for the error.
     */
    protected $data = null;

    /**
     * Error message.
     */
    protected $message = 'Exception';

    /**
     * Status.
     * @var string
     */
    protected $status = 'failed';

    /**
     * Redirect url.
     */
    protected $url = '';

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
         * Set the message.
         */
        $this->message = $message;

        /*
         * Set the code.
         */
        $this->code = $code;

        /*
         * Set the data.
         */
        $this->data = $data;

        /*
         * Set the url.
         */
        $this->url = $url;

        if (is_object($data)) {
            if (get_class($data) == 'Phalcon\Messages\Messages') {
                $tmp = $data;
                $data = [];
                foreach ($tmp as $item) {
                    $data[] = $item;
                }
            }
        }

        /**
         * Log the error.
         */
        $this->addLog(
            'exception',
            null,
            'error',
            $message,
            $data
        );

        /*
         * Trigger the parent construct.
         */
        parent::__construct($message, $code);
    }

    /**
     * Return the data.
     *
     * @return mixed $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return the url.
     *
     * @return mixed $data
     */
    public function getUrl()
    {
        return $this->url;
    }
}
