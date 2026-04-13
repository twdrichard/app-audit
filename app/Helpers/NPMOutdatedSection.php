<?php

/**
 * @file NPMAuditSection.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

class NPMOutdatedSection {
    protected string $line, $package, $current, $wanted, $latest, $location, $depended_by;

    public function __construct(string $line) {
        $this->line = $line;
        $this->parse($line);
    }

    protected function parse(string $line) {
        // strip multiple spaces
        if ($this->isValid()) {
            $line = preg_replace('!\s+!', ' ', $line);
            $columns = explode(' ', $line);
            if ($columns && count($columns) >= 6) {
                $this->package = $columns[0];
                $this->current = $columns[1];
                $this->wanted = $columns[2];
                $this->latest = $columns[3];
                $this->location = $columns[4];
                $this->depended_by = $columns[5];
            }
        }
    }

    public function getSummary(array $colors) : string {
        if (!$this->isValid()) {
            return '';
        }
        return $this->package . ' v' . $this->current .  $colors['orange'] . ' (latest ' . $this->latest . ')' . PHP_EOL;
    }

    public function isValid() : bool {
        if (strpos($this->line, "Package") === 0) {
            return false;       // header line
        }
        if (!isset($this->package)) {
            return false;
        }
        return $this->line != "";
    }
}