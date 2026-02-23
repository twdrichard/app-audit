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

    public function buildOutdatedDescription() : string {
        $outdated = $this->server->executeCommand('npm audit outdated', true);
        if ($outdated == '') {
            return "No outdated modules found.";
        }
        return "Outdated: " . $outdated;
    }


    public function buildAuditDescription() : string {
        $audit = $this->server->executeCommand('npm audit --audit-level=moderate', true);
        $audit_fail_message = "npm ERR! code ENOLOCK";
        if (strpos($audit, $audit_fail_message) === 0) {
            return "npm modules not found.";
        }
        $sections = $this->parseNPMReportIntoSections($audit);
        if ($sections) {
            $audit = "";
            foreach ($sections as $section) {
                $audit .= $section->getSummary() . PHP_EOL;
            }
        }
        return $audit;
    }

    protected function parseNPMReportIntoSections(string $report) : array {
        $report = $this->removeUnneededInfo($report);
        $sections = [];
        //$paras = explode("\n\n", $report);
        $paras = explode("\r\n\r\n", $report);
        if ($paras && count($paras)) {
            foreach ($paras as $para) {
                $section = new NPMAuditSection($para);
                if ($section->isValid()) {
                    $sections []= $section;
                }
            }
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