<?php

/**
 * @file ApplicationInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\Server;

class ApplicationInspector {
    protected Server $server;

    public function isOnServer(Server $server) {
        $this->server = $server;
        return false;
    }

    public function getName() {
        return "unknown";
    }

    public function getDescription() {
        return "unknown" . PHP_EOL;
    }

    protected function getColors() : array {
        return [
            'cyan'      => "\033[0;36m",
            'yellow'    => "\033[0;33m",
            'red'       => "\033[0;31m",
            'blue'      => "\033[0;34m",
            'none'      => "\033[0m",
        ];
    }
}