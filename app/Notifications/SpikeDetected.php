<?php

namespace App\Notifications;

use App\Models\Cause;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Notification;

class SpikeDetected extends Notification implements ShouldQueue
{
    use Queueable;


    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
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
            ->sectionBlock(function (SectionBlock $section) use ($notifiable) {
                $section->text('Application: ' . $notifiable->application);
            })
            ->dividerBlock()
            ->contextBlock(function (ContextBlock $context) use ($notifiable) {
                $context->text('Total Exception Count: ' . $notifiable->amount);
            })
            ->sectionBlock(function (SectionBlock $section) use ($notifiable, &$requestException) {
                $section->text("*Top Errors:*")->markdown();
                $errors = '';
                foreach ($notifiable->data['types'] as $key => $error) {
                    if (str_contains($key, 'RequestException')) {
                        $requestException = true;
                    }
                    $errors .= "$key: $error\n";
                }
                $section->field($errors);
            });

        if (isset($notifiable->data['carrier'])) {
            $message->contextBlock(function (ContextBlock $context) use ($notifiable) {
                $context->text('Carrier: ' . $notifiable->data['carrier']);
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
