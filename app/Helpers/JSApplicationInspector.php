<?php

/**
 * @file JSApplicationInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\Server;
use App\Helpers\ApplicationInspector;
use App\Helpers\NPMHelper;

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

        $npm = new NPMHelper($this->server);
        $description .= $npm->buildAuditDescription($this->getColors()) . PHP_EOL;
        $description .= $npm->buildOutdatedDescription($this->getColors()) . PHP_EOL;
        return $description;
    }
}