<?php

/**
 * @file Server.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

class Server {
    protected string $server_name;
    protected bool $is_local;

    public function __construct(string $server_name) {
        $this->server_name = $server_name;
        $this->is_local = ($server_name == "local");
    }

    public function executeCommand(string $command) : string {
        if ($this->is_local) {
            return $this->executeLocalCommand($command);
        } else {
            return $this->executeRemoteCommand($command);
        }
    }

    public function executeLocalCommand(string $command) : string {
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

    public function executeRemoteCommand(string $command) : string {
		$ssh_command = 'ssh ' . $this->server_name . ' "' . $command . '"';
        return $this->executeLocalCommand($ssh_command);
    }

}
