<?php
namespace Classifier;

use Classifier\Storage\RedisStorage;

/**
 * Abstract classifier
 *
 * User: Mark Cheptea
 * Date: 25-Apr-16
 * Time: 00:06
 */
class AbstractClassifier
{
    protected $store;

    /**
     * Classifier constructor.
     */
    public function __construct()
    {
        $this->store = new RedisStorage();
    }

    /**
     * Normalizez a list of words by removing any special characters.
     *
     * @param array $words
     * @return array
     */
    protected function normalize($words = array())
    {
        if (!empty($words)) {
            $result = array();
            foreach ($words as $word) {
                $word = strtolower($word);
                $word = preg_replace("/[^a-z]/i", "", $word);

                if (!empty($word) && strlen($word) > 2) {
                    $word = strtolower($word);
                    if (!empty($word)) {
                        $result[] = $word;
                    }
                }
            }
            return $result;
        }
    }
}
