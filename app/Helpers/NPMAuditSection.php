<?php

/**
 * @file NPMAuditSection.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\NPMAuditSection;

class NPMAuditSection {
    protected array $lines;
    protected string $name, $severity, $description;

    public function __construct(array $lines) {
        $this->lines = $lines;
        $this->name = '';
        $this->severity = '';
        $this->description = '';
        $this->parse();
    }

    protected function parse() {
        $this->name = $this->lines[0];
        $severity_line_no = -1;
        $line_no = 0;
        foreach ($this->lines as $line) {
            $severity_id = "Severity: ";
            if (strpos($line, $severity_id) !== false) {
                $this->severity = trim(str_replace($severity_id, '', $line));
                $severity_line_no = $line_no;
            } else {
                if ($line_no == 1 || ($severity_line_no != -1 && $line_no = ($severity_line_no + 1))) {
                    $this->description = $line;
                }
            }
            $line_no++;
        }
    }

    public function getSummary(array $colors) : string {
        if (!$this->isValid()) {
            return '';
        }
        $summary = $this->name . PHP_EOL;
        if ($this->severity) {
            $scolor = $this->getSeverityColor($this->severity);
            $color = '';
            if ($scolor && array_key_exists($scolor, $colors)) {
                $color = $colors[$scolor];
            }
            $summary .= "   severity " . $color . $this->severity . PHP_EOL;
            if ($this->description) {
                $summary .= "   " . $colors['blue'] . $this->description . PHP_EOL;
            }
        }
        return $summary;
    }

    protected function getSeverityColor(string $severity) : string {
        switch ($severity) {
            case 'moderate':
                return 'blue';
            case 'high':
                return 'orange';
            case 'critical':
                return 'red';
            default:
                return '';
        }
    }

    public function isValid() {
        if ($this->lines && count($this->lines) && $this->lines[0] != "") {
            // ignore the vulnerabilities total
            $vpos = strpos($this->name, " vulnerabilities");
            if ($vpos !== false && $vpos < 5) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
}