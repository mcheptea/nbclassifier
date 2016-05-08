<?php
/**
 * This scripts trains the classifier using the example dataset.
 *
 * User: Mark Cheptea
 * Date: 08-May-16
 * Time: 16:57
 */
require_once(__DIR__ . "/../vendor/autoload.php");

use Classifier\Trainer;

echo "Retrieving the documents...\n";
$documents = file_get_contents(__DIR__ . "/Datasets/training.set.json");
$documents = json_decode($documents, true);

echo "Training the documents...\n";
$stime = microtime();
$trainer = new Trainer();

foreach ($documents as $document) {
    foreach ($document['classes'] as $class) {
        try {
            $trainer->train($document['document'], $class);
        } catch (Exception $e) {
            echo "The trainer failed with message: ". $e->getMessage() ."\n";
            die(1);
        }
        echo $document['document'] . " => " . $class;
    }
}

echo "Finished.";
$time = microtime() - $stime;
echo "Trained " . count($documents) . " documents in " . $time . " seconds.";
