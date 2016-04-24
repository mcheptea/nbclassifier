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
     * Persists a class to storage
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
     * Removes a class from storage
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
     * Checks if a class exists in storage
     *
     * @param $class The class name
     * @return bool
     */
    public function existsClass($class)
    {
        return $this->redis->hExists("classes", $class);
    }

}
