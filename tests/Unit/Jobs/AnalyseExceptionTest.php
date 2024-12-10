<?php

namespace Jobs;

use App\Jobs\AnalyseException;
use App\Models\Cause;
use App\Service\ExceptionAnalyser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyseExceptionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }


    public function test_handle_detects_spike_and_saves_cause()
    {
        // Arrange
        $exceptionLogs = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleB.php']
        ];
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

    public function test_handle_does_not_save_cause_when_no_spike_detected()
    {
        // Arrange
        $exceptionLogs = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleB.php']
        ];
        $application = 'TestApp';
        $analyser = $this->createMock(ExceptionAnalyser::class);
        $analyser->method('detectSpike')->willReturn(false);

        // Act
        $job = new AnalyseException($exceptionLogs, $application);
        $job->handle($analyser);

        // Assert
        $this->assertDatabaseMissing('causes', ['application' => $application]);
    }

    public function test_handle_handles_empty_exception_logs()
    {
        // Arrange
        $exceptionLogs = [];
        $application = 'TestApp';
        $analyser = $this->createMock(ExceptionAnalyser::class);
        $analyser->method('detectSpike')->willReturn(false);

        // Act
        $job = new AnalyseException($exceptionLogs, $application);
        $job->handle($analyser);

        // Assert
        $this->assertDatabaseMissing('causes', ['application' => $application]);
    }

    private function testCause()
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
            ])
        ]);
    }
}
