<?php

/**
 * @file SiteInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;
use App\Helpers\WordPressApplicationInspector;

class SiteInspector {
    protected Server $server;
    protected array $applications;
    protected $application;

    public function __construct(Server $server) {
        $this->server = $server;
        $this->application = null;
        $this->applications = [
            new WordPressApplicationInspector(),
            new PHPApplicationInspector(),
        ];
    }

    public function getName() : string {
        return "application";
    }

    public function getDomain() : string {
        return "https://example.com";
    }

    public function findApplicationType() : ?string {
        if ($this->application != null) {
            return $this->application->getName();
        }
        foreach ($this->applications as $application) {
            if ($application->isOnServer($this->server)) {
                $this->application = $application;
                return $application->getName();
            }
        }
        return null;
    }

    public function isValidInstallation() : bool {
       if ($this->application != null) {
            return $this->application->isValidInstallation();
        } else {
            return false;
        }
    }

    public function getDescription() : string {
        if ($this->application != null) {
            return $this->application->getDescription();
        } else {
            return "No application found." . PHP_EOL;
        }
    }

    public function getFormattedDescription() : string {
        if ($this->application != null) {
            $logo = $this->getAsciiLogo();
            $colors = $this->application->getColors();
            $description = $this->application->getDescription();
            return $this->combineTextSideBySide($logo, $description, $colors['yellow'], $colors['cyan']);
        } else {
            return "No application found." . PHP_EOL;
        }
    }

    protected function combineTextSideBySide(string $text1, string $text2, string $color1, string $color2, $column_width = 40) : string {
        $lines1 = explode(PHP_EOL, $this->padTextToEqualLineLength($text1));
        $lines2 = explode(PHP_EOL, $text2);
        $column_spacer = str_pad("", $column_width);
        $line_number = 0;
        $output = "";
        foreach ($lines1 as $line_left) {
            if (strlen($line_left) < $column_width) {
                $line_left = $column_spacer;
            }
            $output .= $color1 . $line_left;
            if (isset($lines2[$line_number])) {
                $output .= $color2 . $lines2[$line_number];
            }
            $output .= PHP_EOL;
            $line_number++;
        }
        if (count($lines2) > count($lines1)) {
            // right hand side still has content
            for ($i = $line_number; $i < count($lines2); $i++) {
                $output .= $column_spacer . $color2 . $lines2[$i] . PHP_EOL;
            }
        }
        return $output;
    }

    protected function padTextToEqualLineLength($text, $num_spaces_to_add = 3) : string {
        $lines = explode(PHP_EOL, $text);
        $max_line_length = $this->findMaxLineLength($lines);
        $line_length_required = $max_line_length + $num_spaces_to_add;
        $output = "";
        foreach ($lines as $line) {
            $output .= $this->padLineToLength($line, $line_length_required) . PHP_EOL;
        }
        return $output;
    }

    protected function findMaxLineLength($lines) : int {
        $max_length = 0;
        foreach ($lines as $line) {
            if (strlen($line) > $max_length) {
                $max_length = strlen($line);
            }
        }
        return $max_length;
    }

    protected function padLineToLength($line, $length_required) : string {
        $line_length = strlen($line);
        if ($line_length < $length_required) {
            $spaces_to_pad = $length_required - $line_length;
            for ($i = 0; $i < $spaces_to_pad; $i++) {
                $line .= ' ';
            }
        }
        return $line;
    }

    public function getAsciiLogo() : string {
        if ($this->application != null) {
            return $this->application->getAsciiLogo();
        } else {
            return "";
        }
    }
}