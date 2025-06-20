<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ConsistentHasher;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configureCommands();
        $this->configureModels();
        $this->configureDates();
        $this->bindConsistentHasher();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction()
        );
    }

    /**
     * Configure the dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the models.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard();
    }

    private function bindConsistentHasher(): void
    {
        $this->app->singleton(ConsistentHasher::class, function (): ConsistentHasher {
            $shards = config('shards.connections');

            return new ConsistentHasher($shards);
        });
    }
}
