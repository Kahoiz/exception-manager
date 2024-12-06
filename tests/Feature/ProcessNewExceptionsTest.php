<?php

namespace Tests\Feature;

use App\Console\Commands\ProcessNewExceptions;
use App\Jobs\PersistException;
use App\Jobs\AnalyseException;
use App\Models\ExceptionLog;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProcessNewExceptionsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Fake the queue so we can simulate the state of the queue
        Queue::fake();
        Bus::fake();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_it_processes_new_exceptions()
    {
        // create data collection
        $data = collect();
        for($i = 0; $i < 10; $i++) {
            $data[$i] = ExceptionLog::create([
                'type' => 'error',
                'code' => 500,
                'message' => 'Error message ' . $i,
                'file' => 'app/Exceptions/Handler.php',
                'line' => 42,
                'trace' => 'Line 42 in app/Exceptions/Handler.php',
                'uuid' => '1234-' . $i,
                'user_id' => 1,
                'application' => 'app1',
                'thrown_at' => now(),
            ]);
        }

        $data->each(function($log) {
            $message = Json::encode(['payload' => $log->toArray()]);
            Queue::pushRaw('new-exception', $message);
        });

        // ensure the data is in the queue
        $this->assertEquals(10, Queue::size('new-exception'));
    }
}
