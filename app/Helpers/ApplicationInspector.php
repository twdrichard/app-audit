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
}