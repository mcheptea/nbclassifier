<?php
namespace Classifier;

class Classifier {
	
	private $store;
	private $debug = TRUE;
	
	public function __construct($conf = array()) {
		if(!empty($conf['debug']) && $conf['debug'] === TRUE)
			$this->debug = TRUE;
			
		switch($conf['store']['mode']) {
			case 'redis':
				require_once 'RedisStorage.php';
				$this->store = new AbstractStoreRedis($conf['store']['db']);
				break;
		}
	}
	
	public function train($words, $set) {
		$words = $this->cleanKeywords(explode(" ", $words));
		foreach($words as $w) {
			$this->store->trainTo(html_entity_decode($w), $set);
		}
	}

	public function deTrain($words, $set) {
		$words = $this->cleanKeywords(explode(" ", $words));
		foreach($words as $w) {
			$this->store->deTrainFromSet(html_entity_decode($w), $set);
		}
	}

	public function classify($words, $count = 10, $offset = 0) {
		$P = array();
		$score = array();

		// Break keywords
		$keywords = $this->cleanKeywords(explode(" ", $words));

		// All sets
		$sets = $this->store->getAllSets();
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
	
	public function blacklist($words = array()) {
		$clean = array();
		if(is_string($words)) {
			$clean = array($words);
		}
		else if(is_array($words)) {
			$clean = $words;
		}
		$clean = $this->cleanKeywords($clean);
		
		foreach($clean as $word) {
			$this->store->addToBlacklist($word);
		}
	}

	private function cleanKeywords($kw = array()) {
		if(!empty($kw)) {
			$ret = array();
			foreach($kw as $k) {
				$k = strtolower($k);
				$k = preg_replace("/[^a-z]/i", "", $k);

				if(!empty($k) && strlen($k) > 2) {
					$k = strtolower($k);
					if(!empty($k))
						$ret[] = $k;
				}
			}
			return $ret;
		}
	}
	
	private function isBlacklisted($word) {
		return $this->store->isBlacklisted($word);
	}
	
	private function _debug($msg) {
		if($this->debug)
			echo $msg . PHP_EOL;
	}
	
}
