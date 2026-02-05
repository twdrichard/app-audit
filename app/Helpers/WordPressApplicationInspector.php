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

    public function isOnServer(Server $server) {    // nb check for wp-config file not wp cli?
        $this->has_cli = false;
        $this->server = $server;

        $info = $server->executeCommand("wp --version");
        //echo "Found wp version $info" . PHP_EOL;
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
        $s = "Application type: WordPress" . PHP_EOL;
        $s .= "WordPress core version: " . $this->getCoreVersion() . PHP_EOL;
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

    public function getAsciiLogo() : string {
       $wp_logo_filename = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'wordpress-ascii-logo.txt';
       $logo = file_get_contents($wp_logo_filename);
       return $this->formatLogo($logo);
    }

    protected function formatLogo(string $logo, $remove_every_n = 2) {
        $lines = explode(PHP_EOL, $logo);
        $output = "";
        $line_number = 0;
        foreach ($lines as $line) {
            $line_number++;
            if ($line_number != $remove_every_n) {
                $output .= $this->removeEveryNCharactersFromLine($line, $remove_every_n) . PHP_EOL;
            } else {
                $line_number = 0;
            }
        }
        return $output;
    }

    protected function removeEveryNCharactersFromLine($line, $remove_every_n = 2) {
        $output = "";
        $char_pos = 0;
        for ($i = 0; $i < strlen($line); $i++) {
            $char_pos++;
            if ($char_pos != $remove_every_n) {
                $output .= $line[$i];
            } else {
                $char_pos = 0;
            }
        }
        return $output;
    }
}