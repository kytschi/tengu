<?php

/**
 * Umi cron.
 *
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2022 Kytschi - All Rights Reserved.
 * Unauthorised copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 */

use Kytschi\Umi\Controllers\NotificationsController;

(new NotificationsController())->trigger();
