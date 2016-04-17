<?php
namespace Classifier\Storage;

class RedisStorage extends AbstractStorage {
	
	private $conn;

	private $namespace	= 'nbc-ns';
	private $blacklist 	= 'nbc-blacklists';
	private $words 		= "nbc-words";
	private $sets 		= "nbc-sets";
	private $cache		= "nbc-cache";
	public $delimiter	= "_--%%--_";
	private $wordCount	= "--count--";
	
	function __construct($conf = array()) {
		$this->namespace = $conf['namespace'];

		// Namespacing
		$this->blacklist	= "{$this->namespace}-{$this->blacklist}";
		$this->words		= "{$this->namespace}-{$this->words}";
		$this->sets			= "{$this->namespace}-{$this->sets}";
		$this->cache		= "{$this->namespace}-{$this->cache}";
				
		// Redis connection	
        $this->conn = new Redis();
        $this->conn->connect($conf['db_host'], $conf['db_port']);
		$this->conn->select(77);
	}
	
	public function close() {
		$this->conn->close();
	}
	
	public function addToBlacklist($word) {
		return $this->conn->incr("{$this->blacklist}#{$word}");
	}
	
	public function removeFromBlacklist($word) {
		return $this->conn->set("{$this->blacklist}#{$word}", 0);
	}
	
	public function isBlacklisted($word) {
		$res = $this->conn->get("{$this->blacklist}#{$word}");
		return !empty($res) && $res > 0 ? TRUE : FALSE;
	}
	
	public function trainTo($word, $set) {
		// Words
		$this->conn->hIncrBy($this->words, $word, 1);
		$this->conn->hIncrBy($this->words, $this->wordCount, 1);

		// Sets
		$key = "{$word}{$this->delimiter}{$set}";
		$this->conn->hIncrBy($this->words, $key, 1);
		$this->conn->hIncrBy($this->sets, $set, 1);
	}

	public function deTrainFromSet($word, $set) {
		$key = "{$word}{$this->delimiter}{$set}";

		$check = $this->conn->hExists($this->words, $word) &&
			$this->conn->hExists($this->words, $this->wordCount) &&
			$this->conn->hExists($this->words, $key) &&
			$this->conn->hExists($this->sets, $set);

		if($check) {
			// Words
			$this->conn->hIncrBy($this->words, $word, -1);
			$this->conn->hIncrBy($this->words, $this->wordCount, -1);

			// Sets
			$this->conn->hIncrBy($this->words, $key, -1);
			$this->conn->hIncrBy($this->sets, $set, -1);

			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	public function getAllSets() {
		return $this->conn->hKeys($this->sets);
	}
	
	public function getSetCount() {
		return $this->conn->hLen($this->sets);
	}
	
	public function getWordCount($words) {
		return $this->conn->hMGet($this->words, $words);
	}
	
	public function getAllWordsCount() {
		return $this->conn->hGet($this->wordCount, $this->wordCount);
	}
	
	public function getSetWordCount($sets) {
		return $this->conn->hMGet($this->sets, $sets);
	}
	
	public function getWordCountFromSet($words, $sets) {
		$keys = array();
		foreach($words as $word) {
			foreach($sets as $set) {
				$keys[] = "{$word}{$this->delimiter}{$set}";
			}
		}
		return $this->conn->hMGet($this->words, $keys);
	}
	
}