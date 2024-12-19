<?php

namespace App\Providers;

use App\Service\Analysis\CarrierAnalyser;
use App\Service\Analysis\Interfaces\CarrierAnalyserInterface;
use App\Service\Analysis\Interfaces\SpikeAnalyserInterface;
use App\Service\Analysis\Interfaces\TypeAnalyserInterface;
use App\Service\Analysis\SpikeAnalyser;
use App\Service\Analysis\TypeAnalyser;
use App\Service\ExceptionAnalyser;
use Illuminate\Support\Collection;
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
        $this->app->singleton(ExceptionAnalyser::class, function ($app) {
            return new ExceptionAnalyser(
                $app->make(TypeAnalyserInterface::class),
                $app->make(CarrierAnalyserInterface::class),
                $app->make(SpikeAnalyserInterface::class)
            );
        });

        $this->app->bind(TypeAnalyserInterface::class, TypeAnalyser::class);
        $this->app->bind(CarrierAnalyserInterface::class, CarrierAnalyser::class);
        $this->app->bind(SpikeAnalyserInterface::class, SpikeAnalyser::class);
        $this->app->bind(ExceptionAnalyser::class, ExceptionAnalyser::class);
    }

    /**
     * Bootstrap services.
     * This method is used to bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Collection::macro('containsCarrierException', function () {
            return $this->contains(function ($value) {
                return str_contains($value, 'CarrierException');
            });
        });
        Collection::macro('containsRequestException', function () {
            return $this->contains(function ($value) {
                return str_contains($value, 'RequestException');
            });
        });
    }
}
