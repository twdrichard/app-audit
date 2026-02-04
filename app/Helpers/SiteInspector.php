<?php

/**
 * @file SiteInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

class SiteInspector {
    protected Server $server;

    public function __construct(Server $server) {
        $this->server = $server;
    }

    public function findApplicationType() {
        return "Unknown";
    }
}