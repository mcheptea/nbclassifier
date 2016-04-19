<?php
namespace Classifier\Storage;

abstract class AbstractStorage {
	public abstract function trainTo($words, $class);
	public abstract function deTrainFromSet($words, $class);
	public abstract function getAllClasses();
	public abstract function getWordCount($word);
	public abstract function getTotalWordCount();
	public abstract function getSetWordCount($set);
	public abstract function getWordCountFromSet($word, $set);
}