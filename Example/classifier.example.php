<?php
/**
 * This script classifies the list of test documents and outputs the result.
 *
 * User: mark
 * Date: 08-May-16
 * Time: 18:12
 */
require_once(__DIR__ . "/../vendor/autoload.php");

use Classifier\Classifier;

/* Retrieve the documents*/
echo "Retrieving the documents...\n";
$documents = file_get_contents(__DIR__ . "/Datasets/test.set.json");
$documents = json_decode($documents, true);
echo "\tFound " . count($documents) . " documents\n\n";

/* Train the documents */
echo "Classifying the documents...\n";
$stime = microtime(true);
$classifier = new Classifier();

foreach ($documents as $document) {
    try {
        $classes = $classifier->classify($document['document'], 5);
    } catch (Exception $e) {
        echo "The classifier failed with message: ". $e->getMessage() ."\n";
        die(1);
    }
    echo "\t". $document['document'] . "\n";
    foreach ($classes as $class => $probability) {
        echo "\t\t" . $class . "(" . $probability . ")\n";
    }
}

/* Print result information */
echo "\n\nFinished.\n";
$time = microtime(true) - $stime;
echo "\tClassified " . count($documents) . " documents in " . number_format($time, 3) . " seconds.";
