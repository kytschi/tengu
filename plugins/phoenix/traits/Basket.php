<?php

/**
 * Basket traits.
 *
 * @package     Kytschi\Phoenix\Traits\Basket
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Phoenix\Traits;

use Kytschi\Phoenix\Controllers\BasketController;

trait Basket
{
    public function getBasket()
    {
        return (new BasketController())->get();
    }

    public function getBasketCount()
    {
        if ($basket = (new BasketController())->get()) {
            return $basket->quantity;
        }

        return 0;
    }
}
