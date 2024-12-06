<?php

namespace App\Providers;

use App\Service\Analysis\CarrierAnalyser;
use App\Service\Analysis\ICarrierAnalyser;
use App\Service\Analysis\ISpikeAnalyser;
use App\Service\Analysis\ITypeAnalyser;
use App\Service\Analysis\SpikeAnalyser;
use App\Service\Analysis\TypeAnalyser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
        $this->app->bind(ITypeAnalyser::class, TypeAnalyser::class);
        $this->app->bind(ICarrierAnalyser::class, CarrierAnalyser::class);
        $this->app->bind(ISpikeAnalyser::class, SpikeAnalyser::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
