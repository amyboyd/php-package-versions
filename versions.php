#!/usr/bin/env php
<?php

if (count($_SERVER['argv']) !== 2 || !$_SERVER['argv'][1]) {
	die('Give a package. For example: php versions.php symfony/symfony' . "\n");
}

$package = $_SERVER['argv'][1];
echo 'Package is: ' . $package . "\n";

$allStats = json_decode(file_get_contents('https://packagist.org/packages/' . $package . '/stats.json'), true);

$csvHeaders = null;
$hasSetHeaders = false;
$downloadCount = [];

foreach ($allStats['versions'] as $i => $specificVersion) {
	$match = null;
	if (preg_match('/^v(\d\.\d+)/', $specificVersion, $match)) {
		$aggregateVersion = $match[1];
		echo 'Grouping ' . $specificVersion . ' into ' . $aggregateVersion . "\n";
	} else if (preg_match('/^(\d\.\d+)/', $specificVersion, $match)) {
		$aggregateVersion = $match[1];
		echo 'Grouping ' . $specificVersion . ' into ' . $aggregateVersion . "\n";
	} else {
		echo 'Skipping unknown version: ' . $specificVersion . "\n";
		continue;
	}

	$versionStats = json_decode(file_get_contents('https://packagist.org/packages/' . $package . '/stats/' . $specificVersion . '.json?average=monthly&from=2015-06-01'), true);

	if (!$hasSetHeaders) {
		$csvHeaders = array_merge([''], $versionStats['labels']);
		$hasSetHeaders = true;
	}

	if (!isset($downloadCount[$aggregateVersion])) {
		$downloadCount[$aggregateVersion] = [];
	}

	foreach ($versionStats['values'] as $ii => $specificVersionDownloadCount) {
		if (!isset($downloadCount[$aggregateVersion][$ii])) {
			$downloadCount[$aggregateVersion][$ii] = 0;
		}
		$downloadCount[$aggregateVersion][$ii] += $specificVersionDownloadCount;
	}
}

$csvHandle = fopen('php://memory', 'r+');

fputcsv($csvHandle, $csvHeaders);
foreach ($downloadCount as $aggregateVersion => $countsPerMonth) {
	fputcsv($csvHandle, array_merge([$aggregateVersion], $countsPerMonth));
}

rewind($csvHandle);
$csv = stream_get_contents($csvHandle);
fclose($csvHandle);

$outputFile = str_replace('/', '-', $package) . '.csv';
file_put_contents($outputFile, $csv);

echo 'The average number of downloads per day, grouped by version and month, has been written to:' . "\n";
echo $outputFile . "\n";
