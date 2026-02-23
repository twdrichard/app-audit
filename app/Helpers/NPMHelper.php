<?php

/**
 * @file JSApplicationInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\Server;
use App\Helpers\NPMAuditSection;
use App\Helpers\NPMOutdatedSection;

class NPMHelper {
    protected Server $server;

    public function __construct(Server $server) {
        $this->server = $server;
    }

    public function buildOutdatedDescription(array $colors) : string {
        $report = $this->server->executeCommand('npm outdated', true);
        if ($report == '') {
            return "No outdated modules found.";
        }
        $outdated = $colors['green'] . "Outdated modules" . PHP_EOL;
        $sections = $this->parseNPMOutdatedReportIntoSections($report);
        if ($sections) {
            foreach ($sections as $section) {
                $outdated .= $section->getSummary($colors) . PHP_EOL;
            }
        }
        return $outdated;
    }

    public function buildAuditDescription(array $colors) : string {
        $audit = $this->server->executeCommand('npm audit --audit-level=moderate', true);
        $audit_fail_message = "npm ERR! code ENOLOCK";
        if (strpos($audit, $audit_fail_message) === 0) {
            return "npm modules not found.";
        }
        $report = "";
        $report = $colors['green'] . "Known issues" . PHP_EOL;
        $sections = $this->parseNPMReportIntoSections($audit);
        if ($sections) {
            foreach ($sections as $section) {
                $report .= $section->getSummary($colors) . PHP_EOL;
            }
        }
        return $report;
    }

    protected function parseNPMOutdatedReportIntoSections(string $report) : array {
        $sections = [];
        $lines = explode(PHP_EOL, $report);
        foreach ($lines as $line) {
            if ($line != "" && $line != "\r") {
                $sections []= new NPMOutdatedSection($line);
            }
        }
        return $sections;
    }

    protected function parseNPMReportIntoSections(string $report) : array {
        $report = $this->removeUnneededInfo($report);
        $lines = explode(PHP_EOL, $report);
        $sections = [];
        $section_lines = [];
        foreach ($lines as $line) {
            if ($line == "" || $line == "\r") {
                // blank line signals the end of a section
                if (count($section_lines)) {
                    $sections []= new NPMAuditSection($section_lines);
                    $section_lines = [];
                }
            } else {
                $section_lines []= $line;
            }
        }

        // add a final section if needed
        if (count($section_lines)) {
            $sections []= new NPMAuditSection($section_lines);
            $section_lines = [];
        }
        return $sections;
    }

    protected function removeUnneededInfo(string $report) : string {
        $info_lines_to_ignore = [
            "# npm audit report",
            "To address all issues, run:",
            "npm audit fix",
        ];
        foreach ($info_lines_to_ignore as $line_to_ignore) {
            $report = str_replace($line_to_ignore, '', $report);
        }
        return $report;
    }
}