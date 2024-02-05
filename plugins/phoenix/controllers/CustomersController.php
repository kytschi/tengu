<?php

/**
 * Orders controller.
 *
 * @package     Kytschi\Phoenix\Controllers\CustomersController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Phoenix\Controllers;

use Kytschi\Tengu\Controllers\Core\GroupsController;
use Kytschi\Tengu\Controllers\Core\UsersController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Security;

class CustomersController extends UsersController
{
    use Security;

    public $access = [
        'super-user'
    ];

    public $global_url = '/customers';
    public $resource = 'customer';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->sales . $this->global_url;
    }

    public function add($data)
    {
        if (!($user = $this->check($data['email']))) {
            $data['group_id'] = (new GroupsController())->getGroup('Customer')[0];

            $user = $this->setData(new Users(), $data);
            $user->password = self::hash(StringHelper::random(14));
            $user->temp_password = 1;

            if ($user->save() === false) {
                throw new SaveException(
                    'Failed to create the customer',
                    $user->getMessages()
                );
            }
        }

        return $user;
    }

    public function addAction($template = 'phoenix/customers/add')
    {
        $this->setPageTitle('Creating a customer');
        parent::addAction($template);
    }

    public function editAction($id, $template = 'phoenix/customers/edit', $group = null)
    {
        $this->setPageTitle('Customer view');

        parent::editAction(
            $id,
            $template,
            (new GroupsController())->getGroup('Customer')[0]
        );
    }

    public function indexAction($template = 'phoenix/customers/index', $group = null)
    {
        $this->setPageTitle('Customers');

        parent::indexAction(
            $template,
            (new GroupsController())->getGroup('Customer')[0]
        );
    }

    public function saveAction()
    {
        $_POST['group_id'] = (new GroupsController())->getGroup('Customer')[0];
        parent::saveAction();
    }

    public function updateAction()
    {
        $_POST['group_id'] = (new GroupsController())->getGroup('Customer')[0];
        parent::updateAction();
    }
}
