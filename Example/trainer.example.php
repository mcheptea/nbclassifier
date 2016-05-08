<?php
/**
 * This script trains the classifier using the example dataset.
 * You can train the same unmodified dataset multiple times, without affecting the classifier outcome.
 *
 * User: Mark Cheptea
 * Date: 08-May-16
 * Time: 16:57
 */
require_once(__DIR__ . "/../vendor/autoload.php");

use Classifier\Trainer;

/* Retrieve the documents*/
echo "Retrieving the documents...\n";
$documents = file_get_contents(__DIR__ . "/Datasets/training.set.json");
$documents = json_decode($documents, true);
echo "\tFound " . count($documents) . " documents\n\n";

/* Train the documents */
echo "Training the documents...\n";
$stime = microtime(true);
$trainer = new Trainer();

foreach ($documents as $document) {
    foreach ($document['classes'] as $class) {
        try {
            $trainer->train($document['document'], $class);
        } catch (Exception $e) {
            echo "The trainer failed with message: ". $e->getMessage() ."\n";
            die(1);
        }
        echo "\t". $document['document'] . " => " . $class . "\n";
    }
}

/* Print result information */
echo "\n\nFinished.\n";
$time = microtime(true) - $stime;
echo "\tTrained " . count($documents) . " documents in " . number_format($time, 3) . " seconds.";
