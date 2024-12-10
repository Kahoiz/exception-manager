<?php

namespace Tests\Feature;

use App\Models\ExceptionLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithConsoleEvents;
use Mockery;
use PHPUnit\Framework\Assert;
use Tests\TestCase;
use Tests\TestHelper;


class ProcessNewExceptionsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Fake the queue testing done in the test helper class
        TestHelper::mockQueueFacades();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_it_processes_no_new_exceptions_gracefully()
    {
        // Act
        $this->artisan('mq:process')->assertExitCode(0);
    }

    public function test_it_processes_new_exceptions_with_valid_data($data = [])
    {
        // Arrange
        // Create valid test data if none is provided
        if(empty($data)){
            $data = $this->createValidTestData(10);
        }
        TestHelper::mockQueue($data);

        // Act
        $this->artisan('mq:process')->assertExitCode(0);

        // Assert
        $this->assertDataIsInsertedIntoDatabase($data);
    }

    public function test_it_handles_invalid_data_gracefully_without_processing()
    {
        // Arrange
        $amountOfExceptionsInDB = ExceptionLog::count();
        $data = $this->createInvalidData(10);
        TestHelper::mockQueue($data);

        // Act
        $this->artisan('mq:process')->assertExitCode(0);

        // Assert
        //there should be no new exceptions in the database
        assert::assertEquals($amountOfExceptionsInDB, ExceptionLog::count());
    }

    public function test_processes_exceptions_within_limit_gracefully()
    {
        // Arrange
        $data = $this->createValidTestData(500);
        TestHelper::mockQueue($data);

        $this->artisan('mq:process', ['--limit' => 500])->assertExitCode(0);
        $this->assertDataIsInsertedIntoDatabase($data);
    }

    public function test_processes_exceptions_with_limit_exceeding_max_gracefully()
    {
        // Arrange
        $data = $this->createValidTestData(600);
        TestHelper::mockQueue($data);

        // Assert
        $this->artisan('mq:process', ['--limit' => 600])->assertExitCode(0);
        $this->assertDataIsInsertedIntoDatabase($data);
    }

    public function test_processes_exceptions_with_limit_below_zero_gracefully()
    {
        // Arrange
        $data = $this->createValidTestData(10);
        TestHelper::mockQueue($data);

        // Assert
        $this->artisan('mq:process', ['--limit' => -1])->assertExitCode(0);
        $this->assertDataIsInsertedIntoDatabase($data);
    }

    private function createValidTestData($amount)
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

        return $data;
    }

    private function createInvalidData(int $amount){
        // create data collection
        $data = collect();
        for($i = 0; $i < 10; $i++) {
            $data[$i] = (object)[
                "invalid data",
                123,
                false
            ];
        }

        return $data;
    }

    private function assertDataIsInsertedIntoDatabase($data)
    {
        $data->each(function ($item) {
            $this->assertDatabaseHas('exception_logs', [
                'uuid' => $item->uuid,
                'message' => $item->message,
                'type' => $item->type,
                'code' => $item->code,
                'file' => $item->file,
                'line' => $item->line,
                'trace' => $item->trace,
                'user_id' => $item->user_id,
                'application' => $item->application,
                'thrown_at' => $item->thrown_at,
            ]);
        });
    }
}
