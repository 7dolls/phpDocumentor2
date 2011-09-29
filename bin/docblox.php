#!/usr/bin/env php
<?php
/**
 * DocBlox
 *
 * @category  DocBlox
 * @package   CLI
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://docblox-project.org
 */

// check whether xhprof is loaded
$profile = false;
if (extension_loaded('xhprof')) {

    // check whether one of the arguments is --profile; this will enable the profiler
    $profile = array_search('--profile', $argv);
    if (false !== $profile) {
        unset($_SERVER['argv'][$profile]);
        $_SERVER['argc']--;
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
    }
}

// determine base include folder, if @php_dir@ contains @php_dir then
// we did not install via PEAR
$bootstrap_folder = (strpos('@php_dir@', '@php_dir') === 0)
    ? dirname(__FILE__) . '/../src'
    : '@php_dir@/DocBlox/src';
require($bootstrap_folder . '/DocBlox/Bootstrap.php');

$autoloader = DocBlox_Bootstrap::createInstance()->registerAutoloader();

$application = new DocBlox_Core_Application();
$application->main($autoloader);

if (false !== $profile) {
    include_once 'XHProf/utils/xhprof_lib.php';
    include_once 'XHProf/utils/xhprof_runs.php';

    $xhprof_data = xhprof_disable();
    if ($xhprof_data !== null) {
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, 'docblox');
        $profiler_url = sprintf('index.php?run=%s&source=%s', $run_id, 'docblox');
        echo 'Profile can be found at: ' . $profiler_url . PHP_EOL;
    }
}

// disable E_STRICT reporting on the end to prevent PEAR from throwing Strict warnings.
error_reporting(error_reporting() & ~E_STRICT);
