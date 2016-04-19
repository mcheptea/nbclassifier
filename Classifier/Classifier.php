<?php
namespace Classifier;

use Classifier\Storage\RedisStorage;

/**
 * Classifier
 * Provides a series of method for classification.
 *
 * @package Classifier
 */
class Classifier {
	
	private $store;

    /**
     * Classifier constructor.
     */
	public function __construct() {
        $this->store = new RedisStorage();
	}

    /**
     * Associates a list of words to a class.
     *
     * @param $words The list of words
     * @param $class  The assigned class
     */
	public function train($words, $class) {
		$words = $this->cleanKeywords(explode(" ", $words));
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
	public function deTrain($words, $class) {
		$words = $this->cleanKeywords(explode(" ", $words));
		foreach($words as $w) {
			$this->store->deTrainFromSet(html_entity_decode($w), $class);
		}
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
		$keywords = $this->cleanKeywords(explode(" ", $words));

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

    /**
     * Normalizez a list of words by removing any special characters. 
     *
     * @param array $words
     * @return array
     */
	private function cleanKeywords($words = array()) {
		if(!empty($words)) {
			$result = array();
			foreach($words as $word) {
                $word = strtolower($word);
                $word = preg_replace("/[^a-z]/i", "", $word);

				if(!empty($word) && strlen($word) > 2) {
                    $word = strtolower($word);
					if(!empty($word))
						$result[] = $word;
				}
			}
			return $result;
		}
	}
}
