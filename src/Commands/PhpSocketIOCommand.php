<?php

namespace Lackone\LaravelPhpsocketIo\Commands;

use Illuminate\Console\Command;
use Lackone\LaravelPhpsocketIo\Exceptions\Exception;

class PhpSocketIOCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ps {service_name} {action} {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a PHPSocketIO Service.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        global $argv;

        $action = $this->argument('action') ?: 'start';

        if (in_array($action, ['status', 'start', 'stop', 'restart', 'reload', 'connections'])) {

            $service_name = $this->argument('service_name') ?: config('ps.service_name');
            $daemon = $this->option('d') ? '-d' : '';

            $class = config("ps.{$service_name}.socket_io_handler");

            if ($service_name) {
                $argv[0] = 'ps';
                $argv[1] = $action;
                $argv[2] = $daemon;

                $service = new $class($service_name);
                try {
                    $service->start();
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }
    }
}