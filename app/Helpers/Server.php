<?php

/**
 * @file Server.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

class Server {
    protected string $server_name, $path;
    protected bool $is_local;

    public function __construct(string $server_name, string $path) {
        $this->server_name = $server_name;
        $this->is_local = ($server_name == "local");
        if ($path) {
            $this->path = $path;
        } else {
            if ($this->is_local) {
                $this->path = '/var/www/html/';
            } else {
                $this->path = 'httpdocs/';
            }
        }
    }

    public function getFolder() : string {
        return $this->path;
    }

    public function executeCommand(string $command) : string {
        if ($this->is_local) {
            return $this->executeLocalCommand($command);
        } else {
            return $this->executeRemoteCommand($command);
        }
    }

    public function executeLocalCommand(string $command) : string {
            //echo "executeLocalCommand($command)" . PHP_EOL;
		$output = [];
		exec($command, $output, $result);
                if (count($output)) {
		//if ($result == 0) {
                        //echo $command . PHP_EOL;
                        //print_r($output); echo PHP_EOL;
            return implode("\n", $output);
		}
		return "none.";
    }

    public function executeRemoteCommand(string $command) : string {
        // nb we need to escape commands
		$ssh_command = 'ssh ' . $this->server_name . ' "' . $command . '"';
        return $this->executeLocalCommand($ssh_command);
    }

    protected function getSSHCommand(string $command) : string {
        if ($this->is_local) {
            return $command;
        } else {
            $ssh_command = 'ssh ' . $this->server_name . ' "' . $command . '"';
            return $ssh_command;
        }
    }

    public function findLinuxPrettyName() : string {
        $command = "cat /etc/*release | grep PRETTY_NAME=";
        $output = $this->executeCommand($command);
        return str_replace('PRETTY_NAME=', '', $output);
    }

    /**
     * findHostingInfo
     * Probes the server to search for website hosting information
     **/

    public function findHostingInfo() : ?array {
        $wordpress_spec = [
			[ 'wp-config.php', 'file', '' ],
			[ 'wp-content',	'folder', '' ],
		];
		$yii2_spec = [
			[ 'db-local.php', 'file', 'config' ],
		];

		$server_specs = [
			'WordPress' => $wordpress_spec,
			'Yii2'		=> $yii2_spec,
		];
		$s = '';
		$found_application_name = '';
		foreach ($server_specs as $application_name => $server_spec) {
			$application_found = $this->probeServerApplication($application_name, $server_spec);
			if ($application_found) {
				$s .= "Found application $application_name." . PHP_EOL;
				$found_application_name = $application_name;
			}
		}
		echo $s;

		if ($found_application_name == 'WordPress') {
			// check the db
			$config = $this->getWordPressConfig();
			if ($config) {
				echo "Yay, found config...." . PHP_EOL;
				//echo $config;
				$this->probeWordPressDatabase($config);
			} else {
				echo "...but no config found." . PHP_EOL;
			}
		}
		return [ $s ];
    }

	protected function probeServerApplication(string $application_name, array $server_spec) {
		echo "Checking for application $application_name..." . PHP_EOL;
		$ok = false;
		foreach ($server_spec as $files_spec) {
			$filename = $files_spec[0];
			$filetype = $files_spec[1];
			$file_location = $files_spec[2];
			echo "   Looking for '$filename' type $filetype at $file_location" . PHP_EOL;
			switch ($filetype) {
				case 'file':
					$ok = $this->findFileInfo($filename, $file_location);
					if ($ok == false) {		// file not found
						return false;
					} else {
						$ok = true;				// we found this file but need to find all of them
					}
				break;

				default:
					echo "Skipping filetype $filetype" . PHP_EOL;
				break;
			}
		}
		return $ok;
	}

	protected function findFileInfo($filename, $folder) {
		$full_filename = trim($this->path) . DIRECTORY_SEPARATOR;
		if ($folder) {
			$full_filename .= $folder . DIRECTORY_SEPARATOR;
		}
		$full_filename .= $filename;
		//echo "findFileInfo for '$full_filename'" . PHP_EOL;
		// do we see the output here because we're doing an ls
		$command = $this->getSSHCommand('ls -la ' . $this->escapeString($full_filename) . " 2>&1");
		echo $command . PHP_EOL;
		//echo "...about to shell_exec..." . PHP_EOL;
		//$output = [];
		//exec($command, $output, $result);
		$output = shell_exec($command);
		//echo "...fin..." . PHP_EOL;
		//echo $command . PHP_EOL . print_r($output, true) . PHP_EOL;
		//exit;*/
		if (is_string($output)) {
			if (strpos($output, 'No such file or directory') === false) {
				return true;
			} else {
				//echo "File not found ***" . PHP_EOL;
				return false;
			}
		}
		return false;
	}


	protected function escapeString($s) {
		return "'" . $s . "'";
	}
}
