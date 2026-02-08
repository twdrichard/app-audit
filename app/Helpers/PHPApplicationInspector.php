<?php

/**
 * @file PHPApplicationInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\Server;
use App\Helpers\ApplicationInspector;

class PHPApplicationInspector extends ApplicationInspector {
    protected bool $has_composer_json;
    protected array $composer_ar;

    public function __construct() {
        parent::__construct();
        $this->composer_ar = [];
    }

    public function isOnServer(Server $server) {
        $this->server = $server;

        // check for composer.json
        $composer = $this->getComposer();
        if ($composer == '') {
            return false;
        }
        return true;
    }

    protected function getComposer() {
        $file = $this->readFile('composer.json');
        if ($file) {
            $this->composer_ar = explode(PHP_EOL, $file);
        }
        return $this->composer_ar;
    }

    public function getName() : string {
        $name = $this->findComposerLine('name');
        if ($name) {
            return 'PHP Application: ' . $name;
        } else {
            return "PHP Application";
        }
    }

    public function getDomain() : string {
        return $this->findComposerLine('homepage');
    }

    public function getAsciiLogoFilename() : string {
        return 'php-ascii-logo.txt';
    }
    public function getDescription() {
        $description = "";
        $description .= $this->getName() . PHP_EOL;
        $description .= $this->getDomain() . PHP_EOL;
        $audit = $this->server->executeCommand('composer audit -d ' . $this->server->getFolder());
        $description .= $this->buildComposerAuditSummary($audit);

        return $description;
    }


    protected function buildComposerAuditSummary(string $audit) : string {
        $audit_lines = explode(PHP_EOL, $audit);
        $audit_items = $this->parseComposerAuditToArray($audit_lines);
        $s = "";
        foreach ($audit_items as $item) {
            $s .= $item['package'] . ': ' . $item['title'] . PHP_EOL;
        }
        return $s;
    }

    protected function parseComposerAuditToArray(array $audit_lines) : array {
        $ar = [];
        $divider = "+-------------------+----------------------------------------------------------------------------------+";
        $inside_item = false;
        foreach ($audit_lines as $line) {
            if (strpos($line, $divider) === 0) {
                if (!$inside_item) {
                    $inside_item = true;
                    $title = '1';
                    $package = 'x';
                } else {
                    $item = [ 'title' => $title, 'package' => $package ];
                    $ar []= $item;
                    $inside_item = false;
                }
            }
        }
        return $ar;
    }

    protected function findComposerLine(string $name) : string {
        if (count($this->composer_ar) > 0) {
            $required_line_id = '"' . $name . '":';
            foreach ($this->composer_ar as $line) {
                $line = trim($line);
                if (strpos($line, $required_line_id) === 0) {
                    $value = trim(str_replace($required_line_id, '', $line));
                    $value = str_replace('"', '', $value);
                    $value = str_replace(',', '', $value);
                    return $value;
                }
            }
        }
        return '';
    }
}