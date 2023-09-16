<?php

/**
 * Events controller.
 *
 * @package     Kytschi\Izumi\Controllers\EventsController
 * @copyright   2023 Mike Welsh <mike@kytschi.com>
 * @version     0.0.1
 *
 * Copyright 2023 Mike Welsh

 */

declare(strict_types=1);

namespace Kytschi\Izumi\Controllers;

use Kytschi\Izumi\Models\Events;
use Kytschi\Tengu\Controllers\Core\PostcodesController;
use Kytschi\Tengu\Controllers\Website\PagesController;
use Kytschi\Tengu\Exceptions\RequestException;
use Kytschi\Tengu\Exceptions\SaveException;
use Kytschi\Tengu\Exceptions\ValidationException;
use Kytschi\Tengu\Helpers\DateHelper;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;

class EventsController extends PagesController
{
    public $access = [
        'administrator',
        'super-user'
    ];

    public $global_url  = '/events';
    public $resource = 'event';

    public function initialize()
    {
        $this->global_url = ($this->di->getConfig())->urls->ems . $this->global_url;
        $this->global_add_url = $this->global_url . '/create';
    }

    public function addAction($title = 'Add an event', $template = 'website/pages/add')
    {
        parent::addAction($title, $template);
    }

    public function editAction($id, $title = 'Editing the event', $template = 'website/pages/edit')
    {
        parent::editAction($id, $title, $template);
    }

    public function indexAction($title = 'Our events', $template = 'website/pages/index')
    {
        return parent::indexAction($title, $template);
    }

    public function saveSubAction($model)
    {
        $this->subValidate();

        $model = $this->setSubData($model);

        if ($model->update() === false) {
            throw new SaveException(
                'Failed to update the ' . str_replace('-', ' ', $this->resource),
                $model->getMessages()
            );
        }
    }

    private function setSubData($model)
    {
        $event = Events::findFirst([
            'conditions' => 'page_id=:page_id:',
            'bind' => ['page_id' => $model->id]
        ]);

        $save = false;
        if (empty($event)) {
            $event = new Events();
            $save = true;
        }

        $postcode = $model->postcode;

        $event->page_id = $model->id;
        $event->event_on = DateHelper::sql($_POST['event_on']);
        $event->event_end = !empty($_POST['event_end']) ? DateHelper::sql($_POST['event_end']) : null;
        $event->recurring = !empty($_POST['event_recurring']) ? $_POST['event_recurring'] : null;
        $event->location = !empty($_POST['event_location']) ? $_POST['event_location'] : null;
        $event->price = !empty($_POST['event_price']) ? floatval($_POST['event_price']) : null;
        $event->pricing_type = !empty($_POST['event_pricing_type']) ? $_POST['event_pricing_type'] : null;
        $event->external_contact_form = !empty($_POST['external_contact_form']) ?
            $_POST['external_contact_form'] :
            null;
        $event->fee = !empty($_POST['event_fee']) ? floatval($_POST['event_fee']) : null;
        $event->external_booking_form = !empty($_POST['external_booking_form']) ?
            $_POST['external_booking_form'] :
            null;

        $model->postcode = !empty($_POST['postcode']) ? $_POST['postcode'] : null;
        if (!empty($model->postcode) && $postcode != $model->postcode) {
            if (
                !empty($coords = (new PostcodesController())->getCoordinates($model->postcode))
            ) {
                $model->longitude = $coords['longitude'];
                $model->latitude = $coords['latitude'];
            }
        }

        if ($save) {
            if ($event->save() === false) {
                throw new SaveException(
                    'Failed to save the event data',
                    $event->getMessages()
                );
            }
        } elseif ($event->update() === false) {
            throw new SaveException(
                'Failed to update the event data',
                $event->getMessages()
            );
        }

        $ical = "BEGIN:VCALENDAR\n";
        $ical .= "VERSION:2.0\n";
        $ical .= "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";
        $ical .= "CALSCALE:GREGORIAN\n";
        $ical .= "BEGIN:VEVENT\n";
        if ($event->event_end) {
            $ical .= "DTEND:". DateHelper::ical($event->event_end) . "\n";
        }
        $ical .= "UID:" . $event->id . "\n";
        $ical .= "DTSTAMP:". DateHelper::ical(date("Y-m-d H:i:s")) . "\n";
        if ($event->location) {
            $ical .= "LOCATION:" . str_replace(["\n", "\r\n", "\r"], " ", $event->location);
            if ($model->postcode) {
                $ical .= " " . $model->postcode;
            }
            $ical .= "\n";
        }
        if ($model->longitude && $model->latitude) {
            $ical .= "GEO:" . $model->latitude . ";" . $model->longitude . "\n";
        }
        $settings = $this->getSettings();
        if ($settings->contact_email) {
            $ical .= "ORGANIZER;CN=" . $settings->name;
            $ical .= ":MAILTO:" . $settings->contact_email;
        }
        $ical .= "\n";
        $ical .= "DESCRIPTION:" . $model->name . "\n";
        //$ical .= "URL;VALUE=URI:\n";
        if ($model->summary) {
            $ical .= "SUMMARY:" . str_replace(["\n", "\r\n", "\r"], " ", $model->summary). "\n";
        }
        $ical .= "DTSTART:". DateHelper::ical($event->event_on) . "\n";
        $ical .= "END:VEVENT\n";
        $ical .= "END:VCALENDAR\n";

        file_put_contents(
            ($this->di->getConfig())->application->dumpDir . $event->id . '-event.ics',
            $ical
        );

        return $model;
    }

    public function subValidate()
    {
        if (empty($_POST)) {
            throw new RequestException('Invalid post data');
        }

        $this->saveFormData($_POST);

        $validation = new Validation();

        $validation->add(
            'event_on',
            new PresenceOf(
                [
                    'message' => 'The event date is required',
                ]
            )
        );

        $messages = $validation->validate($_POST);
        if (count($messages)) {
            throw new ValidationException('Form validation failed, please double check the required fields', $messages);
        }
    }

    public function updateSubAction($model)
    {
        $this->subValidate();

        $model = $this->setSubData($model);

        if ($model->update() === false) {
            throw new SaveException(
                'Failed to update the ' . str_replace('-', ' ', $this->resource),
                $model->getMessages()
            );
        }
    }
}
