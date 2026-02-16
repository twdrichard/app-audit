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
    protected string $framework_name;

    public function __construct() {
        parent::__construct();
        $this->composer_ar = [];
        $this->framework_name = "";
    }

    public function isOnServer(Server $server) {
        $this->server = $server;
        // check for composer.json
        $composer = $this->getComposer();
        if ($composer == []) {
            echo "Composer not found, bailing..." . PHP_EOL;
            exit();
            return false;
        }
        return true;
    }

    protected function getComposer() {
        $file = $this->server->readFile('composer.json');
        if ($file) {
            $this->composer_ar = explode(PHP_EOL, $file);
        }
        return $this->composer_ar;
    }

    public function getName() : string {
        $framework_name = $this->findFrameworkName();
        $name = $this->findComposerLine('name');
        if ($name) {
            return $framework_name . PHP_EOL . 'Name : ' . $name;
        } else {
            return $framework_name;
        }
    }

    protected function findFrameworkName() : string {
        if ($this->framework_name)  {
            return $this->framework_name;
        }

        if ($this->server->fileExists("yii")) {
            $this->framework_name = "Yii2 Application";
            return $this->framework_name;
        }
        if ($this->server->fileExists("artisan")) {
            $info = $this->server->executeCommand("php artisan --version", true);
            $info = $this->removePHPErrors($info);
            $laravel_identifier =  "Laravel Framework ";
            $version_pos = strpos($info, $laravel_identifier);
            if ($version_pos !== false) {
                $framework = "Laravel v" . substr($info, $version_pos + strlen($laravel_identifier));
                $this->framework_name = str_replace(PHP_EOL, '', $framework);
                return $this->framework_name;
            }
        }

        $this->framework_name = "PHP Application";
        return $this->framework_name;
    }

    public function getDomain() : string {
        return $this->findComposerLine('homepage');
    }

    public function getAsciiLogoFilename() : string {
        $framework = $this->findFrameworkName();
        if (strpos($framework, "Laravel") === 0) {
            return 'laravel-ascii-logo.txt';
        }
        if (strpos($framework, "Yii2") === 0) {
            return 'yii2-ascii-logo.txt';
        }
        return 'php-ascii-logo.txt';
    }

    protected function isLaravel() : bool {
        $framework = $this->findFrameworkName();
        if (strpos($framework, "Laravel") === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getDescription() {
        $description = $this->getName() . PHP_EOL;
        $description .= $this->getDomain() . PHP_EOL;
        $audit = $this->server->executeCommand('composer audit', true);
        $description .= $this->buildComposerAuditSummary($audit);
        return $description;
    }

    protected function buildComposerAuditSummary(string $audit) : string {
        $colors = $this->getColors();
        $audit_lines = explode(PHP_EOL, $audit);
        $audit_items = $this->parseComposerAuditToArray($audit_lines);
        $max_line_length = (int)($this->line_width / 2);
        $s = "";
        foreach ($audit_items as $item) {
            $line = "";
            $severity = '';
            if ($item['severity'] == 'high') {
                $line = $colors['orange'] . 'High: ';
            }
            $line .= $severity . $item['package'] . ': ' . $item['title'] . PHP_EOL;

            while (strlen($line) > $max_line_length) {
                $s .= substr($line, 0, $max_line_length) . PHP_EOL;
                $line = "   " . substr($line, $max_line_length);
            }
            $s .= $line . PHP_EOL;
        }
        if ($s != "") {
            $s= $colors['red'] . 'Security Issues' . PHP_EOL . $s;
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
                    $item = [ 'title' => $title, 'package' => $package, 'severity' => $severity ];
                    $ar []= $item;
                    $inside_item = false;
                }
            }
            $line = str_replace('|', '', $line);
            $line = trim($line);
            if (strpos($line, 'Title') === 0) {
                $title = trim(str_replace('Title', '', $line));
            }
            if (strpos($line, 'Package') === 0) {
                $package = trim(str_replace('Package', '', $line));
            }
            if (strpos($line, 'Severity') === 0) {
                $severity = trim(str_replace('Severity', '', $line));
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
    public function hasLogs() : bool {
        return $this->server->fileExists($this->findDebugLogFilename(), false);
    }

    protected function findDebugLogFilename() : string {
        if ($this->isLaravel()) {
            $debug_filename = $this->server->getFolder() . DIRECTORY_SEPARATOR .  "storage" . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . "laravel.log";
        } else {
            // Yii2
            $debug_filename = $this->server->getFolder() . DIRECTORY_SEPARATOR .  "runtime" . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . "app.log";
        }
        return $debug_filename;
    }

    public function getLogLines() : array {
        $log_filename = $this->findDebugLogFilename();
        if ($log_filename) {
            $command = "tail -10 $log_filename";
            echo $command . PHP_EOL;
            $output = $this->server->executeCommand($command);
            if ($output) {
                $ar = explode(PHP_EOL, $output);
                return $ar;
            }
            //echo "Log output not found - $command" . PHP_EOL;
        }
        return [];
    }

}