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

    /**
     *  Ansi color codes for linux terminal from
     * https://stackoverflow.com/questions/5947742/how-to-change-the-output-color-of-echo-in-linux
     *  Black        0;30     Dark Gray     1;30
        Red          0;31     Light Red     1;31
        Green        0;32     Light Green   1;32
        Brown/Orange 0;33     Yellow        1;33
        Blue         0;34     Light Blue    1;34
        Purple       0;35     Light Purple  1;35
        Cyan         0;36     Light Cyan    1;36
        Light Gray   0;37     White         1;37
     **/

    public function getColors() : array {
        return [
            'cyan'      => "\033[0;36m",
            'yellow'    => "\033[0;33m",
            'red'       => "\033[0;31m",
            'blue'      => "\033[0;34m",
            'none'      => "\033[0m",
        ];
    }
}