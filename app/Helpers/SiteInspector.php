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
            new JSApplicationInspector(),
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
            $title = $this->application->getTitle();
            $description = $title . PHP_EOL . PHP_EOL . $this->application->getDescription();
            $formatted_description = $this->combineTextSideBySide($logo, $description, $colors['yellow'], $colors['cyan']);
            $formatted_description .= $this->getLogsSummary();
            return $formatted_description;
        } else {
            return "No application found." . PHP_EOL;
        }
    }

    protected function getLogsSummary() : string {
        $colors = $this->application->getColors();
        $s = $colors['orange'] . 'Logs' . PHP_EOL .  $colors['blue'];
        if ($this->application->hasLogs()) {
            $log_entries = $this->application->getLogLines();
            foreach ($log_entries as $line) {
                $s .= $this->formatDisplayLine($line);
            }
        } else {
            $s .= "No log files found." . PHP_EOL;
        }
        return $s;
    }

    protected function formatDisplayLine(string $line, int $width = 80) : string {
        $s = "";
        $indent_required = false;
        $loop_no = 0;
        do {

            if ($indent_required) {
                $s .= '   ';
                $line_width = $width - 3;
            } else {
                $indent_required = true;
                $line_width = $width;
            }

            if (strlen($line) > $line_width) {
                $s .= substr($line, 0, $line_width) . PHP_EOL;
                $line = substr($line, $line_width);
            } else {
                $s .= $line . PHP_EOL;
                $line = '';
            }
        } while (strlen($line) > 0 && $loop_no++ < 10);
        return $s;
    }

    protected function combineTextSideBySide(string $text1, string $text2, string $color1, string $color2, $column_width = 40) : string {
        $left_lines = explode(PHP_EOL, $this->padTextToEqualLineLength($text1));
        $right_lines = explode(PHP_EOL, $text2);
        $num_right_lines = count($right_lines);
        $num_left_lines = count($left_lines);
        //echo "Displaying " . count($left_lines) . ", " . count($right_lines) . " lines" . PHP_EOL;
        $column_spacer = str_pad("", $column_width);
        $output = "";
        if ($num_right_lines < $num_left_lines) {
            // we have less lines on the right, so lets add some empty lines to center vertically
            $num_padding_lines = ($num_left_lines - $num_right_lines) / 2;
            $right_lines = $this->padArrayWithBlankLines($right_lines, $num_padding_lines - 1, $column_width, $num_left_lines);
            $num_right_lines = $num_left_lines;
        }
        // now check if column1 is too long
        if ($num_left_lines < $num_right_lines) {
            // we have less lines on the right, so lets add some empty lines to center vertically
            $num_padding_lines = ($num_right_lines - $num_left_lines) / 2;
            $left_lines = $this->padArrayWithBlankLines($left_lines, $num_padding_lines, $column_width, $num_right_lines);
            $num_left_lines = $num_right_lines;
        }

        $line_number = 0;
        foreach ($left_lines as $line_left) {
            $output .= $color1 . $line_left;
            if (isset($right_lines[$line_number])) {
                $output .= $color2 . $right_lines[$line_number];
            }
            $output .= PHP_EOL;
            $line_number++;
        }
        if (count($right_lines) > count($left_lines)) {
            for ($i = $line_number; $i < count($right_lines); $i++) {
                $output .= $column_spacer . $color2 . $right_lines[$i] . PHP_EOL;
            }
        }
        return $output;
    }

    protected function padArrayWithBlankLines($ar, $num_padding_lines, $column_width, $total_lines) {
        if ($num_padding_lines > 0) {
            //echo "Adding $num_padding_lines padding lines to column" . PHP_EOL;
            $blank_line = str_pad('', $column_width);
            for ($i = 0; $i < $num_padding_lines; $i++) {
                array_unshift($ar, $blank_line);
            }
            while (count($ar) < $total_lines) {
                $ar []= $blank_line;
            }
        }
        return $ar;
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