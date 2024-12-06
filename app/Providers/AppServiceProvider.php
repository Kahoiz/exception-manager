<?php

namespace App\Providers;

use App\Service\Analysis\CarrierAnalyser;
use App\Service\Analysis\Interfaces\CarrierAnalyserInterface;
use App\Service\Analysis\Interfaces\SpikeAnalyserInterface;
use App\Service\Analysis\Interfaces\TypeAnalyserInterface;
use App\Service\Analysis\SpikeAnalyser;
use App\Service\Analysis\TypeAnalyser;
use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider class is a service provider for binding interfaces to their implementations.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * This method binds interfaces to their respective implementations.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ITypeAnalyser interface to the TypeAnalyser implementation
        $this->app->bind(TypeAnalyserInterface::class, TypeAnalyser::class);

        // Bind the ICarrierAnalyser interface to the CarrierAnalyser implementation
        $this->app->bind(CarrierAnalyserInterface::class, CarrierAnalyser::class);

        // Bind the ISpikeAnalyser interface to the SpikeAnalyser implementation
        $this->app->bind(SpikeAnalyserInterface::class, SpikeAnalyser::class);
    }

    /**
     * Bootstrap services.
     * This method is used to bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Bootstrap code can be added here
    }
}
