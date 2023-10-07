<?php

/**
 * Index controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\LoginController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Kytscha\Controllers\Kytscha;
use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\AuthorisationException;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Security;
use Kytschi\Tengu\Traits\Core\Validation as TenguValidation;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Email;

class LoginController extends ControllerBase
{
    use Form;
    use Logs;
    use Security;
    use TenguValidation;

    private $login_attempts = 3;

    public function loginAction()
    {
        if (self::isLoggedIn()) {
            $this->redirect(UrlHelper::backend('/dashboard'));
        }

        try {
            if (empty($_POST)) {
                $this->clearFormData();
                $this->setPageTitle('Login');

                return $this->view->partial("core/users/login");
            } else {
                $this->validate();

                if (!$this->tengu->captchaValidate($_POST['captcha'])) {
                    throw new AuthorisationException('Invalid captcha');
                }

                $this->validTSC($_POST['_TSC']);

                $model = Users::findFirst(
                    [
                        'conditions' => 'email = :email:',
                        'bind'       => [
                            'email' => self::encrypt($_POST['email']),
                        ]
                    ]
                );

                if (empty($model)) {
                    throw new AuthorisationException(
                        'Failed to login',
                        'Invalid user'
                    );
                }

                if ($model->login_attempts >= $this->login_attempts) {
                    $model->status = 'inactive';
                    if ($model->update() === false) {
                        throw new SaveException(
                            'Failed to update the user',
                            $model->getMessages()
                        );
                    }
                    throw new AuthorisationException(
                        'Your account has been disabled',
                        $model->id
                    );
                }

                $data = new \stdClass();
                $data->group = $model->group->slug;
                $data->full_name = $model->full_name;
                $data->first_name = $model->first_name;
                $data->id = $model->id;
                $data->profile_image = $model->profile_image->url;

                $model->login_attempts += 1;
                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the user',
                        $model->getMessages()
                    );
                }

                if (!$this->security->checkHash($_POST['password'], $model->password)) {
                    throw new AuthorisationException(
                        'Failed to login',
                        'Invalid password'
                    );
                }

                if ($model->status == 'deleted') {
                    throw new AuthorisationException(
                        'Your account has been removed',
                        $model->id
                    );
                } elseif ($model->status == 'inactive') {
                    throw new AuthorisationException(
                        'Your account has been disabled',
                        $model->id
                    );
                }

                $url = UrlHelper::backend('/dashboard');
                if (!empty($_GET['from'])) {
                    $url = urldecode($_GET['from']);
                }

                $model->last_login = date('Y-m-d H:i:s');
                $model->login_attempts = 0;

                if ($model->update() === false) {
                    throw new SaveException(
                        'Failed to update the user',
                        $model->getMessages()
                    );
                }

                $this->session->set('user', $data);

                $this->redirect($url);
            }
        } catch (\Exception $err) {
            $data = $_POST;
            $data['password'] = '*************';
            unset($data['_TENGU_CAPTCHA']);
            $this->addLog(
                'login',
                '',
                'error',
                $err->getMessage(),
                json_encode($data)
            );
            $this->saveFormError($err->getMessage());

            $url = '/login';
            if (!empty($_GET['from'])) {
                $url .= '?from=' . $_GET['from'];
            }
            $this->redirect(
                UrlHelper::backend($url)
            );
        }
    }

    public function logoutAction()
    {
        $this->logout();
    }

    private function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $validation = new Validation();

        $validation->add(
            'email',
            new PresenceOf(
                [
                    'message' => 'The email is required',
                ]
            )
        );

        $validation->add(
            'email',
            new Email(
                [
                    'message' => 'The email is not valid',
                ]
            )
        );

        $validation->add(
            'password',
            new PresenceOf(
                [
                    'message' => 'The password is required',
                ]
            )
        );

        $validation->add(
            '_TSC',
            new PresenceOf(
                [
                    'message' => 'Missing form authentication key',
                ]
            )
        );

        $validation->add(
            '_TENGU_CAPTCHA',
            new PresenceOf(
                [
                    'message' => 'This captcha is required',
                ]
            )
        );

        $validation->add(
            'captcha',
            new PresenceOf(
                [
                    'message' => 'This captcha is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new AuthorisationException('Validation failed', $messages);
        }
    }
}
