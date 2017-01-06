<?php
namespace Integration\Api\Console;

use Illuminate\Console\Command;

class ConfigureCommand extends Command
{
    // use MakesHttpRequests;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration:annotaion:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'annotaion create';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->laravel->make('annotations.route.scanner')->addAnnotationNamespace('Integration\Api\Annotions', __DIR__ . '/../Annotions/');
        $this->call('route:scan');
    }
}

