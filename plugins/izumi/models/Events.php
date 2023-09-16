<?php

/**
 * Events model.
 *
 * @package     Kytschi\Izumi\Models\Events
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

namespace Kytschi\Izumi\Models;

use Kytschi\Tengu\Models\Core\Users;
use Kytschi\Tengu\Models\Model;
use Kytschi\Tengu\Models\Website\Pages;

class Events extends Model
{
    public $page_id;
    public $event_on;
    public $event_end;
    public $recurring;
    public $location;
    public $external_contact_form;
    public $external_booking_form;
    public $price;
    public $pricing_type;
    public $fee;
    public $ical_url='';

    public function initialize()
    {
        $this->setSource('izumi_events');

        $this->hasOne(
            'page_id',
            Pages::class,
            'id',
            [
                'alias'    => 'page',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'updated_by',
            Users::class,
            'id',
            [
                'alias'    => 'updated',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'created_by',
            Users::class,
            'id',
            [
                'alias'    => 'created',
                'reusable' => true
            ]
        );

        $this->hasOne(
            'deleted_by',
            Users::class,
            'id',
            [
                'alias'    => 'deleted',
                'reusable' => true
            ]
        );
    }

    public function afterFetch()
    {
        parent::afterFetch();
        $this->ical_url = ($this->getConfig())->application->dumpUri . $this->id . '-event.ics';
    }
}
