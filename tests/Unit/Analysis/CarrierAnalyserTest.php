<?php

namespace Analysis;

use App\Service\Analysis\CarrierAnalyser;
use PHPUnit\Framework\TestCase;

class CarrierAnalyserTest extends TestCase
{
    public function test_analyse_returns_most_frequent_carrier(): void
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $data = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleB.php'],
            ['file' => '/var/www/Carriers/CarrierB/Modules/ModuleA.php']
        ];

        $exceptions = collect($data);


        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertEquals('CarrierA', $result);
    }

    public function test_analyse_returns_empty_string_when_no_carrier_found(): void
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $data = [
            ['file' => '/var/www/Modules/ModuleA.php'],
            ['file' => '/var/www/Modules/ModuleB.php']
        ];
        $exceptions = collect($data);

        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertEquals('', $result);
    }

    public function test_analyse_handles_empty_exceptions_array(): void
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $exceptions = collect();

        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertEquals('', $result);
    }

    public function test_analyse_handles_multiple_carriers_with_same_count(): void
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $data = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Carriers/CarrierB/Modules/ModuleA.php']
        ];
        $exceptions = collect($data);


        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertContains($result, ['CarrierA', 'CarrierB']);
    }

    public function test_analyse_ignores_non_matching_paths(): void
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $data = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Other/CarrierB/Modules/ModuleA.php']
        ];
        $exceptions = collect($data);


        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertEquals('CarrierA', $result);
    }
}
