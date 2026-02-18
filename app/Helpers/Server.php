<?php

/**
 * @file Server.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

class Server {
    protected string $server_name, $path ,$hostname, $username, $key_filename;
    protected bool $is_local;
    protected int $last_command_result;

    public function __construct(string $server_name, string $path) {
        $this->key_filename = '';
        $this->username = '';

        $this->server_name = $server_name;
        $this->last_command_result = 0;
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

    public function getLastCommandResult() : int {
        return $this->last_command_result;
    }

    public function executeCommand(string $command, $use_path = false) : string {
        if ($use_path) {
            $command = "cd " . $this->path . " && " . $command;
        }
        $command .= " 2>&1";    // suppress error output
        if ($this->is_local) {
            return $this->executeLocalCommand($command);
        } else {
            return $this->executeRemoteCommand($command);
        }
    }

    public function executeLocalCommand(string $command) : string {
		$output = [];
		$exec_out = exec($command, $output, $result);
        //echo "executeLocalCommand($command) with result '$result' exec_out '$exec_out' and output" . PHP_EOL;
        //print_r($output);
        //echo PHP_EOL;
        $this->last_command_result = $result;
        if (count($output)) {
            return implode("\n", $output);
		}
		return "";
    }

    public function executeRemoteCommand(string $command) : string {
		$ssh_command = $this->buildSSHConnection() . ' "' . $command . '"';
        return $this->executeLocalCommand($ssh_command);
    }

    protected function buildSSHConnection() : string {
		$ssh_command = 'ssh ' . $this->server_name;
        // todo: add support for individual host, username and identity rather than just alias
        return $ssh_command;
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
        $output = str_replace('PRETTY_NAME=', '', $output);
        return str_replace('"', '', $output);
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

    public function fileExists(string $filename, $add_path = true) : bool {
        if ($add_path) {
            $full_filename = trim($this->path) . DIRECTORY_SEPARATOR . $filename;
        } else {
            $full_filename = $filename;
        }
        $command = "test -f $full_filename";
        $result = $this->executeCommand($command);
        //echo "fileExists($filename) command $command result " . $this->last_command_result . PHP_EOL;
        if ($this->last_command_result == 1) {
            return false;
        } else {
            return true;
        }
    }

    public function readFile(string $filename, $add_path = true) : bool {
        if ($add_path) {
            $full_filename = trim($this->path) . DIRECTORY_SEPARATOR . $filename;
        } else {
            $full_filename = $filename;
        }
        $command = "cat $full_filename";
        return $this->executeCommand($command);
	}
}
