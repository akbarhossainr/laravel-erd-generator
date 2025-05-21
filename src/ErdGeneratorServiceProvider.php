<?php

namespace Akbarhossainr\ErdGenerator;

use Illuminate\Support\ServiceProvider;

class ErdGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register command
        $this->commands([
            \Akbarhossainr\ErdGenerator\Console\MakeErdCommand::class,
        ]);
    }

    public function boot()
    {
        // Boot logic if needed
    }
}
