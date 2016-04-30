<?php
namespace Classifier\Storage;

use Predis\Client;
use Classifier\Config;

/**
 * RedisStorage storage driver.
 *
 * @package Classifier\Storage
 * @author Mark Cheptea <m.celmare@gmail.com>
 */
class RedisStorage {

    private $redis;

    public function __construct()
    {
        $this->redis = new Client([
            "scheme" => "tcp",
            "host" => Config::get("redis.host"),
            "port" => Config::get("redis.port"),
            "database" => Config::get("redis.database")
        ]);
    }

    /**
     * Adds a document class.
     *
     * @param string $class The class name
     */
    public function addClass($class)
    {
        if (!$this->existsClass($class)) {
            $this->redis->hset("classes", $class, 1);
        } else {
            $this->redis->hIncrBy("classes", $class, 1);
        }
    }

    /**
     * Removes a document class from storage
     *
     * @param string $class The class name
     */
    public function removeClass($class)
    {
        if ($this->existsClass($class)) {
            $this->redis->hIncrBy("classes", $class, -1);

            if ($this->redis->hGet("classes", $class) <= 0) {
               $this->redis->hDel("classes", $class);
            }
        }
    }

    /**
     * Returns the full class list.
     *
     * @return array<string> List of classes.
     */
    public function getAllClasses()
    {
       return $this->redis->hkeys("classes");
    }

    /**
     * Checks if a class exists in storage
     *
     * @param string $class The class name
     * @return bool
     */
    public function existsClass($class)
    {
        return $this->redis->hExists("classes", $class);
    }

    /**
     * Returns the number of documents in a class
     *
     * @param string $class The class name
     * @return int The number of documents
     */
    public function getClassCount($class)
    {
        if ($this->existsClass($class)) {
            return (int)$this->redis->hGet("classes", $class);
        } else {
            return 0;
        }
    }

    /**
     * The total number of classified documents.
     *
     * @return int The total number of documents
     */
    public function getDocumentCount()
    {
        $classes = $this->redis->hgetall("classes");

        if (empty($classes)) {
            return 0;
        }

        $total = 0;
        foreach ($classes  as $class) {
            $total += $class;
        }

        return $total;
    }


    /**
     * Persists words and their corresponding clsasses.
     *
     * @param string $word The word
     * @param string $class The class
     */
    public function addWord($word, $class)
    {
        //increment class:word
        if ($this->redis->hexists("words", $class."#".$word)) {
            $this->redis->hincrby("words", $class."#".$word, 1);
        } else {
            $this->redis->hset("words", $class."#".$word, 1);
        }

        //increment classWordCount
        if ($this->redis->hexists("classWordCount", $class)) {
            $this->redis->hincrby("classWordCount", $class, 1);
        } else {
            $this->redis->hset("classWordCount", $class, 1);
        }

        //increment vocabulary (number of unique words)
        if (!$this->redis->hexists("vocabulary", $word)) {
            $this->redis->hset("vocabulary", $word, 0);
        } else {
            $this->redis->hincrby("vocabulary", $word, 1);
        }
    }

    /**
     * Removes word from storage.
     *
     * @param string $word
     * @param string $class
     */
    public function removeWord($word, $class)
    {
        //decrement class:word
        if ($this->redis->hExists("words", $class."#".$word)) {
            $this->redis->hIncrBy("words", $class."#".$word, -1);

            if ($this->redis->hGet("words", $class."#".$word) <= 0) {
                $this->redis->hDel("words", $class."#".$word);
            }
        }

        //decrement classWordCount
        if ($this->redis->hexists("classWordCount", $class)) {
            $this->redis->hincrby("classWordCount", $class, -1);

            if ($this->redis->hGet("classWordCount", $class) <= 0) {
                $this->redis->hDel("classWordCount", $class);
            }
        }

        //decrement vocabulary
        if ($this->redis->hexists("vocabulary", $word)) {
            $this->redis->hincrby("vocabulary", $word, -1);
        }
    }

    /**
     * Returns the number of word occurrences in class.
     *
     * @param string $word
     * @param string $class
     * @return int The number of occurrences of a word in class.
     */
    public function countWordInClass($word, $class)
    {
        if ($this->redis->hexists("words", $class."#".$word)) {
            return $this->redis->hget("words", $class."#".$word);
        } else {
            return 0;
        }
    }

    /**
     * Returns the total number of words in a given class.
     *
     * @param string $class
     * @return int the number of words in the class
     */
    public function countWordsInClass($class)
    {
        if ($this->redis->hexists("classWordCount", $class)) {
            return $this->redis->hget("classWordCount", $class);
        } else {
            return 0;
        }
    }

    /**
     * Returns the number of words in the vocabulary.
     *
     * @return int The vocabulary size
     */
    public function countVocabulary()
    {
        if ($this->redis->exists("vocabulary")) {
            return $this->redis->hlen("vocabulary");
        } else {
            return 0;
        }
    }
}
