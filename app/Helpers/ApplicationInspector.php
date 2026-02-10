<?php

/**
 * @file ApplicationInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\Server;

class ApplicationInspector {
    protected Server $server;
    protected bool $is_valid;

    public function __construct() {
        $this->is_valid = true;
    }

    public function isOnServer(Server $server) {
        $this->server = $server;
        return false;
    }

    public function getName() {
        return "unknown";
    }

    public function getDomain() {
        return "http://example.com";
    }

    /**
     * @function getDomainAndInfo
     * Returns a warning message too if not https by default
     **/

    public function getDomainAndInfo() : string {
        $domain = $this->getDomain();
        $colors = $this->getColors();
        if (strpos($domain, "http://") !== false) {
            $domain .= ' ' . $colors['red'] . '(not https)';
        }
        return $domain;
    }

    public function getDescription() {
        return "unknown";
    }

    public function getTitle() {
        return "Application Audit";
    }

    public function isValidInstallation() : bool {
        return $this->is_valid;
    }

    protected function readFile(string $filename) : string {
        $full_filename = $this->server->getFolder();
        if ($full_filename) {
            $lpos = strlen($full_filename) - 1;
            if ($lpos > 0) {
                $last_char = $full_filename[$lpos];
                if ($last_char != DIRECTORY_SEPARATOR) {
                    $full_filename .= DIRECTORY_SEPARATOR;
                }
                $full_filename .= $filename;
                if (file_exists($full_filename)) {
                    return file_get_contents($full_filename);
                }
            }
        }
        return '';
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
            'yellow'    => "\033[1;33m",
            'red'       => "\033[0;31m",
            'blue'      => "\033[0;34m",
            'green'      => "\033[0;32m",
            'orange'      => "\033[0;33m",
            'none'      => "\033[0m",
        ];
    }

    public function getAsciiLogo() : string {
       $wp_logo_filename = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'ascii-art' . DIRECTORY_SEPARATOR . $this->getAsciiLogoFilename();
       $logo = file_get_contents($wp_logo_filename);
       return $this->formatLogo($logo);
    }

    protected function formatLogo(string $logo) {
        $lines = explode(PHP_EOL, $logo);
        $remove_every_n = 2;    // nb we need this for rows

        $output = "";
        $line_number = 0;
        foreach ($lines as $line) {
            $line_number++;
            if ($line_number != $remove_every_n) {
                //$output .= $this->removeCharactersFromLine($line, $max_line_length) . PHP_EOL;
                $output .= $this->removeEveryNCharactersFromLine($line, $remove_every_n) . PHP_EOL;
                //$output .= $line . PHP_EOL;
            } else {
                $line_number = 0;
            }
        }
        return $output;
    }

    protected function removeCharactersFromLine(string $line, int $max_length) {
        while (strlen($line) > $max_length) {
            $line = $this->removeEveryNCharactersFromLine($line, 3);
        }
        return $line;
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