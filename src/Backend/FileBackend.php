<?php

namespace Tomaj\Image\Backend;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

/**
 * Class FileBackend
 *
 * Zakladny backend pre obrazky ktory uklada obrazky lokalne do zadaneho foldera $storageRoot
 *
 * @package Tomaj\Image\Backend
 */
class FileBackend implements BackendInterface
{
	/** @var  string */
	private $root;

	/** @var  string */
	private $url;

	public function __construct($storageRoot, $url = '/')
	{
		$this->root = $storageRoot;
		$this->url = $url;
	}

	public function save($sourceFile, $originalName, $path, $thumbs = array())
	{
        if (!file_exists($this->root)) {
            throw new \Nette\IOException("FileBackend root doesn't exists '{$this->root}'");
        }

        if (!file_exists($sourceFile)) {
            throw new \Nette\IOException("Source file '$sourceFile' doesn't exists ");
        }

		FileSystem::createDir($this->root . $path);
		$originalName = Strings::toAscii($originalName);
        $targetFile = $path . DIRECTORY_SEPARATOR . $originalName;
        $targetFullPath = $this->root  . $targetFile;

		FileSystem::copy($sourceFile, $targetFullPath);

        foreach ($thumbs as $thumb => $thumbPath) {
            if (!file_exists($thumbPath)) {
                throw new \Nette\IOException("Thumb doesn't exists '$thumbPath'");
            }
            $targetThumbPath = $this->root . $path . DIRECTORY_SEPARATOR . $thumb . '_' . $originalName;
			FileSystem::copy($thumbPath, $targetThumbPath);
        }

        return $path . DIRECTORY_SEPARATOR . $originalName;
	}

	public function saveThumb($thumbPath, $identifier, $type)
	{
		$targetThumbPath = $this->root . $this->getThumbPath($identifier, $type);
		FileSystem::rename($thumbPath, $targetThumbPath, TRUE);
	}

    public function url($identifier, $thumb = NULL)
    {
        if ($thumb) {
            return $this->url . '/' . $this->getThumbPath($identifier, $thumb);
        } else {
            return $this->url . '/' . $identifier;
        }
    }

	public function localFile($idenfier)
	{
		return $this->root . $idenfier;
	}

	private function getThumbPath($identifier, $type)
	{
		$parts = explode('/', $identifier);
		$last = array_pop($parts);
		$last = $type . '_' . $last;
		array_push($parts, $last);
		return implode(DIRECTORY_SEPARATOR, $parts);
	}
}