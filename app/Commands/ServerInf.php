<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ServerInf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serverInf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Hello, " . $this->findUsername() .  "!");
        $basic_server_info = [
            'hostname'      => "hostname",
            'PHP version'   => "php --version",
            //'OS version'    => "uname -svrm",
            'is Linux'      => "if \"true\" == ',('; then echo Linux; fi;true \) else echo Windows",
        ];
        foreach ($basic_server_info as $info_name => $command) {
            $value = $this->executeCommand($command);
            $this->info($info_name . ": " . $value);
        }
        $this->info('Linux version: ' . $this->findLinuxVersion());
    }

	protected function findUsername() : string {
		$command = "whoami";
		$output = [];
		exec($command, $output, $result);
		if ($result == 0) {
			if ($output && count($output)) {
				$name = reset($output);
				if ($name) {
					$this->username = $name;
					return ucfirst($name);
				}
			}
		}
		return "stranger";
	}

    protected function findLinuxVersion() : string {
        $command = "cat /etc/*release";
		$output = [];
        $version_name = "unknown";
		exec($command, $output, $result);
		if ($result == 0) {
            $version_name = "";
            $os_name = "";
            $os_version = "";
            foreach ($output as $line) {
                if ($line) {
                    $ar = explode("=", $line);
                    if ($ar && count($ar) > 1) {
                        $item_name = $ar[0];
                        $value = str_replace('"', '', $ar[1]);
                        if ($item_name == "NAME") {
                            $os_name = $value;
                        }
                        if ($item_name == "VERSION") {
                            $os_version = $value;
                        }
                    }
                }
            }
            $version_name = $os_name . ' ' . $os_version;
        }
        return $version_name;
    }

    protected function executeCommand(string $command) : string {
		$output = [];
		exec($command, $output, $result);
		if ($result == 0) {
			if ($output && count($output)) {
				return reset($output);
			}
            return implode("\n", $output);
		}
		return "none.";
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
