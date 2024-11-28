<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\SlackAlerts\Facades\SlackAlert;

class TestSlackAlert extends Command
{
    protected $signature = 'slack:test';
    protected $description = 'Test SlackAlert functionality';

    public function handle(): void
    {
        SlackAlert::message('This is a test message from SlackAlert.');
        $this->info('Test message sent to Slack.');
    }
}
