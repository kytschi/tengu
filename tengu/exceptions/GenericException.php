<?php

/**
 * Generic exception.
 *
 * @package     Kytschi\Tengu\Exceptions\GenericException
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301, USA.
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

    /**
     * Generate the grumpy cat, I mean just look at him!
     */
    private function gfx($code)
    {
        if (!$code) {
            $code = 500;
        }

        return "<pre>
         ⡴⠶⣆⠠⠤⣤⠤⠤⠤⠤⠤⠤⠀⠤⣔⠒⢀⡨⠛⢵⠀⠀
⠀⠀⠀⠀⠀⠀⠀⢸⠃⠀⣾⠇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⢿⠀⢸⡇⠀
⠀⠀⠀⠀⠀⠀⠀⢸⡄⡠⠃⠀⠀⠀⠀⠈⠆⠀⠀⠂⠀⠀⠀⠀⠀⠀⠱⣸⡇⠀
⠀⠀⠀⠀⠀⠀⠀⠸⡝⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢹⠁⠀
⠀⠀⠀⠀⠀⠀⠀⠀⢧⢀⣀⣀⣀⣒⣀⣀⡲⠀⠀⣂⣀⣀⣒⣂⣀⣀⡀⢸⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⢸⠈⢫⣍⡻⢟⣉⠽⠳⣖⣲⠞⠫⣁⡛⢋⣠⠜⠁⢸⠀⠀   ⢰⣷⡀⠀⠀⣿⣿⠀⣠⣴⣾⣿⣶⣦⡀⠀⣶⣶⣶⣶⣶⡄⣶⣶⣶⣶⣶⡆⠀
⠀⠀⠀⠀⠀⠀⠀⠀⢸⠀⠀⠉⠋⠁⠀⢀⡡⠚⠓⢄⡀⠀⠀⠁⠁⠀⠀⢸⠀⠀   ⢸⣿⣿⣄⠀⣿⣿⣼⣿⠋⠀⠀⠈⠻⣿⡆⣿⣇⠀⠀⢹⣿⣿⣿⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠘⣄⠀⠀⠀⠀⢄⠏⠀⠐⠀⠀⠸⠂⡄⠀⠀⠀⠀⡼⠀⠀   ⢸⣿⠙⣿⣦⣿⣿⣿⣇⠀⠀⠀⠀⠀⣿⡷⣿⣿⣤⣴⣿⠟⣿⣿⠿⠿⠿⠇⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⡇⠑⢄⠀⠀⠀⠁⠀⠀⠀⠀⠀⠁⠀⠀⠀⡴⠋⡇⠀⠀   ⢸⣿⠀⠈⢿⣿⣿⠹⣿⣦⣀⣀⣠⣼⡿⠃⣿⡯⠉⠉⠁⠀⣿⣿⣀⣀⣀⡀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⡇⠀⠀⠑⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⡀⠀⠀⠀⠃⠀⠀   ⠘⠛⠀⠀⠀⠛⠛⠀⠈⠛⠻⠿⠟⠋⠁⠀⠛⠓⠀⠀⠀⠀⠛⠛⠛⠛⠛⠃⠀
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠁⠀⠀⠀⢰⠀⠀⠀⠀⠀⠀⠀⠀⢀⠃⠀⠀⠀⡀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⢀⡆⠀⠀⠀⠀⠑⠀⠀⠀⠀⠀⠀⠀⠈⠀⠀⠀⠀⡷⡀⠀   Error Code: " . $code . "
⠀⠀⠀⠀⠀⠀⠀⢠⠊⢱⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⡰⠁⠈⢆
⠀⢀⠤⠐⠒⠉⠉⡇⡠⠤⣇⠀⠀⠀⠀⠀⡿⠀⠀⠀⢻⠀⠀⠀⠀⢰⡡⠒⢄⡸
⡰⠁⠀⣀⣄⣀⡀⢹⣱⡞⡜⡄⠀⠀⠀⠀⡇⠁⠀⠊⢠⠀⠀⠀⢀⠏⠰⡲⣮⣷
⢇⠀⠀⠀⠀⠈⢹⠉⠚⠒⢣⠼⣀⠀⠀⠀⡐⠉⠉⠉⠹⡀⠀⠀⡀⢯⣳⠊⠀⠀
⠈⠢⢀⣀⣀⠤⠃⠀⠀⠀⠳⠧⠄⠬⠤⠔⠁⠀⠀⠀⠀⠑⠂⠀⠓⠊⠀⠀⠀⠀</pre>";
    }

    /**
     * Override the default string to we can have our grumpy cat.
     */
    public function __toString()
    {
        return $this->gfx($this->getCode()) .
            "<p>&nbsp;&nbsp;<strong>" . $this->getMessage() . "</strong><br/>" .
            "&nbsp;&nbsp;<small><muted>tengu " . constant("TENGU_VERSION") . "</muted></small></p>";
    }
}
