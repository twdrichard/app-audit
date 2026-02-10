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

        // first check if we have WordPress instance here
        $info = $server->executeCommand($this->buildWPCommand("core version"));
        if (strpos($info, "none.") !== false) {
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
        return $this->url;
    }

    public function getDescription() {
        $colors = $this->getColors();
        $s = $colors['green'] . "Linux version: " . $this->server->findLinuxPrettyName() . PHP_EOL;
        //$s .= $colors['yellow'] . $this->getDomainAndInfo() . PHP_EOL;
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