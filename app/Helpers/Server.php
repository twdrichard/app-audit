<?php

/**
 * @file Server.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

class Server {
    public function __construct(string $server_name) {
    }

    public function executeCommand(string $command) : string {
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
}
