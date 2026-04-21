<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

use App\Helpers\Server;
use App\Helpers\SiteInspector;

class Audit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit
        {server : The server SSH alias or host}
        {folder=httpdocs : The application folder}
        {username? : server username}
        {identity? : SSH identity key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit an application on a server.';

    protected Server $server;
    protected SiteInspector $inspector;

    /**
     * Execute the audit command.
     */
    public function handle()
    {
        $server_name = $this->argument('server');
        $folder = $this->argument('folder');
        $username = $this->argument('username');
        $identity = $this->argument('identity');

        $this->server = new Server($server_name, $folder);
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
