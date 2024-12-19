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
        // create header message
        $headerMessage = $this->createHeaderMessage($notifiable);

        $message = (new SlackMessage)
            ->headerBlock(':rotating_light: ' . $headerMessage . ':rotating_light:')
            ->sectionBlock(function (SectionBlock $section) use ($notifiable) {
                $section->text('Application: ' . $notifiable->application);
            })
            ->dividerBlock()
            ->contextBlock(function (ContextBlock $context) use ($notifiable) {
                $context->text('Total Exception Count: ' . $notifiable->amount);
            });

        if (isset($notifiable->data['carrier'])) {
            $message->contextBlock(function (ContextBlock $context) use ($notifiable) {
                $context->text('Carrier: ' . $notifiable->data['carrier']);
            });
        }

        $this->addCauseSection($message, $notifiable->data);

        $this->addSimpleTypeSection($message, $notifiable->data);

        $containsString = false;
        $searchString = 'RequestException';

        foreach (array_keys($notifiable->data) as $key) {
            if (str_contains($key, $searchString)) {
                $containsString = true;
                break;
            }
        }

        if ($containsString) {
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

    private function createHeaderMessage(object $notifiable): string
    {
        $headerMessage = 'no header found';

        if (count($notifiable->data['causes']) > 1) {
            $headerMessage = 'Multiple causes detected';
        } else {
            $headerMessage = array_key_first($notifiable->data['causes']);
        }

        return $headerMessage;
    }

    private function addCauseSection(SlackMessage $message, array $data): void
    {
        $message->sectionBlock(function (SectionBlock $section) use ($data) {
            $section->text("*Causes:*")->markdown();
            $causes = '';
            // flatten the array so we can loop through it
            foreach ($data['causes'] as $key => $item) {
                $causes .= "$key: $item\n";
            }
            $section->field($causes);
        });
    }

    private function addSimpleTypeSection(SlackMessage $message, array $data): void
    {
        $message->sectionBlock(function (SectionBlock $section) use ($data) {
            $section->text("*Top Errors:*")->markdown();
            $errors = '';
            // loop through the types and add them to the message
            foreach ($data['types'] as $key => $error) {
                $errors .= "$key: $error\n";
            }
            $section->field($errors);
        });
    }
}
