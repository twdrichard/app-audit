<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

use App\Helpers\Server;
use App\Helpers\SiteInspector;

class ServerInf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serverinf {server} {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show server information';

    protected Server $server;
    protected SiteInspector $inspector;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server_name = $this->argument('server');
        $path = $this->argument('path');

        $this->server = new Server($server_name, $path);
        //$this->info("Hello, " . $this->findUsername());

        $this->info("Server info:");
        $basic_server_info = [
            'hostname'      => "hostname",
            'PHP version'   => "php --version",
            //'OS version'    => "uname -svrm",
           // 'is Linux'      => "if \"true\" == ',('; then echo Linux; fi;true \) else echo Windows",
        ];
        foreach ($basic_server_info as $info_name => $command) {
            $value = $this->server->executeCommand($command);
            $this->info($info_name . ": " . $value);
        }

        $linux_version = $this->server->findLinuxPrettyName();
        if ($linux_version) {
            $this->info("Linux version: " . $linux_version);
        }

        $this->info("");

        //$this->info("Application info:");
        $this->inspector = new SiteInspector($this->server);
        $this->inspector->findApplicationType();
        $this->info($this->inspector->getFormattedDescription());
    }

	protected function findUsername() : string {
        return ucfirst($this->server->executeCommand("whoami"));
	}
}
