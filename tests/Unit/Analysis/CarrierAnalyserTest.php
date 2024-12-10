<?php

namespace Analysis;

use App\Service\Analysis\CarrierAnalyser;
use PHPUnit\Framework\TestCase;

class CarrierAnalyserTest extends TestCase
{
    public function test_analyse_returns_most_frequent_carrier()
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $exceptions = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleB.php'],
            ['file' => '/var/www/Carriers/CarrierB/Modules/ModuleA.php']
        ];

        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertEquals('CarrierA', $result);
    }

    public function test_analyse_returns_empty_string_when_no_carrier_found()
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $exceptions = [
            ['file' => '/var/www/Modules/ModuleA.php'],
            ['file' => '/var/www/Modules/ModuleB.php']
        ];

        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertEquals('', $result);
    }

    public function test_analyse_handles_empty_exceptions_array()
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $exceptions = [];

        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertEquals('', $result);
    }

    public function test_analyse_handles_multiple_carriers_with_same_count()
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $exceptions = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Carriers/CarrierB/Modules/ModuleA.php']
        ];

        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertContains($result, ['CarrierA', 'CarrierB']);
    }

    public function test_analyse_ignores_non_matching_paths()
    {
        // Arrange
        $carrierAnalyser = new CarrierAnalyser();
        $exceptions = [
            ['file' => '/var/www/Carriers/CarrierA/Modules/ModuleA.php'],
            ['file' => '/var/www/Other/CarrierB/Modules/ModuleA.php']
        ];

        // Act
        $result = $carrierAnalyser->analyse($exceptions);

        // Assert
        $this->assertEquals('CarrierA', $result);
    }
}
