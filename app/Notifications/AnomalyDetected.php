<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Notifications\Notification;

class AnomalyDetected extends Notification implements ShouldQueue
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

        $headerMessage = count($notifiable->data['anomalies']) > 1 ? 'Multiable anomalies Detected' :
            $notifiable->data['anomalies']->keys()->first() . ' Detected';

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

        $this->addAnomaliesSection($message, $notifiable->data);

        $this->addSimpleTypeSection($message, $notifiable->data);

        $containsString = false;
        $searchString = 'RequestException';

        foreach (array_keys($notifiable->data['types']) as $key) {
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

    private function addAnomaliesSection(SlackMessage $message, array $anomalies): void
    {
        $message->sectionBlock(function (SectionBlock $section) use ($anomalies) {
            $section->text("*Anomalies:*")->markdown();
            $anomaly = '';
            // flatten the array so we can loop through it
            foreach ($anomalies as $key => $item) {
                $anomaly .= "$key: $item\n";
            }
            $section->field($anomaly);
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
