<?php

namespace Gabrielesbaiz\NovaCardRssNews\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Gabrielesbaiz\NovaCardRssNews\CardServiceProvider;

class TestCase extends Orchestra
{
    /**
     * Register the package providers under test.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            CardServiceProvider::class,
        ];
    }

    /**
     * Configure the testbench environment.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
