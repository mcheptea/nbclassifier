<?php
namespace Classifier;

/**
 * Classifier
 * Provides a series of methods for document classification.
 *
 * @package Classifier
 */
class Classifier extends AbstractClassifier
{
    /**
     * Classifier constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Classifies a document string and returns the top N predicted classes and their scores.
     *
     * @param string    $document   The document to classify
     * @param int       $top        Number of top classes to return.
     *
     * @return array    A descendingly sorted list of classes and their scores. Example: array("class" => "score", ...)
     */
    public function classify($document, $top = 5)
    {
        $words = $this->normalize(explode(" ", $document));

        $classes = $this->store->getAllClasses();

        //prior probabilities
        $priors = array();
        $totalDocuments = $this->store->getDocumentCount();
        foreach ($classes as $class) {
            $priors[$class] = $this->store->getClassCount($class)/$totalDocuments;
        }

        //vocabulary
        $vocabulary = $this->store->countVocabulary();

        //conditional probabilities
        $conditionals = array();
        foreach ($classes as $class) {
            foreach ($words as $word) {
                $countWordInClass = $this->store->countWordInClass($word, $class);
                $wordsInClass = $this->store->countWordsInClass($class);
                $conditionals[$class][$word] = ($countWordInClass + 1) / ($wordsInClass + $vocabulary + 1);
            }
        }

        //probabilities
        $probabilities = array();
        foreach ($classes as $class) {
            $probabilities[$class] = $priors[$class] * array_product($conditionals[$class]);
        }

        //sort
        arsort($probabilities);

        return array_slice($probabilities, 0, $top);
    }
}
