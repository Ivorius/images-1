<?php

namespace WebChemistry\Images\Image;

use Nette, WebChemistry;

class Image extends Container {

	/** @var Image */
	protected $noImage;

	/** @var Info */
	protected $original;

	/**
	 * @param WebChemistry\Images\Connectors\IConnector $connector
	 * @param string                                    $absoluteName
	 * @param string|null                               $noImage
	 */
	public function __construct(WebChemistry\Images\Connectors\IConnector $connector, $absoluteName, $noImage = NULL) {
		parent::__construct($connector);

		$this->setAbsoluteName($absoluteName);
		$this->original = $this->getOriginal();

		if ($noImage) {
			$this->noImage = new self($connector, $noImage);
		}
	}

	/**
	 * @param string $noImage
	 * @return $this
	 */
	public function setNoImage($noImage) {
		$this->noImage = new self($this->connector, $noImage);

		return $this;
	}

	/**
	 * @param bool $original
	 * @param bool $createInfo
	 * @return string|Info
	 */
	private function creator($original = FALSE, $createInfo = FALSE) {
		$info = $this->getInfo();

		// Original and resized image does not exist.
		if (!$info->isImageExists() && !$this->original->isImageExists()) {
			if ($this->noImage) {
				return $this->noImage->getLink();
			} else {
				return '#noimage';
			}
		}

		// Original image
		if ($this->original->isImageExists() && $original) {
			return $createInfo ? $this->original : $this->connector->getLink($this->original);
		}

		// Resize image does not exist
		if (!$info->isImageExists() && $this->isResize()) {
			$image = $this->connector->getNetteImage($this->original);

			$this->processHelpers($image);

			if ($this->getWidth() || $this->getHeight()) {
				$image->resize($this->getWidth(), $this->getHeight(), $this->getFlag());
			}

			$this->wakeUpCallbacks($image);

			$this->connector->save($image, $info, $this->original->getImageType());

			return $createInfo ? $info : str_replace('%', '%25', $this->connector->getLink($info));
		}

		// Resize image exists.
		if ($info->isImageExists()) {
			return $createInfo ? $info : str_replace('%', '%25', $this->connector->getLink($info));
		}
		//return $createInfo ? $info : $this->connector->getLink($info); // Disallow re-loading page as image
	}

	/**
	 * @return bool
	 */
	public function isExists() {
		return $this->getInfo($this)
					->isImageExists();
	}

	/**
	 * @param bool $original
	 * @param bool $returnInfo
	 * @return string|Info
	 */
	public function getLink($original = FALSE, $returnInfo = FALSE) {
		return $this->creator($original, $returnInfo);
	}

	/************************* Deprecated **************************/

	/**
	 * @deprecated
	 */
	public function exists() {
		trigger_error('exists is deprecated, please use isExists');

		return $this->getInfo($this)
					->isImageExists();
	}

	/**
	 * @deprecated
	 */
	public function createInfoLink($createResized = TRUE) {
		trigger_error('createInfoLink is deprecated, please use getInfo');

		return $this->creator(!$createResized, TRUE);
	}

	/**
	 * @deprecated
	 */
	public function createLink($createResized = TRUE) {
		trigger_error('createLink is deprecated, please use getLink');

		return $this->creator(!$createResized);
	}
}
