#!/usr/bin/env php
<?php
/**
 * Naive Bayes Classifier Trainer (CLI utility)
 *
 * User: Mark Cheptea
 * Date: 08-May-16
 * Time: 21:56
 */
require_once(__DIR__ . "/../vendor/autoload.php");
use Classifier\Trainer;

$options = getopt("t:f:c:h");

if (isset($options['h']) || empty($options)) {
    showHelp();
    exit(0);
}

validateOptions($options);

$classes = array();
if (is_array($options['c'])) {
    $classes = $options['c'];
} else {
    $classes[] = $options['c'];
}

$document = "";
if (isset($options['t'])) {
    $document = $options['t'];
} else {
    $document = file_get_contents($options['f']);
}

//train
$trainer = new Trainer();

foreach ($classes as $class) {
    try {
        $trainer->train($document, $class);
    } catch (Exception $e) {
        echo "The trainer failed with message: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "OK!";
exit(0);

/* Functions */
function showHelp()
{
    echo "This utility allows you to train documents to classes.\n";
    echo "Documents can be fed to the trainer both as a text (.txt) file or as a string.\n\n";
    echo "Options:\n";
    echo "\t -t \tThe text as a string. Note: The text should be qualified with\n";
    echo "\t\tquotes and escaped.\n";
    echo "\t -c \tThe class(es) with which the class is associated.\n";
    echo "\t -f \tThe path to the file containing the text.\n";
    echo "\t -h \tDisplays this help message.\n";
    echo "\nExample:\n";
    echo "\t php nbtrainer -t \"Lorem ipsum...\" -c \"dummy text\" -c \"lorem\" \n";
}

function validateOptions($options)
{
    //classes
    if (!isset($options['c']) || empty($options['c'])) {
        echo "Fatal: No classes were indicated!";
        exit(1);
    }

    //text
    if (isset($options['t']) && isset($options['f'])) {
        echo "Fatal: Both a string a path was provided, please use only one option.";
        exit(1);
    } elseif (!isset($options['t']) && !isset($options['f'])) {
        echo "Fatal: Text content not provided! Use -t or -f to provide a string or a text file for classification.";
        exit(1);
    }

    if (isset($options['t'])) {
        if (empty($options['t'])) {
            echo "Fatal: An empty string was provided as text.";
            exit(2);
        }
    }

    if (isset($options['f'])) {
        if (empty($options['f'])
            || !file_exists($options['f'])
            || empty(file_get_contents($options['f']))
        ) {
            echo "Fatal: Cannot open the provided file!";
            exit(3);
        }
    }
}
