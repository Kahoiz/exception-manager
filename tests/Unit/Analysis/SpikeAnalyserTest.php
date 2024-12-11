<?php

namespace Analysis;

use App\Service\Analysis\SpikeAnalyser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SpikeAnalyserTest extends TestCase
{
    use RefreshDatabase;
    private SpikeAnalyser $spikeAnalyser;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize the SpikeAnalyser instance
        $this->spikeAnalyser = new SpikeAnalyser();
    }

    public static function spikeDetectionDataProvider(): array
    {
        return [
            // the last element in the collection, will be asserted
            [collect([10, 10, 10, 10, 10, 10]), false], // no spike detected
            [collect([10, 10, 10, 10, 100]), true], // spike detected
            [collect([10, 10, 10, 0]), false], // edge case
        ];
    }

    /**
     * @dataProvider spikeDetectionDataProvider
     */
    public function test_spike_detection_should_return_expected_value(Collection $exceptionCount, bool $expectedResult): void
    {
        $amountOfTimesToRun = $exceptionCount->count();
        $result = false;

        for($i = 0; $i < $amountOfTimesToRun; $i++)
        {
            // Create a collection of exceptions
            $exceptions = $this->createTestData($exceptionCount[$i]);

            // Call the detectSpike method
            $result = $this->spikeAnalyser->detectSpike($exceptions, 'TestApplication');
        }

        // Assert the result
        $this->assertEquals($expectedResult, $result);
    }

    public function test_spike_detection_with_wrong_datatype_should_throw_error(): void
    {
        // invalid data type
        $exceptions = 'Invalid data type';

        $this->expectException(\Error::class);

        // Call the detectSpike method
        $this->spikeAnalyser->detectSpike($exceptions, 'TestApplication');
    }

    private function createTestData(int $amount) : Collection
    {
        $data = collect();
        for($i = 0; $i < $amount; $i++)
        {
            $data[$i] = ['type' => 'ExceptionType' . $i];
        }
        return $data->collect();
    }
}
