<?php

/**
 * Themes controller.
 *
 * @package     Kytschi\Tengu\Controllers\Website\ThemesController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.2
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

namespace Kytschi\Tengu\Controllers\Website;

use Kytschi\Tengu\Controllers\ControllerBase;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ThemeException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Themes;
use Kytschi\Tengu\Traits\Core\Form;
use Kytschi\Tengu\Traits\Core\Logs;
use Kytschi\Tengu\Traits\Core\Pagination;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class ThemesController extends ControllerBase
{
    use Form;
    use Logs;
    use Pagination;

    public $access = [
        'administrator',
        'super-user'
    ];

    public $current_theme = null;

    public $global_url = '/themes';
    public $resource = 'theme';

    private $theme_url = '/themes';

    public $tengu_themes = [
        'default',
        'clear-blue'
    ];

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->cms . $this->global_url;
    }

    public function addAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Adding a theme');

        return $this->view->partial(
            'website/themes/add',
            [
                'statuses' => $this->defaultStatuses()
            ]
        );
    }

    public function deleteAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Themes())->findFirst([
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

            $this->saveFormDeleted('Theme successfully marked as deleted');
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function editAction()
    {
        $this->secure($this->access);
        $this->setPageTitle('Editing the theme');

        $model = (new Themes())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        return $this->view->partial(
            'website/themes/edit',
            [
                'data' => $model,
                'statuses' => $this->defaultStatuses()
            ]
        );
    }

    public function get(string $resource)
    {
        return  $this->theme_url .
            trim($this->current_theme->folder, '/') .
            $resource;
    }

    public function getAsset(string $asset, $ignore_cache = false)
    {
        try {
            $url = '';
            if (empty($this->current_theme)) {
                $splits = explode('.', $asset);
                $url = 'no-deafult-theme.' . $splits[length($splits) - 1];
            }

            if (empty($url)) {
                $asset = trim($asset, '/');
                $not_found = true;

                if (
                    file_exists(
                        $this->getDI()->get('config')->application->tenguThemesDir .
                        trim($this->current_theme->folder, '/') .
                        '/assets/' .
                        $asset
                    )
                ) {
                    $url = $this->theme_url .
                        trim($this->current_theme->folder, '/') .
                        '/assets/' .
                        $asset;
                    $not_found = false;
                }

                if ($not_found) {
                    if (file_exists($this->getDI()->get('config')->application->tenguAssetDir . $asset)) {
                        $url = $this->getDI()->get('config')->application->tenguAssetWebURL . $asset;
                        $not_found = false;
                    }
                }

                if ($not_found) {
                    if (
                        !file_exists(
                            $this->getDI()->get('config')->application->themesDir .
                            trim($this->current_theme->folder, '/') .
                            '/assets/' .
                            $asset
                        )
                    ) {
                        $splits = explode('.', $asset);
                        $asset = 'resource-not-found.' . $splits[length($splits) - 1];
                    }

                    $url = $this->theme_url . trim($this->current_theme->folder, '/') . '/assets/' . $asset;
                }

                if (($_ENV['APP_ENV'] == 'local' || $_ENV['APP_ENV'] == 'staging') && !$ignore_cache) {
                    $url .= '?' . time();
                }
            }

            return $url;
        } catch (\Exception $err) {
            echo $err->getMessage();
        }
    }

    public function getAssetFolder(string $folder = '')
    {
        return $this->theme_url . trim($this->current_theme->folder, '/') . '/assets/' . $folder;
    }

    public function getCSS(string $css, bool $is_url = true)
    {
        $url = '';
        if (empty($this->current_theme)) {
            $url = 'no-deafult-theme.css';
        }

        if (empty($url)) {
            $not_found = true;
            $css = trim($css, '/');
            if (
                file_exists(
                    $this->getDI()->get('config')->application->tenguThemesDir .
                    trim($this->current_theme->folder, '/') .
                    '/css/' .
                    $css
                )
            ) {
                if ($is_url) {
                    $url = $this->getDI()->get('config')->application->tenguThemesWebURL .
                        trim($this->current_theme->folder, '/') .
                        '/css/' .
                        $css;
                } else {
                    $url = $this->getDI()->get('config')->application->tenguThemesDir .
                        trim($this->current_theme->folder, '/') .
                        '/css/' .
                        $css;
                }
                $not_found = false;
            }

            if ($not_found) {
                if (file_exists($this->getDI()->get('config')->application->tenguAssetDir . 'css/' . $css)) {
                    if ($is_url) {
                        $url = $this->getDI()->get('config')->application->tenguAssetWebURL . 'css/' . $css;
                    } else {
                        $url = $this->getDI()->get('config')->application->tenguAssetDir . 'css/' . $css;
                    }
                    $not_found = false;
                }
            }

            if ($not_found) {
                if (file_exists($this->getDI()->get('config')->application->tenguAssetDir . $css)) {
                    if ($is_url) {
                        $url = $this->getDI()->get('config')->application->tenguAssetWebURL . $css;
                    } else {
                        $url = $this->getDI()->get('config')->application->tenguAssetDir . $css;
                    }
                    $not_found = false;
                }
            }

            if ($not_found) {
                if (
                    !file_exists(
                        (
                            TENGU_BACKEND ?
                                $this->getDI()->get('config')->application->tenguThemesDir :
                                $this->getDI()->get('config')->application->themesDir
                        ) .
                        trim($this->current_theme->folder, '/') .
                        '/css/' .
                        $css
                    )
                ) {
                    $css = 'resource-not-found.css';
                }

                if ($is_url) {
                    $url = $this->theme_url . trim($this->current_theme->folder, '/') . '/css/' . $css;
                } else {
                    $url = (
                            TENGU_BACKEND ?
                                $this->getDI()->get('config')->application->tenguThemesDir :
                                $this->getDI()->get('config')->application->themesDir
                        ) .
                        trim($this->current_theme->folder, '/') .
                        '/css/' .
                        $css;
                }
            }

            if (($_ENV['APP_ENV'] == 'local' || $_ENV['APP_ENV'] == 'staging') && $is_url) {
                $url .= '?' . time();
            }
        }

        return $url;
    }

    public function getActive($settings = null)
    {
        if (TENGU_BACKEND) {
            $theme = 'default';

            if (!empty($settings)) {
                $theme = !empty($settings->tengu_theme) ? $settings->tengu_theme : 'default';
            }

            $this->current_theme = new Themes([
                'name' => $theme,
                'slug' => $theme,
                'folder' => str_replace('-', '_', $theme),
                'status' => 'active'
            ]);

            $this->theme_url = $this->getDI()->get('config')->application->tenguThemesWebURL;
            return;
        }

        if (
            $timed_theme = (new Themes())->findFirst(
                [
                    'reusable' => true,
                    'conditions' => 'deleted_at IS NULL AND NOW() >= active_from AND NOW() <= active_to'
                ]
            )
        ) {
            $this->current_theme = $timed_theme;
            if ($this->current_theme->status == 'inactive') {
                $this
                    ->modelsManager
                    ->executeQuery('UPDATE ' . Themes::class . ' SET status="inactive" WHERE status != "deleted"');

                $this->current_theme->status = 'active';
                if ($this->current_theme->save() === false) {
                    throw new SaveException(
                        'Failed to update the theme',
                        $this->current_theme->getMessages()
                    );
                }
            }
        } else {
            $this->current_theme = (new Themes())->findFirst(
                [
                    'reusable' => true,
                    'conditions' => 'deleted_at IS NULL AND status = "active"'
                ]
            );

            if (!empty($this->current_theme->active_to)) {
                if ($this->current_theme->active_to <= date('Y-m-d')) {
                    if ($this->current_theme->annual) {
                        $this->current_theme->active_to = date(
                            'Y-m-d',
                            strtotime('+1 years', strtotime($this->current_theme->active_to))
                        );
                        $this->current_theme->active_from = date(
                            'Y-m-d',
                            strtotime('+1 years', strtotime($this->current_theme->active_from))
                        );
                        $this->current_theme->status = 'inactive';
                        if ($this->current_theme->save() === false) {
                            throw new SaveException(
                                'Failed to update the theme',
                                $this->current_theme->getMessages()
                            );
                        }
                    }

                    $this->current_theme = (new Themes())->findFirst(
                        [
                            'reusable' => true,
                            'conditions' => 'deleted_at IS NULL AND default = 1'
                        ]
                    );

                    $this->current_theme->status = 'active';
                    if ($this->current_theme->save() === false) {
                        throw new SaveException(
                            'Failed to update the theme',
                            $this->current_theme->getMessages()
                        );
                    }
                }
            } elseif (empty($this->current_theme)) {
                $this->current_theme = (new Themes())->findFirst(
                    [
                        'reusable' => true,
                        'conditions' => 'deleted_at IS NULL AND default = 1'
                    ]
                );

                if (empty($this->current_theme)) {
                    throw new ThemeException(
                        'Failed to start as no theme defined'
                    );
                }

                $this->current_theme->status = 'active';
                if ($this->current_theme->save() === false) {
                    throw new SaveException(
                        'Failed to update the theme',
                        $this->current_theme->getMessages()
                    );
                }
            }
        }

        $this->theme_url = $this->getDI()->get('config')->application->themesWebURL;
    }

    public function getJS(string $js)
    {
        $js = '';
        if (empty($this->current_theme)) {
            $js = 'no-deafult-theme.js';
        }

        if ($js) {
            if (
                !file_exists(
                    $this->getDI()->get('config')->application->themesDir .
                    trim($this->current_theme->folder, '/') .
                    '/js/' .
                    $js
                )
            ) {
                $js = 'resource-not-found.js';
            }
        }

        return $this->theme_url .
            trim($this->current_theme->folder, '/') .
            '/js/' .
            $js;
    }

    public function getSlug()
    {
        return  $this->current_theme->slug;
    }

    public function getSlogan()
    {
        return  $this->current_theme->slogan;
    }

    public function indexAction()
    {
        $this->clearFormData();

        $this->secure($this->access);
        $this->setPageTitle('Themes');
        $this->savePagination();
        $this->setFilters();

        $this->setIndexDefaults(
            [
                'name',
                'folder',
                'status'
            ],
            'name'
        );

        $builder = $this
            ->modelsManager
            ->createBuilder()
            ->from(Themes::class)
            ->where('id != ""')
            ->orderBy($this->orderBy . ' ' . $this->orderDir);

        if (!empty($this->search)) {
            $builder
                ->andWhere('name LIKE :name:')
                ->orWhere('folder LIKE :folder:')
                ->setBindParams(
                    [
                        'name' => '%' . $this->search . '%',
                        'folder' => '%' . $this->search . '%'
                    ]
                );
        }

        $paginator = new QueryBuilder(
            [
                "builder" => $builder,
                "limit" => $this->perPage,
                "page" => $this->page,
            ]
        );

        return $this->view->partial(
            'website/themes/index',
            [
                'data' => $paginator->paginate()
            ]
        );
    }

    public function recoverAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        $model = (new Themes())->findFirst([
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

            $this->saveFormSaved('Theme successfully recovered');
            $this->redirect(UrlHelper::backend($this->global_url . '/edit/' . $model->id));
        } catch (Exception $err) {
            throw new SaveException(
                $err->getMessage(),
                method_exists($err, 'getData') ? $err->getData() : null
            );
        }
    }

    public function resetDefault()
    {
        $this
            ->modelsManager
            ->executeQuery('UPDATE ' . Themes::class . ' SET status="inactive" WHERE status != "deleted"');
    }

    public function saveAction()
    {
        $this->secure($this->access);

        try {
            $this->validate();

            $this->resetDefault();

            $model = $this->setData(new Themes());

            if ($model->save() === false) {
                throw new SaveException(
                    'Failed to create the theme',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Created by ' . $this->getUserFullName()
            );

            $this->saveFormSaved('Theme successfully saved');
            $this->clearFormData();
            $this->redirect(UrlHelper::backend($this->global_url));
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

    private function setData($model)
    {
        $model->name = $_POST['name'];
        $model->slug = empty($_POST['slug']) ? $this->createSlug($_POST['name']) : $_POST['slug'];
        $model->folder = $_POST['folder'];
        $model->default = isset($_POST['default']) ? true : false;
        $model->annual = isset($_POST['annual']) ? true : false;
        $model->active_from = !empty($_POST['active_from']) ? $_POST['active_from'] : null;
        $model->active_to = !empty($_POST['active_to']) ? $_POST['active_to'] : null;
        $model->status = isset($_POST['status']) ? 'active' : 'inactive';

        if ($model->status != 'deleted') {
            $model->deleted_at = null;
            $model->deleted_by = null;
        }

        $model->slogan = !empty($_POST['slogan']) ? $_POST['slogan'] : null;

        return $model;
    }

    public function setActiveAction()
    {
        $this->clearFormData();

        $this->secure($this->access);

        if (empty($_POST['active'])) {
            throw new ValidationException('Theme Id is missing');
        }

        $model = (new Themes())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $_POST['active']
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        $this->resetDefault();

        $model->status = 'active';
        if ($model->update() === false) {
            throw new SaveException(
                'Failed to update the theme',
                $model->getMessages()
            );
        }

        $this->saveFormSaved('Theme successfully set to active');
        $this->redirect(UrlHelper::backend($this->global_url));
    }

    public function updateAction()
    {
        $this->secure($this->access);

        $model = (new Themes())->findFirst([
            'conditions' => 'id = :id:',
            'bind' => [
                'id' => $this->dispatcher->getParam('id')
            ]
        ]);

        if (empty($model)) {
            return $this->notFound();
        }

        try {
            $this->validate();

            $this->resetDefault();

            $model = $this->setData($model);

            if ($model->update() === false) {
                throw new SaveException(
                    'Failed to update the theme',
                    $model->getMessages()
                );
            }

            $this->addLog(
                $this->resource,
                $model->id,
                'info',
                'Updated by ' . $this->getUserFullName()
            );

            $this->saveFormUpdated('Theme successfully updated');
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

    private function validate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'name',
            new PresenceOf(
                [
                    'message' => 'The name is required',
                ]
            )
        );

        $validation->add(
            'folder',
            new PresenceOf(
                [
                    'message' => 'The folder is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }
}
