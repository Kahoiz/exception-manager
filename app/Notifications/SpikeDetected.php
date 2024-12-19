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


    private bool $requestException = false; //we use this to determine if we should show the health check message
    private bool $carrierException = false; //we use this to determine if we should show the health check message

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

        $this->addCauseSection($message, $notifiable);

        $this->addSimpleTypeSection($message, $notifiable);


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

    private function addCauseSection($message, $notifiable): void
    {
        $message->sectionBlock(function (SectionBlock $section) use ($notifiable, &$requestException) {
            $section->text("*Causes:*")->markdown();
            $causes = '';
            // flatten the array so we can loop through it
            foreach ($notifiable->data['causes'] as $key => $item) {
                $causes .= "$key: $item\n";
            }
            $section->field($causes);
        });
    }

    private function addSimpleTypeSection($message, $notifiable): void
    {
        $message->sectionBlock(function (SectionBlock $section) use ($notifiable) {
            $section->text("*Top Errors:*")->markdown();
            $errors = '';
            // loop through the types and add them to the message
            foreach ($notifiable->data['types'] as $key => $error) {
                if (str_contains($key, 'RequestException')) {
                    $this->requestException = true;
                }
                $errors .= "$key: $error\n";
            }
            $section->field($errors);
        });
    }
}
