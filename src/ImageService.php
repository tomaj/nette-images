<?php

namespace Tomaj\Image;

use Nette\Image;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Nette\Utils\Strings;

class ImageService
{
	/** @var \Tomaj\Image\BackendInterface  */
	private $backend;

	/** @var string */
	private $storePath;

	/** @var array */
	private $thumbTypes;

	/** @var int  */
	private $imageQuality = 100;

	public function __construct(\Tomaj\Image\Backend\BackendInterface $backend, $storePath, $thumbTypes = array(), $imageQuality = 100)
	{
		$this->backend = $backend;
		$this->storePath = $storePath;
		$this->thumbTypes = $thumbTypes;
		$this->imageQuality = $imageQuality;
	}

	/**
	 * Store basic file.
	 * For use in nette see *storeNetteFile*
	 *
	 * @param $sourceFile
	 * @param $orginalName
	 * @param bool $deleteOrginalFile
	 * @return mixed
	 * @throws \Nette\IOException
	 */
	public function store($sourceFile, $orginalName, $deleteOrginalFile = FALSE)
	{
		$path = $this->processStorePath($this->storePath, $orginalName);

		$thumbs = $this->createThumbs($sourceFile);

		$identifier = $this->backend->save($sourceFile, $orginalName, $path, $thumbs);

		if ($deleteOrginalFile) {
			FileSystem::delete($sourceFile);
		}

		return $identifier;
	}

	/**
	 * Store file from nette form
	 *
	 * @param \Nette\Http\FileUpload $file
	 * @return mixed
	 * @throws \Nette\IOException
	 */
	public function storeNetteFile(\Nette\Http\FileUpload $file)
	{
		return $this->store($file->getTemporaryFile(), $file->getName(), TRUE);
	}

	public function regenerateThumb($identifier, $type)
	{
		$sourceFilePath = $this->backend->localFile($identifier);
		$thumbPath = $this->tmpFile();
		$this->createThumb($sourceFilePath, $thumbPath, $type);
		$this->backend->saveThumb($thumbPath, $identifier, $type);
	}

	/**
	 * @param $sourceFile
	 * @return array
	 * @throws \Nette\IOException
	 */
	private function createThumbs($sourceFile)
	{
		$resultThumbs = array();
		foreach ($this->thumbTypes as $type) {
			$filePath = $this->tmpFile();
			$this->createThumb($sourceFile, $filePath, $type);
			$resultThumbs[$type] = $filePath;
		}
		return $resultThumbs;
	}

	private function createThumb($originalFile, $targetPath, $type)
	{
		$resizeType = FALSE;
		if (!Strings::contains($type, '_')) {
			$sizes = $type;
		} else {
			list($sizes, $resizeType) = explode('_', $type);
		}
		list($width, $height) = explode('x', $sizes);

		$image = Image::fromFile($originalFile);
		$image->resize($width, $height, $this->getResizeType($resizeType));
		$image->save($targetPath, $this->imageQuality, Image::JPEG);
		return $targetPath;
	}

	protected function getResizeType($resizeType)
	{
		if ($resizeType) {
			if ($resizeType == 'FIT')		 return Image::FIT;
			if ($resizeType == 'FILL')		return Image::FILL;
			if ($resizeType == 'EXACT')	   return Image::EXACT;
			if ($resizeType == 'SHRINK_ONLY') return Image::SHRING_ONLY;
			if ($resizeType == 'STRETCH')	 return Image::STRETCH;
		}
		return Image::EXACT;
	}

	public function url($identifier, $thumb = NULL)
	{
		return $this->backend->url($identifier, $thumb);
	}

	private function processStorePath($storePath, $tmpFileName)
	{
		$replace = array(
			':year' => date('Y'),
			':month' => date('m'),
			':day' => date('d'),
			':hash' => md5($tmpFileName . time() . Random::generate(32)),
			':filename' => Strings::toAscii($tmpFileName), // todo remove all bad characters - maybe nette function (need dependencies)
		);
		return str_replace(array_keys($replace), array_values($replace), $storePath);
	}

	private function tmpFile($length = 32)
	{
		return tempnam(sys_get_temp_dir(), Random::generate($length));
	}
}