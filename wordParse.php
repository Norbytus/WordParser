<?php

	class DocxToHtml
	{
		public $filePath      = "";
		public $html          = "";
		public $imgDataArray  = [];
		public $copyFilePath  = "";
		public $fileExtension = ['jpeg', 'png', 'jpg'];

		public function __construct($file, $copyPath)
		{
			$data = [];
			exec('/usr/local/bin/pandoc -s ' . $file . ' -t html5', $data);
			$this->filePath     = $file;
			$this->html         = implode($data);
			$this->copyFilePath = $copyPath;
		}

		public function getHtml()
		{
			$doc = new DOMDocument();
			$doc->loadHTML($this->html);

			$body = $doc->getElementsByTagName('body');
			$img  = $doc->getElementsByTagName('img');

			if($img->length != 0) {

				$this->changeFileDestanation();
				foreach($img as $image) {

					$srcImage = $image
						->attributes
						->getNamedItem('src')
						->value;
					if(array_key_exists($srcImage, $this->imgDataArray)) {

						$image
							->attributes
							->getNamedItem('src')
							->value = $this->imgDataArray[$srcImage]['new'];

					}

				}

			}

			$meta  = $doc->getElementsByTagName('meta');
			$clear = [];

			foreach($meta as $item)
			  $clear[] = $item;

			foreach($clear as $item)
				$item->parentNode->removeChild($item);

			return $doc->saveHTML();
		}

		private function changeFileDestanation()
		{
			$zip = new ZipArchive();
			$zip->open($this->filePath);

			for($i = 0; $i < $zip->numFiles; $i++) {
				$fileZipName = $zip->getNameIndex($i);
				$fileZipInfo = pathinfo($fileZipName);
				if(in_array($fileZipInfo['extension'], $this->fileExtension)) {
					$path = explode('/', $fileZipInfo['dirname']);
					$this->imgDataArray[$path[1] . '/' . $fileZipInfo['basename']] = ['currentDir' => getcwd(),'fileName' => $fileZipInfo['basename']];
					$fileNewName = uniqid('DocZip', true) . "." . $fileZipInfo['extension'];
					$this->imgDataArray[$path[1] . '/' . $fileZipInfo['basename']] = ['new' => $this->copyFilePath . '/' . $fileNewName];
					copy("zip://" . $this->filePath . "#" . $fileZipName, $_SERVER['DOCUMENT_ROOT'] . $this->copyFilePath . '/'. $fileNewName);
				}
			}

			$zip->close();
		}
	}
