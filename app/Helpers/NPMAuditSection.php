<?php

/**
 * @file NPMAuditSection.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\NPMAuditSection;

class NPMAuditSection {
    protected string $text;
    protected array $lines;

    public function __construct(string $text) {
        $this->text = $text;
        //$this->lines = explode(PHP_EOL, $this->text);
        $this->lines = explode("\r\n", $this->text);
    }

    public function getSummary() {
        return "Section: " . $this->lines[0];
    }

    public function isValid() {
        if ($this->lines && count($this->lines) && $this->lines[0] != "") {
            return true;
        } else {
            return false;
        }
    }
}