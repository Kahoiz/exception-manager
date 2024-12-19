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

    public function __construct(
        string  $application,
        ?string $carrier = null,
        array   $fields = [])
    {
        $this->fields = $fields;
        $this->application = $application;
        $this->carrier = $carrier;
    }
}
