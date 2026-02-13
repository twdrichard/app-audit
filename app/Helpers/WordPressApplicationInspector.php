<?php

/**
 * @file WordPressApplicationInspector.php
 * @author Richard@TowerWebDesign.co.uk
 **/

namespace App\Helpers;

use App\Helpers\Server;
use App\Helpers\ApplicationInspector;

class WordPressApplicationInspector extends ApplicationInspector {
    protected bool $has_cli;
    protected string $url;

    public function isOnServer(Server $server) {    // nb check for wp-config file not wp cli?
        $this->has_cli = false;
        $this->server = $server;
        $this->url = '';

        if (!$this->server->fileExists('wp-config.php')) {
            return false;
        }

        // first check if we have WordPress instance here
        $info = $server->executeCommand($this->buildWPCommand("core version"));
        $command_result = $server->getLastCommandResult();
        if ($command_result > 0 || strpos($info, "none.") !== false) {
			$this->is_valid = false;
			return false;
        }

        $info = $server->executeCommand("wp --version");
        if (strpos($info, "WP-CLI") !== false) {
            // we've found wp-cli
            $this->has_cli = true;
            return true;
        } else {
            return false;
        }
    }

    public function getName() : string {
        return "WordPress";
    }

    public function getTitle() : string {
        $colors = $this->getColors();
        return "WordPress audit on " . date('j M Y') . PHP_EOL . $colors['yellow'] . $this->getDomain();
    }

    /**
     * @function getDomain
     * Use wp "db query" command to look up the siteurl
     * NB directly on the command line we can use
     * wp --path=/var/www/html/wp/ db query "SELECT option_value FROM wp_options WHERE option_name='siteurl' LIMIT 1;"
     **/

    public function getDomain() : string {
        if ($this->url == '') {
            $command = "option get siteurl";
            $command = $this->buildWPCommand($command);
            $this->url = $this->server->executeCommand($command);
        }
        $this->url = $this->removePHPErrors($this->url);
        return $this->formatUrlForDisplay($this->url);
    }

    public function getDescription() {
        $colors = $this->getColors();
        $s = $colors['green'] . "Linux version: " . $this->server->findLinuxPrettyName() . PHP_EOL;
        $s .= $colors['blue'] .  "WordPress, core version " . $this->getCoreVersion();
        if ($this->coreIsOutOfDate()) {
			  $s .= $colors['red'] . ' (out of date)';
			} else {
			  $s .= $colors['green'] . ' (latest version)';
			}
			$s .= PHP_EOL;
        $s.= $this->getPluginsList();
        return $s;
    }

    protected function buildWPCommand(string $command) : string {
        $folder = $this->server->getFolder();
        return "wp --path=$folder " . $command;
    }

    protected function getCoreVersion() : string {
        if ($this->has_cli) {
            $command = $this->buildWPCommand("core version");
            return $this->server->executeCommand($command);
        } else {
            return "unknown";
        }
    }

    protected function coreIsOutOfDate() : bool {
        if ($this->has_cli) {
            $command = $this->buildWPCommand("core check-update");
            $response = $this->server->executeCommand($command);
            if (strpos($response, "Success: WordPress is at the latest version.") !== false) {
				return false;		// ok we're already at the latest version
			}
        }
		return true;
    }

    public function hasLogs() : bool {
        return $this->hasDebugLog();
    }

    /**
     * @function hasDebugLog
     * @return bool if wp is in debug mode and debug log file is available
     * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
     **/

    protected function hasDebugLog() : bool {
        $config_filename = $this->server->getFolder() . DIRECTORY_SEPARATOR . "wp-config.php";
        $command = "grep DEBUG $config_filename";
        $debug_info = $this->server->executeCommand($command);
        $debug_settings = explode(PHP_EOL, $debug_info);
        if ($debug_settings) {
            $wp_debug = false;
            $wp_debug_log = false;
            foreach ($debug_settings as $debug_setting) {
                $debug_setting = str_replace('define(', '', $debug_setting);
                $debug_setting = str_replace("'", '', $debug_setting);
                $debug_setting = str_replace(";", '', $debug_setting);
                $debug_setting = trim(str_replace(')', '', $debug_setting));
                $setting_ar = explode(',', $debug_setting);
                if ($setting_ar && count($setting_ar) > 1) {
                    $setting_name = trim($setting_ar[0]);
                    $value = strtolower(trim($setting_ar[1]));
                    if ($setting_name == 'WP_DEBUG') {
                        $wp_debug = ($value == 'true');
                    }
                    if ($setting_name == 'WP_DEBUG_LOG') {
                        $wp_debug_log = ($value == 'true');
                    }
                }
            }
        }
        if ($wp_debug_log && $wp_debug) {
            return true;
        }
        return false;
    }

    protected function findDebugLogFilename() : string {
        $debug_filename = $this->server->getFolder() . DIRECTORY_SEPARATOR .  "wp-content" . DIRECTORY_SEPARATOR . "debug.log";
        return $debug_filename;
    }

    public function getLogLines() : array {
        $log_filename = $this->findDebugLogFilename();
        if ($log_filename) {
            $command = "tail -10 $log_filename";
            $output = $this->server->executeCommand($command);
            if ($output) {
                $ar = explode(PHP_EOL, $output);
                return $ar;
            }
        }
        return [];
    }

    protected function getPluginsList() : string {
        if ($this->has_cli) {
            $folder = $this->server->getFolder();
            $plugin_list = $this->server->executeCommand("wp --path=$folder plugin list");
            $active_list = $this->formatPluginsList($plugin_list, true);
            $inactive_list = $this->formatPluginsList($plugin_list, false);
            if ($inactive_list != '') {
                return $active_list . PHP_EOL . $inactive_list;
            } else {
                return $active_list;
            }
        } else {
            return "Plugins not found." . PHP_EOL;
        }
    }

    protected function formatPluginsList(string $plugins_list, $active_only = true) {
        $lines = explode(PHP_EOL, $plugins_list);
        $colors = $this->getColors();
        $plugin_lines = [];
        if ($active_only) {
            $active_status = 'active';
        } else {
            $active_status = 'inactive';
        }
        $num_added = 0;
        $num_lines = count($lines);
        if ($lines && $num_lines > 2) {
            for ($i = 0; $i < $num_lines; $i++) {
                if ($i > 0 && $i < $num_lines+1) {
                    $line = $lines[$i];
                    $stripped_line = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $line);       // remove multiple spaces
                    if ($stripped_line) {
                        $plugin_info = explode(' ', $stripped_line);
                        if ($plugin_info && count($plugin_info) > 3) {
                            $plugin_name = $plugin_info[0];
                            $status = $plugin_info[1];
                            if ($status == $active_status) {
                                $num_added++;
                                $update_available = $plugin_info[2];
                                $version = $plugin_info[3];
                                $plugin_description = $colors['blue'];
                                $plugin_description .= $plugin_name;
                                if ($update_available == 'available') {
                                    $plugin_description .= $colors['red'];
                                    $plugin_description .= ' v' . $version . ' (out of date)';
                                } else {
                                    $plugin_description .= ' v' . $version;
                                }
                                $plugin_lines []= '   ' . $plugin_description;
                            }
                        }
                    }
                }
            }
        }
        if ($num_added == 0) {
            return '';
        }
        return ucfirst($active_status) . " plugins:" . PHP_EOL . implode(PHP_EOL, $plugin_lines);
    }

    public function getAsciiLogoFilename() : string {
        return 'wordpress-ascii-logo.txt';
    }
}