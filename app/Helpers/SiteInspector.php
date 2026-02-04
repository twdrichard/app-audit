<?php

/**
 * @file SiteInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;
use App\Helpers\WordPressApplicationInspector;

class SiteInspector {
    protected Server $server;
    protected array $applications;
    protected $application;

    public function __construct(Server $server) {
        $this->server = $server;
        $this->application = null;
        $this->applications = [
            new WordPressApplicationInspector()
        ];
    }

    public function findApplicationType() {
        foreach ($this->applications as $application) {
            if ($application->isOnServer($this->server)) {
                $this->application = $application;
                return $application->getName();
            }
        }
        return "Unknown";
    }

    public function getDescription() {
        if ($this->application != null) {
            return $this->application->getDescription();
        } else {
            return "No application found." . PHP_EOL;
        }
    }
}