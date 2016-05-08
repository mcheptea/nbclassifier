<?php
namespace Classifier;

/**
 * Trainer.
 *
 * User: Mark Cheptea
 * Date: 25-Apr-16
 * Time: 00:01
 */
class Trainer extends AbstractClassifier
{
    /**
     * Trainer constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Associates a document to a class.
     *
     * @param string $document The document contents.
     * @param string $class The assigned class
     */
    public function train($document, $class)
    {
        $words = $this->normalize(explode(" ", $document));

        //add document to class
        $this->store->addClass($class);

        foreach ($words as $word) {
            //train word to class
            $this->store->addWord($word, $class);
        }
    }

    /**
     * Removes the association between a document and a class.
     *
     * @param string $document The document contents
     * @param string $class The assigned class.
     */
    public function unTrain($document, $class)
    {
        $words = $this->normalize(explode(" ", $document));

        //remove document from class
        $this->store->removeClass($class);

        foreach ($words as $word) {
            $this->store->removeWord($word, $class);
        }
    }
}
