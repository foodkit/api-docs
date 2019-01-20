#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Scripts\Commands\MergeCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new MergeCommand());
$application->run();