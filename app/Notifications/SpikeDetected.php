<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Notification;

class SpikeDetected extends Notification implements ShouldQueue
{
    use Queueable;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['slack'];
    }


    /**
     * @throws \JsonException
     */

    public function toSlack(object $notifiable): SlackMessage
    {
        $requestException = false;
        $message = (new SlackMessage)
            ->headerBlock(':rotating_light: Spike detected :rotating_light:')
            ->sectionBlock(function (SectionBlock $section) {
                $section->text('Application: ' . $this->data['Application']);
            })
            ->dividerBlock()
            ->contextBlock(function (ContextBlock $context) {
                $context->text('Total Exception Count: ' . $this->data['Total Exception Count']);
            })
            ->sectionBlock(function (SectionBlock $section) use (&$requestException) {
                $section->text("*Top Errors:*")->markdown();
                $errors = '';
                foreach ($this->data['Top Errors'] as $key => $error) {
                    if (str_contains($key, 'RequestException')) {
                        $requestException = true;
                    }
                    $errors .= "$key: $error\n";
                }
                $section->field($errors);
            });

        if (isset($this->data['Carrier'])) {
            $message->contextBlock(function (ContextBlock $context) {
                $context->text('Carrier: ' . $this->data['Carrier']);
            });
        }
        if ($requestException) {
            $message->contextBlock(function (ContextBlock $context) {
                $context->text("Health Check Deployed to check Carrier API's")->markdown();
            });
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

}
