<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

use App\Helpers\Server;

class ServerInf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serverinf {server=local}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show server information';

    protected Server $server;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server_name = $this->argument('server');
        $this->server = new Server($server_name);

        $this->info("Hello, " . $this->findUsername() .  "!");
        //$this->info('Local linux version: ' . $this->findLinuxVersion());

        $this->info("Server info:");
        $basic_server_info = [
            'hostname'      => "hostname",
            'PHP version'   => "php --version",
            //'OS version'    => "uname -svrm",
            'is Linux'      => "if \"true\" == ',('; then echo Linux; fi;true \) else echo Windows",
        ];
        foreach ($basic_server_info as $info_name => $command) {
            $value = $this->server->executeCommand($command);
            $this->info($info_name . ": " . $value);
        }

        $linux_version = $this->server->findLinuxPrettyName();
        if ($linux_version) {
            $this->info("Linux version: " . $linux_version);
        }

        $website_info = $this->server->findHostingInfo();
        if ($website_info) {
            foreach ($website_info as $website_info_line) {
                $this->info($website_info_line);
            }
        } else {
            $this->info("No hosting information found.");
        }
    }

	protected function findUsername() : string {
        return ucfirst($this->server->executeCommand("whoami"));
	}

}
