<?php
namespace Classifier\Config;

use Symfony\Component\Yaml\Yaml;
use Exception;

/**
 * Provides access to the classifier's settings.
 * The settings are provided a in a YAML (.yml) file.
 *
 * User: Mark Cheptea
 * Date: 24-Apr-16
 * Time: 00:13
 */
class Config
{
    public static $configFile = "../config.yml";

    /**
     * Returns the value of a key. The path is separated by dots (.)
     *
     * @param string $path Path Syntax: [arraykey1].[arraykey2]...[arraykeyN]
     * @return mixed
     * @throws Exception
     */
    public static function get($path = "")
    {
        $path = explode(".", $path);
        $configFileName = __DIR__ . "/" . self::$configFile;

        if (!file_exists($configFileName)) {
            throw new Exception("No configuration file named ". $configFileName . " exists!");
        }

        $configString = file_get_contents($configFileName);
        $configArray = Yaml::parse($configString);

        return self::getArrayValueByPath($path, $configArray);
    }
    /**
     * Writes a value to the ini file. The path is separated by dots (.)
     *
     * @param string $path, Path Syntax: [arraykey1].[arraykey2]...[arraykeyN]
     * @param mixed $value
     * @return mixed
     * @throws Exception
     */
    public static function set($path = "", $value = "")
    {
        $keys = explode(".", $path);
        $configFileName = __DIR__ . "/" . self::$configFile;

        if (!file_exists($configFileName)) {
            throw new Exception("No configuration file named ". $configFileName . " exists!");
        }

        $configString = file_get_contents($configFileName);
        $configArray = Yaml::parse($configString);

        //set value
        self::setArrayValueByPath($configArray, $keys, $value);

        $yamlString = Yaml::dump($configArray);
        file_put_contents($configFileName, $yamlString);
    }
    /**
     * Returns a value from an array given a path to it.
     *
     * @param array $path, a set of keys identifying the value
     * @param array $array, the storing array
     * @return mixed
     * @throws Exception
     */
    private static function getArrayValueByPath($path, $array)
    {
        if (!isset($array[$path[0]])) {
            throw new Exception("The configuration file doesn't contain the key '" . $path[0] . "'");
        }

        if (count($path) > 1) {
            return self::getArrayValueByPath(array_slice($path, 1), $array[$path[0]]);
        } else {
            return $array[$path[0]];
        }
    }

    /**
     *
     *
     * @param array $array, destination
     * @param array $pathParts, path
     * @param mixed $value, value
     * @return array
     */
    private static function setArrayValueByPath(&$array, $pathParts, &$value)
    {
        $current = &$array;

        foreach ($pathParts as $key) {
            $current = &$current[$key];
        }
        $backup = $current;
        $current = $value;

        return $backup;
    }
}
