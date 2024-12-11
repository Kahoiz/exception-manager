<?php

namespace Jobs;

use App\Jobs\AnalyseException;
use App\Models\Cause;
use App\Service\ExceptionAnalyser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JsonException;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

class AnalyseExceptionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }


    /**
     * @throws Exception
     * @throws JsonException
     */
    public function test_handle_detects_spike_and_saves_cause(): void
    {
        // Arrange
        $data = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleB.php']
        ];
        $exceptionLogs = collect($data);
        $application = 'TestApp';
        $cause = $this->testCause();
        $analyser = $this->createMock(ExceptionAnalyser::class);
        $analyser->method('detectSpike')->willReturn(true);
        $analyser->method('identifyCause')->willReturn($cause);

        // Act
        $job = new AnalyseException($exceptionLogs, $application);
        $job->handle($analyser);

        // Assert
        $this->assertDatabaseHas('causes', ['application' => $application]);
    }

    /**
     * @throws Exception
     */
    public function test_handle_does_not_save_cause_when_no_spike_detected(): void
    {
        // Arrange
        $data = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleB.php']
        ];
        $exceptionLogs = collect($data);
        $application = 'TestApp';
        $analyser = $this->createMock(ExceptionAnalyser::class);
        $analyser->method('detectSpike')->willReturn(false);

        // Act
        $job = new AnalyseException($exceptionLogs, $application);
        $job->handle($analyser);

        // Assert
        $this->assertDatabaseMissing('causes', ['application' => $application]);
    }

    /**
     * @throws Exception
     */
    public function test_handle_handles_empty_exception_logs(): void
    {
        // Arrange
        $exceptionLogs = collect();
        $application = 'TestApp';
        $analyser = $this->createMock(ExceptionAnalyser::class);
        $analyser->method('detectSpike')->willReturn(false);

        // Act
        $job = new AnalyseException($exceptionLogs, $application);
        $job->handle($analyser);

        // Assert
        $this->assertDatabaseMissing('causes', ['application' => $application]);
    }

    /**
     * @throws JsonException
     */
    private function testCause(): Cause
    {
        return new Cause([
            'application' => 'TestApp',
            'data' => json_encode([
                'types' => [
                    'InvalidArgumentException',
                    'App\Exceptions\CarrierException',
                    'OverflowException',
                    'UnexpectedValueException',
                    'App\Exceptions\TestException'
                ],
                'carrier' => 'DHL'
            ], JSON_THROW_ON_ERROR)
        ]);
    }
}
