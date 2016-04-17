<?php
namespace Classifier\Storage;

abstract class AbstractStorage {
	public abstract function trainTo($words, $set);
	public abstract function deTrainFromSet($words, $set);
	public abstract function getAllSets();
	public abstract function getWordCount($word);
	public abstract function getAllWordsCount();
	public abstract function getSetWordCount($set);
	public abstract function getWordCountFromSet($word, $set);
	
	public abstract function isBlacklisted($word);
}