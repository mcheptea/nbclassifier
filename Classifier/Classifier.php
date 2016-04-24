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
	public function __construct() {
        parent::__construct();
	}

    /**
     * Classifies a list of words to a class.
     *
     * @param $words
     * @param int $count
     * @param int $offset
     * @return array
     */
	public function classify($words, $count = 10, $offset = 0) {
		$P = array();
		$score = array();

		// Break keywords
		$keywords = $this->normalize(explode(" ", $words));

		// All sets
		$sets = $this->store->getAllClasses();
		$P['sets'] = array();

		// Word counts in sets
		$setWordCounts = $this->store->getSetWordCount($sets);
		$wordCountFromSet = $this->store->getWordCountFromSet($keywords, $sets);

		foreach($sets as $set) {
			foreach($keywords as $word) {
				$key = "{$word}{$this->store->delimiter}{$set}";
				if($wordCountFromSet[$key] > 0)
					$P['sets'][$set] += $wordCountFromSet[$key] / $setWordCounts[$set];
			}

			if(!is_infinite($P['sets'][$set]) && $P['sets'][$set] > 0)
				$score[$set] = $P['sets'][$set];
		}

		arsort($score);

		return array_slice($score, $offset, $count-1);
	}
}
