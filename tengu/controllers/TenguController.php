<?php

/**
 * Tengu controller.
 *
 * @package     Kytschi\Tengu\Controllers\TenguController
 * @copyright   2022 Kytschi
 * @version     0.0.2
 *
 * Copyright Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

declare(strict_types=1);

namespace Kytschi\Tengu\Controllers;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException;
use Kytschi\Tengu\Controllers\Core\FormController;
use Kytschi\Tengu\Controllers\Website\BlogPostsController;
use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Controllers\Website\PortfolioController;
use Kytschi\Tengu\Controllers\Website\ThemesController;
use Kytschi\Tengu\Exceptions\CustomerException;
use Kytschi\Tengu\Exceptions\GenericException;
use Kytschi\Tengu\Helpers\UrlHelper;
use Kytschi\Tengu\Models\Website\Settings;
use Kytschi\Tengu\Models\Website\Themes;
use Kytschi\Tengu\Traits\Core\Filters;
use Kytschi\Tengu\Traits\Core\Tags;
use Kytschi\Tengu\Traits\Core\User;
use Phalcon\Security\Random;
use Phalcon\Tag;

class TenguController
{
    use Filters;
    use Tags;
    use User;

    public $blog = null;
    public $portfolio = null;
    public $pages = null;
    public $settings = null;
    public $theme = null;
    public $version = '0.0.3 alpha';

    public $filters = [];

    public function __construct()
    {
        $this->blog = new BlogPostsController();
        $this->portfolio = new PortfolioController();
        $this->pages = new PagesController();

        $this->theme = new ThemesController();

        $this->settings = (new Settings())->findFirst(
            [
                'reusable' => true,
                'limit' => '1'
            ]
        );

        if (empty($this->settings)) {
            $this->settings = new Settings();
        }

        $this->theme->getActive($this->settings);

        define('TENGU_CURRENCY', $this->settings->finance->currency);
    }

    public function canonicalUrl()
    {
        try {
            if ($url = Tag::getValue('canonical_url')) {
                return (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $url;
            }

            $path = parse_url($_SERVER['REQUEST_URI']);
            $url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $path['path'];
            return $url;
        } catch (\Exception $err) {
            throw new GenericException($err->getMessage());
        }
    }

    public function captcha($colour = '000000')
    {
        $colour = ltrim($colour, '#');

        if (strlen($colour) == 6) {
            $red = hexdec(substr($colour, 0, 2));
            $green = hexdec(substr($colour, 2, 2));
            $blue = hexdec(substr($colour, 4, 2));
        } elseif (strlen($colour) == 3) {
            $red = hexdec(substr($colour, 0, 1));
            $green = hexdec(substr($colour, 1, 1));
            $blue = hexdec(substr($colour, 3, 1));
        } else {
            $red = 0;
            $green = 0;
            $blue = 0;
        }

        $controller = new FormController();

        $width = 340;
        $height = 100;

        $image = imagecreatetruecolor($width, $height);
        imagesavealpha($image, true);

        $trans_colour = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $trans_colour);

        $keyspace = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < 7; ++$i) {
            if (random_int(0, 1)) {
                $letter = $keyspace[random_int(0, $max)];
            } else {
                $letter = strtolower($keyspace[random_int(0, $max)]);
            }
            $pieces[] = $letter;
        }
        $captcha = implode('', $pieces);

        $font_colour = imagecolorallocate($image, $red, $green, $blue);
        imagefttext(
            $image,
            38,
            0,
            15,
            65,
            $font_colour,
            $controller->di->getConfig()->application->tenguAssetDir . '/fonts/captcha.ttf',
            $captcha
        );

        for ($iLoop = 0; $iLoop < 20; $iLoop++) {
            $line_color = imagecolorallocatealpha($image, $red, $green, $blue, rand(10, 100));
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
        }

        ob_start();
        imagepng($image);
        $image_data = ob_get_contents();
        ob_end_clean();

        mt_srand(mt_rand(100000, 999999));
        $encrypted = @openssl_encrypt(
            '_TENGU_CAPTCHA=' . $captcha . '=' . mt_rand(0, 99999999),
            'aes128',
            $captcha,
            $options = 0
        );

        echo '<div class="captcha-img"><img src="data:image/png;base64,' .
            \base64_encode($image_data) .
            '" alt="captcha"/></div>';
        echo '<input name="captcha" class="form-control" value="" required/>';
        echo '<input name="_TENGU_CAPTCHA" type="hidden" value="' . $encrypted . '"/>';

        imagedestroy($image);
    }

    public function captchaValidate($captcha)
    {
        if (!$captcha) {
            return false;
        }

        $token = openssl_decrypt(
            $_REQUEST['_TENGU_CAPTCHA'],
            'aes128',
            $captcha,
            $options = 0
        );

        if (!$token) {
            return false;
        }

        $splits = explode('=', $token);

        if ($splits[0] != '_TENGU_CAPTCHA') {
            return false;
        }

        if ($splits[1] != $captcha) {
            return false;
        }

        return true;
    }

    public function redirect(string $url, $pagination = true)
    {
        header('Location: ' . ($pagination ? UrlHelper::generate($url) : $url));
        exit;
    }
}
