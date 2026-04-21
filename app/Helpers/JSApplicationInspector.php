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
    protected array $package_ar;

    public function __construct() {
        parent::__construct();
        $this->package_ar = [];
    }

    public function isOnServer(Server $server) {
        $this->server = $server;
        return true;
    }

    public function getName() : string {
        if ($this->isReact()) {
            return "JS React Application";
        } else {
            return "JS Application";
        }
    }

    public function getAsciiLogoFilename() : string {
        if ($this->isReact()) {
            return 'react-ascii-logo.txt';
        } else {
            return 'js-ascii-logo.txt';
        }
    }

    protected function isReact() : bool {
        return $this->packageHasComponent("react");
    }

    protected function getPackageJson() {
        if ($this->package_ar == []) {
            $filename = 'package.json';
            if (!$this->server->fileExists($filename)) {
                return [];
            }
            $file = $this->server->readFile($filename);
            if ($file) {
                $this->package_ar = explode(PHP_EOL, $file);
            }
        }
        return $this->package_ar;
    }

    protected function packageHasComponent(string $component_name) {
        $component_name = '"' . $component_name . '":';
        $package_ar = $this->getPackageJson();
        if ($package_ar) {
            foreach ($package_ar as $package_line) {
                if (strpos($package_line, $component_name) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getDescription() {
        $description = "";
        $description .= $this->getName() . PHP_EOL;

        $npm = new NPMHelper($this->server);
        $description .= $this->server->getServerDescription($this->getColors());
        $description .= $npm->buildAuditDescription($this->getColors()) . PHP_EOL;
        $description .= $npm->buildOutdatedDescription($this->getColors()) . PHP_EOL;
        return $description;
    }
}