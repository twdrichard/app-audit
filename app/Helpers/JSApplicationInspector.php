<?php

/**
 * @file JSApplicationInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\Server;
use App\Helpers\ApplicationInspector;

class JSApplicationInspector extends ApplicationInspector {
    protected bool $has_composer_json;
    protected array $composer_ar;

    public function __construct() {
        parent::__construct();
        $this->composer_ar = [];
    }

    public function isOnServer(Server $server) {
        $this->server = $server;
        return true;
    }

    public function getName() : string {
        return "JS Application";
    }

     public function getAsciiLogoFilename() : string {
        return 'js-ascii-logo.txt';
    }
    public function getDescription() {
        $description = "";
        $description .= $this->getName() . PHP_EOL;
        $description .= $this->runNPMAuditDescription() . PHP_EOL;
        return $description;
    }

    protected function runNPMAuditDescription() : string {
        $audit = $this->server->executeCommand('npm audit --audit-level=moderate', true);
        $audit_fail_message = "npm ERR! code ENOLOCK";
        if (strpos($audit, $audit_fail_message) === 0) {
            return "npm modules not found.";
        }
        return $audit;
    }
}