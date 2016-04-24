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
    public function addDocumentClass($class)
    {
        if (!$this->existsDocumentClass($class)) {
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
    public function removeDocumentClass($class)
    {
        if ($this->existsDocumentClass($class)) {
            $this->redis->hIncrBy("classes", $class, -1);

            if ($this->redis->hGet("classes", $class) <= 0) {
               $this->redis->hDel("classes", $class);
            }
        }
    }

    /**
     * Checks if a class exists in storage
     *
     * @param $class The class name
     * @return bool
     */
    public function existsDocumentClass($class)
    {
        return $this->redis->hExists("classes", $class);
    }

    /**
     * The number of documents in a class
     *
     * @param $class The class name
     * @return int The number of documents
     */
    public function getDocumentCountForClass($class)
    {
        if ($this->existsDocumentClass($class)) {
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
}
