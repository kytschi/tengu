<?php

/**
 * Routes.
 *
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

use Phalcon\Mvc\Router;
use Kytschi\Tengu\Helpers\UrlHelper;

$url = $config->urls->mms;

/*
 * Dashboard.
 */
$router->add(
    UrlHelper::backend($url . '/dashboard'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Dashboards',
        'action'     => 'index',
    ]
);

/*
 * Keywords.
 */
$router->add(
    UrlHelper::backend($url . '/keywords'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Keywords',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/keywords/add'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Keywords',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/keywords/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Keywords',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/keywords/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Keywords',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/keywords/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Keywords',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/keywords/save'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Keywords',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/keywords/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Keywords',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Page scanner.
 */
$router->add(
    UrlHelper::backend($url . '/page-scanner'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'PageScanner',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/page-scanner/add'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'PageScanner',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/page-scanner/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'PageScanner',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/page-scanner/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'PageScanner',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/page-scanner/re-scan/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'PageScanner',
        'action'     => 'rescan',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/page-scanner/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'PageScanner',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/page-scanner/save'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'PageScanner',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/page-scanner/search/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'PageScanner',
        'action'     => 'search',
        'id' => 1
    ]
);


$router->add(
    UrlHelper::backend($url . '/page-scanner/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'PageScanner',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Personas.
 */
$router->add(
    UrlHelper::backend($url . '/personas'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Personas',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/personas/create'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Personas',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/personas/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Personas',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/personas/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Personas',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/personas/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Personas',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/personas/save'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Personas',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/personas/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Personas',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * SEO Campaigns.
 */
$router->add(
    UrlHelper::backend($url . '/seo-campaigns'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SeoCampaigns',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/seo-campaigns/build'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SeoCampaigns',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/seo-campaigns/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SeoCampaigns',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/seo-campaigns/delete/{id}/result/{url}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SeoCampaigns',
        'action'     => 'deleteResult',
        'id' => 1,
        'url' => 2
    ]
);

$router->add(
    UrlHelper::backend($url . '/seo-campaigns/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SeoCampaigns',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/seo-campaigns/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SeoCampaigns',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/seo-campaigns/re-scan/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SeoCampaigns',
        'action'     => 'rescan',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/seo-campaigns/save'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SeoCampaigns',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/seo-campaigns/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SeoCampaigns',
        'action'     => 'update',
        'id' => 1
    ]
);

/*
 * Spinner.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{id}/spinner'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action'     => 'index',
        'type' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{id}/spinner'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action'     => 'index',
        'type' => 1,
        'id' => 2,
        'parent' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{id}/spinner/add'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action'     => 'add',
        'type' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{id}/spinner/add'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action'     => 'add',
        'type' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/clean/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'clean',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'delete',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'delete',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'edit',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'edit',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'recover',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'recover',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{id}/spinner/save'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action'     => 'save',
        'type' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{id}/spinner/save'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action'     => 'save',
        'type' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/set/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'set',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/set/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'set',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/spin/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action'     => 'spinContent',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/spin/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action'     => 'spinContent',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'update',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spin',
        'action' => 'update',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

/*
 * Spun content.
 */
$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/{id}/spun'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action'     => 'index',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/{id}/spun'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action'     => 'index',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/{spinner_id}/spun/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action' => 'delete',
        'type' => 1,
        'page_id' => 2,
        'spinner_id' => 3,
        'id' => 4
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/{spinner_id}/spun/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action' => 'delete',
        'type' => 1,
        'page_id' => 2,
        'spinner_id' => 3,
        'id' => 4
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/{spinner_id}/spun/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action' => 'edit',
        'type' => 1,
        'page_id' => 2,
        'spinner_id' => 3,
        'id' => 4
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/{spinner_id}/export'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SpunExport',
        'action'     => 'index',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/{spinner_id}/export-start'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SpunExport',
        'action'     => 'start',
        'type' => 1,
        'page_id' => 2,
        'id' => 3
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/{spinner_id}/spun/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action' => 'edit',
        'type' => 1,
        'page_id' => 2,
        'spinner_id' => 3,
        'id' => 4
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/{spinner_id}/spun/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action' => 'recover',
        'type' => 1,
        'page_id' => 2,
        'spinner_id' => 3,
        'id' => 4
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/{spinner_id}/spun/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action' => 'recover',
        'type' => 1,
        'page_id' => 2,
        'spinner_id' => 3,
        'id' => 4
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{type}/{page_id}/spinner/{spinner_id}/spun/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action' => 'update',
        'type' => 1,
        'page_id' => 2,
        'spinner_id' => 3,
        'id' => 4
    ]
);

$router->add(
    UrlHelper::backend($config->urls->cms . '/{parent}/{type}/{page_id}/spinner/{spinner_id}/spun/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Spun',
        'action' => 'update',
        'type' => 1,
        'page_id' => 2,
        'spinner_id' => 3,
        'id' => 4
    ]
);

/**
 * Surveys.
 */
$router->add(
    UrlHelper::backend($url . '/surveys'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Surveys',
        'action'     => 'index',
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/create'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Surveys',
        'action'     => 'add',
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Surveys',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Surveys',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Surveys',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/save'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Surveys',
        'action'     => 'save',
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Surveys',
        'action'     => 'update',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/view/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Surveys',
        'action'     => 'view',
        'id' => 1
    ]
);

/**
 * Survey steps.
 */
$router->add(
    UrlHelper::backend($url . '/surveys/{survey_id}/steps/create'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SurveySteps',
        'action'     => 'add',
        'survey_id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/{survey_id}/steps/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SurveySteps',
        'action'     => 'delete',
        'survey_id' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/{survey_id}/steps/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SurveySteps',
        'action'     => 'edit',
        'survey_id' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/{survey_id}/steps/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SurveySteps',
        'action'     => 'recover',
        'survey_id' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/{survey_id}/steps/save'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SurveySteps',
        'action'     => 'save',
        'survey_id' => 1,
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/{survey_id}/steps/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SurveySteps',
        'action'     => 'update',
        'survey_id' => 1,
        'id' => 2
    ]
);

$router->add(
    UrlHelper::backend($url . '/surveys/{survey_id}/view/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'SurveySteps',
        'action'     => 'view',
        'survey_id' => 1,
        'id' => 2
    ]
);

/*
 * Words.
 */
$router->add(
    UrlHelper::backend($url . '/exclude-words'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Words',
        'action'     => 'index'
    ]
);

$router->add(
    UrlHelper::backend($url . '/exclude-words/add'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Words',
        'action'     => 'add'
    ]
);

$router->add(
    UrlHelper::backend($url . '/words/delete/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Words',
        'action'     => 'delete',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/words/delete-all'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Words',
        'action'     => 'deleteAll'
    ]
);

$router->add(
    UrlHelper::backend($url . '/exclude-words/edit/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Words',
        'action'     => 'edit',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/exclude-words/save'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Words',
        'action'     => 'save'
    ]
);

$router->add(
    UrlHelper::backend($url . '/words/recover/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Words',
        'action'     => 'recover',
        'id' => 1
    ]
);

$router->add(
    UrlHelper::backend($url . '/exclude-words/update/{id}'),
    [
        'namespace'  => 'Kytschi\Makabe\Controllers',
        'controller' => 'Words',
        'action'     => 'update',
        'id' => 1
    ]
);
