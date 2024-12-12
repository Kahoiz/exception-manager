<?php

namespace App\Service\Notification;

class NotificationData
{
    public string $title;
    public string $message;
    public string $application; // Application name;
    public ?string $context; // Optional contextual information

    public ?string $carrier;
    public array $fields;    // Key-value pairs for additional data

    public function __construct(string $title,
                                string $application,
                                string $message,
                                ?string $context = null,
                                ?string $carrier = null,
                                array $fields = [])
    {
        $this->title = $title;
        $this->message = $message;
        $this->context = $context;
        $this->fields = $fields;
        $this->application = $application;
        $this->carrier = $carrier;
    }
}
