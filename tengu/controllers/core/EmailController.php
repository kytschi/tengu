<?php

/**
 * Email controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\EmailController
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

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Models\Core\Communications;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class EmailController extends ControllerBase
{
    use Form;
    use Logs;

    /**
     * Send an email.
     *
     * @param string $from_name
     * @param string $from_email
     * @param string $subject
     * @param string $message
     * @param string $to_name
     * @param string $to_email
     */
    public function send(
        string $from_name,
        string $from_email,
        string $subject,
        string $message,
        string $to_name = '',
        string $to_email = '',
        string $from_phone = ''
    ): bool {
        try {
            if (empty($to_name)) {
                $to_name = !empty($this->tengu->settings->name) ?
                    $this->tengu->settings->name :
                    $_ENV['APP_NAME'];
            }

            if (empty($to_email)) {
                $to_email = !empty($this->tengu->settings->contact_email) ?
                    $this->tengu->settings->contact_email :
                    $_ENV['SMTP_USERNAME'];
            }

            $subject = strip_tags($subject);
            if (strlen($subject) > 255) {
                $subject = substr($subject, 0, 255);
            }

            $from_name = strip_tags($from_name);
            if (strlen($from_name) > 255) {
                $from_name = substr($from_name, 0, 255);
            }

            $from_email = strip_tags($from_email);
            if (strlen($from_email) > 255) {
                $from_email = substr($from_email, 0, 255);
            }

            $from_phone = strip_tags($from_phone);
            if (strlen($from_phone) > 255) {
                $from_phone = substr($from_phone, 0, 255);
            }

            $comms = new Communications(
                [
                    'subject' => $subject,
                    'message' => strip_tags($message),
                    'from_name' => $from_name,
                    'from_email' => $from_email,
                    'from_phone' => $from_phone,
                    'to_name' => $to_name,
                    'to_email' => $to_email,
                    'status' => (empty($_ENV['SMTP_ENABLED']) || $_ENV['SMTP_ENABLED'] == 'false') ? 'sent' : 'sending'
                ]
            );

            if ($comms->save() === false) {
                throw new SaveException(
                    'Failed to create the communications entry',
                    $comms->getMessages()
                );
            }

            if (empty($_ENV['SMTP_ENABLED']) || $_ENV['SMTP_ENABLED'] == 'false') {
                $this->clearFormData();
                return true;
            }

            /*
            * Define the mailer.
            */
            $mail = new PHPMailer();

            /*
             * Enable verbose debug output.
             */
            if ($_ENV['APP_DEBUG'] == 'true') {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }

            /**
             * Send using SMTP.
             */
            $mail->isSMTP();

            /*
             * Set the SMTP server to send through.
             */
            $mail->Host = $_ENV['SMTP_HOST'];

            /*
             * Enable SMTP authentication.
             */
            $mail->SMTPAuth = true;

            /*
             * SMTP username.
             */
            $mail->Username = $_ENV['SMTP_USERNAME'];

            /*
             * SMTP password.
             */
            $mail->Password = $_ENV['SMTP_PASSWORD'];

            /*
             * Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged.
             */
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            /*
             * TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above.
             */
            $mail->Port = $_ENV['SMTP_PORT'];

            /*
             * Recipients
             */
            $mail->setFrom($to_email, $to_name);

            /*
             * Add a recipient
             */
            $mail->addAddress($to_email, $to_name);
            $mail->addReplyTo($from_email, $from_name);

            /*
             * Content.
             * Set email format to HTML.
             */
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $message;

            /*
             * Send the email.
             */
            $mail->send();

            $comms->status = 'sent';
            if ($comms->update() === false) {
                throw new SaveException(
                    'Failed to update the communications entry',
                    $comms->getMessages()
                );
            }

            $this->clearFormData();

            /*
             * All good, return true.
             */
            return true;
        } catch (Exception $e) {
            $comms->status = 'failed';
            if ($comms->update() === false) {
                throw new SaveException(
                    'Failed to update the communications entry',
                    $comms->getMessages()
                );
            }
            /*
             * Error occurred, return false.
             */
            return false;
        }
    }
}
