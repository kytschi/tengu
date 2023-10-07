<?php

/**
 * Users controller.
 *
 * @package     Kytschi\Tengu\Controllers\Core\UsersController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers\Core;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Controllers\EmailController;
use Kytschi\Tengu\Controllers\Core\GroupsController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Kytschi\Tengu\Helpers\StringHelper;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Core\Groups;
use Kytschi\Tengu\Models\Core\Notifications;
use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Traits\Core\Api;
use Kytschi\Tengu\Traits\Core\Files;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Json;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Notes;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Tengu\Traits\Core\Pagination;
use Kytschi\Tengu\Traits\Core\Security;
use Kytschi\Wako\Models\UserTaxCodes;
use Kytschi\Wako\Traits\TaxYear;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Email;

class UsersController extends ControllerBase
{
    use Api;
    use Files;
    use Form;
    use Json;
    use Logs;
    use Notes;
    use Pagination;
    use Security;
    use Tags;
    use TaxYear;

    public $access = [
        'administrator',
        'super-user'
    ];
    public $global_url = '/users';
    public $resource = 'user';

    public $titles = [
        'Mr', 'Ms', 'Miss', 'Mrs', 'Master', 'Rev', 'Dr', 'Pastor', 'Rabbi', 'Sister', 'Father'
    ];

    public $types = [
        'user',
        'customer',
        'director',
        'employee',
        'contractor'
    ];

    public function addAction($template = 'core/users/add')
    {
        $this->secure($this->access);

        $data = [
            'statuses' => $this->defaultStatuses(),
            'types' => $this->types
        ];

        if ($template == 'core/users/add') {
            $this->setPageTitle('Add a user');
            $data = array_merge(
                $data,
                [
                    'groups' => $this->getGroups()
                ]
            );
        }

        return $this->view->partial(
            $template,
            $data
        );
    }

    public function addProfileImage($model)
    {
        $this->clearFiles('profile-image', $model->id);

        list($width, $height) = getimagesize($_FILES['upload_picture']['tmp_name']);
        $desired_width = 400;

        switch ($_FILES['upload_picture']['type']) {
            case 'image/jpeg':
                $upload = imagecreatefromjpeg($_FILES['upload_picture']['tmp_name']);
                $ext = 'jpg';
                break;
            case 'image/png':
                $upload = imagecreatefrompng($_FILES['upload_picture']['tmp_name']);
                $ext = 'png';
                break;
        }

        $file = $this->addFile(
            $model->id,
            [
                'name' => $model->id . '-profile',
                'type' => $_FILES['upload_picture']['type'],
                'tmp_name' => $_FILES['upload_picture']['tmp_name']
            ],
            'profile-image',
            'profile-image',
            $model->id . '-profile.' . $ext,
            false
        );

        $width = imagesx($upload);
        $height = imagesy($upload);

        $desired_height = intval(floor($height * ($desired_width / $width)));
        $save = imagecreatetruecolor($desired_width, $desired_height);
        imagecopyresampled($save, $upload, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        switch ($_FILES['upload_picture']['type']) {
            case 'image/jpeg':
                imagejpeg($save, ($this->di->getConfig())->application->dumpDir . $file->filename, 60);
                @shell_exec('jpegoptim -m 60 ' . ($this->di->getConfig())->application->dumpDir . $file->filename);
                break;
            case 'image/png':
                imagepng($save, ($this->di->getConfig())->application->dumpDir . $file->filename, 6);
                @shell_exec('optipng ' . ($this->di->getConfig())->application->dumpDir . $file->filename);
                break;
        }
    }

    public function check($email)
    {
        return (new Users())->findFirst([
            'conditions' => 'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ]);
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Users())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->softDelete();

            $this->addLog(
                $this->resource,
                $model->id,
                'danger',
                'Deleted by ' . $this->getUserFullName()
            );

            $this->saveFormDeleted(ucfirst(str_replace('-', ' ', $this->resource)) . ' has been deleted');
            $this->redirect(UrlHelper::backend($this->global_url));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function editAction($id, $template = 'core/users/edit', $group = null)
    {
        $this->secure($this->access);

        $query = 'id = :id:';
        $params = [
            'id' => $this->dispatcher->getParam('id')
        ];

        if ($group) {
            $query .= ' AND group_id = :group_id:';
            $params['group_id'] = $group;
        }

        $model = (new Users())
            ->findFirst([
                'conditions' => $query,
                'bind' => $params
            ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $data = [
            'data' => $model,
            'statuses' => $this->defaultStatuses(),
            'types' => $this->types
        ];

        if ($template == 'core/users/edit') {
            $this->setPageTitle('Editing the user');
            $data = array_merge(
                $data,
                [
                    'groups' => $this->getGroups(),
                    'tax_years' => $this->getTaxYears()
                ]
            );
        }

        return $this->view->partial(
            $template,
            $data
        );
    }

    public function getGroups()
    {
        $groups = [];

        $results = Groups::query()
            ->where('id != ""')
            ->execute()
            ->toArray();

        foreach ((new GroupsController())->getGroups() as $name => $group) {
            $groups[] = (new Groups(
                [
                    'id' => $group[0],
                    'name' => $name,
                    'slug' => $group[1],
                    'system' => true,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => '00000000-0000-0000-0000-000000000000',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => '00000000-0000-0000-0000-000000000000'
                ]
            ))->toArray();
        }

        return array_merge($groups, $results);
    }

    public static function getNotifications()
    {
        return (new Notifications())
            ->find([
                'conditions' => 'deleted_at IS NULL AND to_user_id = :id:',
                'bind' => [
                    'id' => self::getUserId()
                ]
            ]);
    }

    public function getTitles()
    {
        return $this->titles;
    }

    public function notificationsAction()
    {
        $this->apiSecure();
        $this->apiResponse('notifications', self::getNotifications());
    }

    public function indexAction($template = 'core/users/index', $group = null)
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->savePagination();
        $this->setFilters();

        if ($template == 'core/users/index') {
            $this->setPageTitle('Users');
        }

        $this->setIndexDefaults(
            [
                'email',
                'first_name',
                'last_name',
                'status'
            ],
            'email'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Users::class)
            ->where('id != ""')
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        $params = [];

        if (!empty($this->search)) {
            $params = [
                'email' => '%' . $this->search . '%',
                'first_name' => '%' . $this->search . '%',
                'last_name' => '%' . $this->search . '%'
            ];

            $builder
                ->andWhere(
                    'email LIKE :email: OR
                    first_name LIKE :first_name: OR
                    last_name LIKE :last_name:'
                );
        }

        $group_exclude = true;
        if ($group) {
            $builder->andWhere('group_id = :group_id:');
            $params['group_id'] = $group;
            $group_exclude = false;
        } else {
            $group = (new GroupsController())->getGroup('Customer')[0];
            $builder->andWhere('group_id != :group_id:');
            $params['group_id'] = $group;
        }

        if (!empty($this->filters)) {
            $iLoop = 1;
            foreach ($this->filters as $filter => $value) {
                if (empty($value)) {
                    continue;
                }
                switch ($filter) {
                    case 'status':
                        $builder->andWhere('status LIKE :status_' . $iLoop . ':');
                        $params['status_' . $iLoop] = $value;
                        $iLoop++;
                        break;
                }
            }
        }

        $builder->setBindParams($params);

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            $template,
            [
                'data' => $paginator->paginate(),
                'stats' => $this->stats($group, $group_exclude)
            ]
        );
    }

    public function profileAction()
    {
        $this->secure();

        $model = (new Users())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => self::getUserId()
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->setPageTitle('My profile');

            return $this->view->partial(
                'core/users/profile',
                [
                    'data' => $model
                ]
            );
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function profileSaveAction()
    {
        $this->secure();

        $model = (new Users())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => self::getUserId()
            ]
        ]);
        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->validate($model, true);

            $model->email = $_POST['email'];
            $model->first_name = $_POST['first_name'];
            $model->last_name = $_POST['last_name'];
            $model->job_title = !empty($_POST['job_title']) ? $_POST['job_title'] : null;

            if (!empty($_POST['password'])) {
                $model->password = self::hash($_POST['password']);
            }

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update your profile',
                    $model->getMessages()
                );
            }

            if (!empty($_FILES['upload_picture']['tmp_name'])) {
                $this->addProfileImage($model);
            }

            $model->decryptData();

            $data = new \stdClass();
            $data->group = $model->group->slug;
            $data->full_name = $model->full_name;
            $data->first_name = $model->first_name;
            $data->id = $model->id;
            $data->profile_image = $model->profile_image;

            $this->session->set('user', $data);

            $this->saveFormUpdated("Nice one, you've updated your profile");
            $this->redirect(UrlHelper::backend($this->global_url . '/profile'));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/profile'));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Users())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $model->recover();

            $this->addLog(
                $this->resource,
                $model->id,
                'warning',
                'Recovered by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated(ucfirst(str_replace('-', ' ', $this->resource)) . ' has been recovered');
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function saveAction()
    {
        $this->secure($this->access);

        try {
            $this->validate();

            $model = $this->setData(new Users());
            if (!empty($_POST['password'])) {
                $model->password = self::hash($_POST['password']);
                $model->temp_password = 0;
            } else {
                $password = StringHelper::random(14);
                $model->password = self::hash($password);
                $model->temp_password = 1;
            }
            $model->status = 'active';

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the user',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            if ($model->temp_password) {
                (new EmailController())->send(
                    !empty($this->tengu->settings->name) ? $this->tengu->settings->name : $_ENV['APP_NAME'],
                    !empty($this->tengu->settings->contact_email) ?
                        $this->tengu->settings->contact_email : $_ENV['SMTP_USERNAME'],
                    'Welcome to ' . $_ENV['APP_NAME'],
                    "We have set you a temporary password <strong>" . $password . "</strong>",
                    $model->full_name,
                    $model->email
                );
            }

            $this->saveFormSaved(ucfirst(str_replace('-', ' ', $this->resource)) . ' has been saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/add'));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function searchAction()
    {
        $this->apiSecure();

        $valids = [
            'email',
            'group'
        ];

        $query = '';
        $params = [];

        foreach ($_POST as $key => $value) {
            if (!in_array($key, $valids)) {
                continue;
            }

            switch ($key) {
                case 'email':
                    $query .= 'email = :email: AND ';
                    $params['email'] = $value;
                    break;
                case 'group':
                    $query .= 'group_id = :group_id: AND ';
                    $params['group_id'] = $value;
                    break;
            }
        }

        $this->apiResponse(
            'found users',
            (new Users())->findFirst([
                'conditions' => rtrim($query, ' AND '),
                'bind' => $params
            ])
        );
    }

    public function setData($model, $data = null)
    {
        if (empty($data)) {
            $data = $_POST;
        }

        $model->email = $data['email'];
        $model->first_name = $data['first_name'];
        $model->last_name = $data['last_name'];
        $model->type = $data['type'];
        $model->group_id = $data['group_id'];
        $model->job_title = !empty($data['job_title']) ? $data['job_title'] : null;
        $model->phone = !empty($data['phone']) ? $data['phone'] : null;
        $model->company = !empty($data['company']) ? $data['company'] : null;
        $model->address_line_1 = !empty($data['address_line_1']) ? $data['address_line_1'] : null;
        $model->address_line_2 = !empty($data['address_line_2']) ? $data['address_line_2'] : null;
        $model->town = !empty($data['town']) ? $data['town'] : null;
        $model->county = !empty($data['county']) ? $data['county'] : null;
        $model->country = !empty($data['country']) ? $data['country'] : null;
        $model->postcode = !empty($data['postcode']) ? $data['postcode'] : null;
        $model->utr = !empty($data['utr']) ? $data['utr'] : null;
        $model->employee_ref = !empty($data['employee_ref']) ? $data['employee_ref'] : null;
        $model->national_insurance = !empty($data['national_insurance']) ? $data['national_insurance'] : null;
        $model->dob = !empty($data['dob']) ? DateHelper::sql($data['dob'], false) : null;

        if (($this->di->getConfig())->apps->wako) {
            $model->shareholder = !empty($data['shareholder']) ? floatval($data['shareholder']) : null;
        }

        if ($model->status != 'deleted') {
            $model->deleted_at = null;
            $model->deleted_by = null;
        }

        return $model;
    }

    public function stats($group = null, $group_exclude = false)
    {
        $table = (new Users())->getSource();

        if ($group && !$group_exclude) {
            $group_query = "AND group_id = '$group'";
        } elseif ($group && $group_exclude) {
            $group_query = "AND group_id != '$group'";
        }

        $query = 'SELECT ';
        $query .= "(SELECT count(id) FROM $table WHERE status = 'active' $group_query) AS active,";
        $query .= "(SELECT count(id) FROM $table WHERE status = 'inactive' $group_query) AS inactive,";
        $query .= "(SELECT count(id) FROM $table WHERE deleted_at IS NOT NULL $group_query) AS deleted,";

        $model = new Users();
        return (new \Phalcon\Mvc\Model\Resultset\Simple(
            null,
            $model,
            $model->getReadConnection()->query(rtrim($query, ','))
        ))->toArray()[0];
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Users())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->validate($model);

            $model = $this->setData($model);

            if (!empty($_POST['password'])) {
                $model->password = self::hash($_POST['password']);
            }

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the user',
                    $model->getMessages()
                );
            }

            $this->addTagsFromRequest($model->id, true);
            $this->addNoteFromRequest($model->id);

            if (!empty($_FILES['upload_sig']['name'])) {
                $this->addFile(
                    $model->id,
                    $_FILES['upload_sig'],
                    'sig',
                    $model->id,
                    '',
                    false,
                    true
                );
            }

            if (!empty($_POST['tax_year_id']) && !empty($_POST['tax_code'])) {
                $info = $this->getTaxCodeInfo($_POST['tax_code']);

                $code = new UserTaxCodes([
                    'user_id' => $model->id,
                    'tax_year_id' => $_POST['tax_year_id'],
                    'code' => $_POST['tax_code'],
                    'percentage' => $info->percentage,
                    'allowance' => $info->allowance,
                ]);

                if ($code->save() === false) {
                    throw new SaveException(
                        'Failed to save the tax code',
                        $code->getMessages()
                    );
                }
            }

            if (!empty($_FILES['upload_picture']['tmp_name'])) {
                $this->addProfileImage($model);
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated(ucfirst(str_replace('-', ' ', $this->resource)) . ' has been updated');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (ValidationException $err) {
            $this->saveValidationError($err);
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    private function validate($data = null, $profile = false)
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST, ['password', 'password_check']);

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
            'first_name',
            new PresenceOf(
                [
                    'message' => 'The first name is required',
                ]
            )
        );

        $validation->add(
            'last_name',
            new PresenceOf(
                [
                    'message' => 'The last name is required',
                ]
            )
        );

        if (!$profile) {
            $validation->add(
                'type',
                new PresenceOf(
                    [
                        'message' => 'The type is required',
                    ]
                )
            );
            $validation->add(
                'group_id',
                new PresenceOf(
                    [
                        'message' => 'The group is required',
                    ]
                )
            );
        }

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Validation failed', $messages);
        }

        if ($data === null) {
            if (empty($_POST['password']) || empty($_POST['password_check'])) {
                throw new ValidationException('Validation failed, passwords required');
            } elseif ($_POST['password'] != $_POST['password_check']) {
                throw new ValidationException('Validation failed, passwords do not match');
            }

            if (!empty($this->check($_POST['email']))) {
                throw new ValidationException('Failed to save, email already in the system');
            }
        } else {
            if (!empty($_POST['password']) && !empty($_POST['password_check'])) {
                if ($_POST['password'] != $_POST['password_check']) {
                    throw new ValidationException('Validation failed, passwords do not match');
                }
            }

            if ($_POST['email'] != $data->email && !empty($this->check($_POST['email']))) {
                throw new ValidationException('Failed to save, email already in the system');
            }
        }
    }
}
