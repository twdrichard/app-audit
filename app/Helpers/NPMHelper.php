<?php

/**
 * @file JSApplicationInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\Server;
use App\Helpers\NPMAuditSection;

class NPMHelper {
    protected Server $server;

    public function __construct(Server $server) {
        $this->server = $server;
    }

    public function buildOutdatedDescription(array $colors) : string {
        $outdated = $this->server->executeCommand('npm audit outdated', true);
        if ($outdated == '') {
            return "No outdated modules found.";
        }
        return "Outdated: " . $outdated;
    }


    public function buildAuditDescription(array $colors) : string {
        $audit = $this->server->executeCommand('npm audit --audit-level=moderate', true);
        $audit_fail_message = "npm ERR! code ENOLOCK";
        if (strpos($audit, $audit_fail_message) === 0) {
            return "npm modules not found.";
        }
        $sections = $this->parseNPMReportIntoSections($audit);
        if ($sections) {
            $audit = "";
            $audit = "Found " . count($sections) . " audit sections." . PHP_EOL;
            foreach ($sections as $section) {
                $audit .= $section->getSummary($colors) . PHP_EOL;
            }
        }
        return $audit;
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