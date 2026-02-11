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
    //protected $signature = 'audit {server} {identity=none} {--username=none} {path=httpdocs}';
    protected $signature = 'audit
        {server : The server SSH alias.}
        {identity? : SSH identity key}
        {username? : server username}
        {path=httpdocs : The application file path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit an application on a server.';

    protected Server $server;
    protected SiteInspector $inspector;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server_name = $this->argument('server');
        $path = $this->argument('path');
        $username = $this->argument('username');
        $identity = $this->argument('identity');

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
