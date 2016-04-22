<?php
namespace Classifier\Storage;

use Redis;

/**
 * RedisStorage storage driver.
 *
 * @package Classifier\Storage
 */
class RedisStorage extends AbstractStorage {
	
	private $conn;

	private $nsPrefix	= 'nbc-ns';
	private $nsWord 	= "nbc-words";
	private $nsClass	= "nbc-sets";
	private $nsCache	= "nbc-cache";
	public $delimiter	= "_--%%--_";
	private $wordCount	= "--count--";
	
	function __construct($conf) {
		$this->nsPrefix = $conf['namespace'];

		// Namespacing
		$this->nsWord		= "{$this->nsPrefix}-{$this->nsWord}";
		$this->nsClass		= "{$this->nsPrefix}-{$this->nsClass}";
		$this->nsCache		= "{$this->nsPrefix}-{$this->nsCache}";
				
		// Redis connection	
        $this->conn = new Redis();
        $this->conn->connect($conf['host'], $conf['port']);
		$this->conn->select($conf['database']);
	}
	
	public function close() {
		$this->conn->close();
	}

	/**
	 * Associate word to class
	 * 
	 * @param $word The word to associate.
	 * @param $class The class
	 */
	public function trainTo($word, $class) {
		// Words
		$this->conn->hIncrBy($this->nsWord, $word, 1);
		$this->conn->hIncrBy($this->nsWord, $this->wordCount, 1);

		// Sets
		$key = "{$word}{$this->delimiter}{$class}";
		$this->conn->hIncrBy($this->nsWord, $key, 1);
		$this->conn->hIncrBy($this->nsClass, $class, 1);
	}

	/**
	 * Remove association between word and class.
	 *
	 * @param $word
	 * @param $class
	 *
	 * @return bool
	 */
	public function deTrainFromSet($word, $class) {
		$key = "{$word}{$this->delimiter}{$class}";

		$check = $this->conn->hExists($this->nsWord, $word) &&
			$this->conn->hExists($this->nsWord, $this->wordCount) &&
			$this->conn->hExists($this->nsWord, $key) &&
			$this->conn->hExists($this->nsClass, $class);

		if($check) {
			// Words
			$this->conn->hIncrBy($this->nsWord, $word, -1);
			$this->conn->hIncrBy($this->nsWord, $this->wordCount, -1);

			// Sets
			$this->conn->hIncrBy($this->nsWord, $key, -1);
			$this->conn->hIncrBy($this->nsClass, $class, -1);

			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	/**
	 * Retrieves and returns a list of classes.
	 *
	 * @return array A full list of sets.
	 */
	public function getAllClasses() {
		return $this->conn->hKeys($this->nsClass);
	}

	/**
	 * Retrieves and returns the class count.
	 *
	 * @return int The class count.
	 */
	public function getClassCount() {
		return $this->conn->hLen($this->nsClass);
	}

	/**
	 * Retrieves the total count for a given word
	 *
	 * @param $word The word for which to retrieve the count.
	 * @return array
	 */
	public function getWordCount($word) {
		return $this->conn->hMGet($this->nsWord, $word);
	}

	/**
     * Retrieves the global word count. A count of all the words in the training set.
     *
	 * @return string The overall word count
	 */
	public function getTotalWordCount() {
		return $this->conn->hGet($this->wordCount, $this->wordCount);
	}

    /**
     * Retrieves the total word count for a set.
     *
     * @param $set The counted set
     * @return array
     */
	public function getSetWordCount($set) {
		return $this->conn->hMGet($this->nsClass, $set);
	}

    /**
     * Retrieves a list of word counts for a list of sets.
     *
     * @param $words List of words.
     * @param $sets List of sets
     * @return array The list of words and their counts.
     */
	public function getWordCountFromSet($words, $sets) {
		$keys = array();
		foreach($words as $word) {
			foreach($sets as $set) {
				$keys[] = "{$word}{$this->delimiter}{$set}";
			}
		}
		return $this->conn->hMGet($this->nsWord, $keys);
	}
}
