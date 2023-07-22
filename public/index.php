<?php

/**
 * Index.
 *
 * @copyright   2023 Kytschi
 * @version     0.0.1
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

//Let tengu handle the errors
error_reporting(0);
@ini_set('display_errors', 0);

define('TENGU_START_TIME', microtime(true) * 1000);

use Phalcon\Di\FactoryDefault;

/*function fatal_error()
{
    $error = error_get_last();
    echo "<p><strong>Error:</strong><br/>" . nl2br($error['message']) . "</p>";
}

register_shutdown_function('fatal_error');*/

try {
    define('BASE_PATH', dirname(getcwd()));
    define('APP_PATH', BASE_PATH . '/tengu');

    /*
     * Load the autoloader from composer.
     */
    include APP_PATH . '/../vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();

    if (!empty($_SERVER['REQUEST_URI'])) {
        if (
            substr(
                $_SERVER['REQUEST_URI'],
                0,
                strlen($_ENV['APP_TENGU_URL'])
            ) == $_ENV['APP_TENGU_URL']
        ) {
            define('TENGU_BACKEND', true);
        } else {
            define('TENGU_BACKEND', false);
        }

        $parsed = parse_url($_SERVER['REQUEST_URI']);
        if (!empty($parsed['path'])) {
            $path = '/' . trim(ltrim($parsed['path'], $_ENV['APP_TENGU_URL']), '/');
        } else {
            $path = '/';
        }

        if (strpos($path, $_ENV['APP_TENGU_API_URL']) !== false) {
            define('TENGU_API', true);
        } else {
            define('TENGU_API', false);
        }
    } else {
        define('TENGU_BACKEND', false);
        define('TENGU_API', false);
    }

    /*
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();
    
    /*
     * Read services
     */
    include APP_PATH . '/config/services.php';

    /*
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /*
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /*
     * Handle routes
     */
    include APP_PATH . '/config/router.php';

    /*
     * Define the tengu var.
     */
    $controller = new Kytschi\Tengu\Controllers\TenguController();
    $di->setShared('tengu', $controller);

    /*
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);
    $render = $application->handle($_SERVER['REQUEST_URI'])->getContent();
    echo $render;
} catch (Kytschi\Tengu\Exceptions\ValidationException $err) {
    if (TENGU_API) {
        (new Kytschi\Tengu\Controllers\IndexController())->apiError($err);
    }

    $url = $err->getUrl();
    if (!empty($application->session)) {
        $application->session->set('error', $err);
    } else {
        $url = Kytschi\Tengu\Helpers\UrlHelper::append($url, 'tengu_error=' . urlencode($err->getMessage()));
    }

    (new Kytschi\Tengu\Controllers\TenguController())->redirect($url);
} catch (Kytschi\Tengu\Exceptions\SaveException $err) {
    if (TENGU_API) {
        (new Kytschi\Tengu\Controllers\IndexController())->apiError($err);
    }

    if (!empty($application->session)) {
        $application->session->set('error', $err);
    }

    echo $application->handle('/errors/save')->getContent();
} catch (Kytschi\Tengu\Exceptions\AuthorisationException $err) {
    if (TENGU_API) {
        (new Kytschi\Tengu\Controllers\IndexController())->apiError($err, 403);
    }

    if (!empty($application->session)) {
        $application->session->set('error', $err);
    }

    if (TENGU_BACKEND) {
        $url = rtrim($_ENV['APP_TENGU_URL'], '/') . '/errors/access-denined';
    } else {
        $url = '/errors/access-denined';
    }
    echo $application->handle($url)->getContent();
} catch (Kytschi\Tengu\Exceptions\CustomerException $err) {
    if (TENGU_API) {
        (new Kytschi\Tengu\Controllers\IndexController())->apiError($err);
    }

    include BASE_PATH . '/tengu/views/generic/errors/customer.phtml';
    die();
} catch (\Exception $err) {
    if (TENGU_API) {
        (new Kytschi\Tengu\Controllers\IndexController())->apiError($err);
    }

    if (method_exists($err, 'isJson')) {
        if ($err->isJson()) {
            $err->outputJson();
        }
    }

    if (strpos($_SERVER['REQUEST_URI'], '/errors/critical') === false && !empty($application)) {
        if (!empty($application)) {
            if (!empty($application->session)) {
                $application->session->set('error', $err);
            }
        }
        
        if (TENGU_BACKEND) {
            $url = rtrim($_ENV['APP_TENGU_URL'], '/') . '/errors/critical';
        } else {
            $url = '/errors/critical';
        }

        $controller = new Kytschi\Tengu\Controllers\Core\FormController();

        try {
            $controller->addLog(
                'system',
                '',
                'error',
                $err->getMessage(),
                json_encode($err)
            );
        } catch (\Exception $err) {
            echo $err->getMessage();
        }
        echo $err->getMessage();
    } else {
        if (!empty($_ENV['APP_DEBUG'])) {
            if ($_ENV['APP_DEBUG'] == 'true') {
                echo $err->getMessage();
                $data = method_exists($err, 'getData') ? $err->getData() : null;
                if ($data) {
                    echo '<pre>';
                    var_dump($data);
                    echo '</pre>';
                }
                echo '<pre>';
                var_dump($err->getTraceAsString());
                echo '</pre>';
                die();
            }
        }

        echo 'A fatal error has occurred';
        die();
    }
} /*finally {
    echo 'fatal error';
}*/
