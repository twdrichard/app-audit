<?php

/**
 * @file WordPressApplicationInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\Server;
use App\Helpers\ApplicationInspector;

class WordPressApplicationInspector extends ApplicationInspector {
    protected bool $has_cli;

    public function isOnServer(Server $server) {
        $this->has_cli = false;
        $this->server = $server;

        $info = $server->executeCommand("wp --version");
        echo "Found wp version $info" . PHP_EOL;
        if (strpos($info, "WP-CLI") !== false) {
            // we've found wp-cli
            $this->has_cli = true;
            return true;
        } else {
            return false;
        }
    }

    public function getName() {
        return "WordPress";
    }

    public function getDescription() {
        $s = "WordPress core version: " . $this->getCoreVersion() . PHP_EOL;
        $s.= $this->getPluginsList();
        return $s;
    }

    protected function getCoreVersion() : string {
        if ($this->has_cli) {
            $folder = $this->server->getFolder();
            return $this->server->executeCommand("wp --path=$folder core version");
        } else {
            return "unknown";
        }
    }

    protected function getPluginsList() : string {
        if ($this->has_cli) {
            $folder = $this->server->getFolder();
            return $this->server->executeCommand("wp --path=$folder plugin list");
        } else {
            return "Plugins not found." . PHP_EOL;
        }
    }
}