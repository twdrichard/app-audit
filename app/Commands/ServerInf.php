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
    protected $signature = 'serverinf {server} {path=httpdocs}';

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
        $this->inspector = new SiteInspector($this->server);
        if (!$this->inspector->findApplicationType()) {
			  echo "No application type found." . PHP_EOL;
			  exit();
			 }
        if (!$this->inspector->isValidInstallation()) {
			  $this->info("Sorry, I'm not recognizing a valid site here.");
		  } else {
			$this->info($this->inspector->getFormattedDescription());
		}
    }

	protected function findUsername() : string {
        return ucfirst($this->server->executeCommand("whoami"));
	}
}
