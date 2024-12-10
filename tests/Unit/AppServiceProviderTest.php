<?php

namespace Tests\Unit;

use App\Service\Analysis\Interfaces\CarrierAnalyserInterface;
use App\Service\Analysis\Interfaces\SpikeAnalyserInterface;
use App\Service\Analysis\Interfaces\TypeAnalyserInterface;
use Tests\TestCase;
use Illuminate\Support\Collection;
use App\Service\Analysis\CarrierAnalyser;
use App\Service\Analysis\SpikeAnalyser;
use App\Service\Analysis\TypeAnalyser;
use App\Service\ExceptionAnalyser;
use App\Providers\AppServiceProvider;

class AppServiceProviderTest extends TestCase
{
    public function test_registers_exception_analyser_as_singleton()
    {
        // Arrange
        $this->app->register(AppServiceProvider::class);

        // Act
        $exceptionAnalyser1 = $this->app->make(ExceptionAnalyser::class);
        $exceptionAnalyser2 = $this->app->make(ExceptionAnalyser::class);

        // Assert
        $this->assertSame($exceptionAnalyser1, $exceptionAnalyser2);
    }

    public function test_binds_type_analyser_interface_to_type_analyser()
    {
        // Arrange
        $this->app->register(AppServiceProvider::class);

        // Act
        $typeAnalyser = $this->app->make(TypeAnalyserInterface::class);
        // Assert
        $this->assertInstanceOf(TypeAnalyser::class, $typeAnalyser);
    }

    public function test_binds_carrier_analyser_interface_to_carrier_analyser()
    {
        // Arrange
        $this->app->register(AppServiceProvider::class);

        // Act
        $carrierAnalyser = $this->app->make(CarrierAnalyserInterface::class);

        // Assert
        $this->assertInstanceOf(CarrierAnalyser::class, $carrierAnalyser);
    }

    public function test_binds_spike_analyser_interface_to_spike_analyser()
    {
        // Arrange
        $this->app->register(AppServiceProvider::class);

        // Act
        $spikeAnalyser = $this->app->make(SpikeAnalyserInterface::class);

        // Assert
        $this->assertInstanceOf(SpikeAnalyser::class, $spikeAnalyser);
    }

    public function test_collection_macro_contains_carrier_exception()
    {
        // Arrange
        $this->app->register(AppServiceProvider::class);

        // Act
        $collection = new Collection(['CarrierException', 'OtherException']);

        // Assert
        $this->assertTrue($collection->containsCarrierException());
    }

    public function test_collection_macro_does_not_contain_carrier_exception()
    {
        // Arrange
        $this->app->register(AppServiceProvider::class);

        // Act
        $collection = new Collection(['OtherException']);

        // Assert
        $this->assertFalse($collection->containsCarrierException());
    }

    public function test_collection_macro_contains_request_exception()
    {
        // Arrange
        $this->app->register(AppServiceProvider::class);

        // Act
        $collection = new Collection(['RequestException', 'OtherException']);

        // Assert
        $this->assertTrue($collection->containsRequestException());
    }

    public function test_collection_macro_does_not_contain_request_exception()
    {
        // Arrange
        $this->app->register(AppServiceProvider::class);

        // Act
        $collection = new Collection(['OtherException']);

        // Assert
        $this->assertFalse($collection->containsRequestException());
    }
}
