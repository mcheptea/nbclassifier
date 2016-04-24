<?php
namespace Classifier;

use Classifier\Storage\RedisStorage;

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
    public function __construct() {
        parent::__construct();
    }

    /**
     * Associates a list of words to a class.
     *
     * @param $words The list of words
     * @param $class  The assigned class
     */
    public function train($words, $class) {
        $words = $this->normalize(explode(" ", $words));
        foreach($words as $w) {
            $this->store->trainTo(html_entity_decode($w), $class);
        }
    }

    /**
     * Removes the association between a list of words and a class.
     *
     * @param $words The list of words.
     * @param $class The assigned class.
     */
    public function unTrain($words, $class) {
        $words = $this->normalize(explode(" ", $words));
        foreach($words as $w) {
            $this->store->deTrainFromSet(html_entity_decode($w), $class);
        }
    }
}