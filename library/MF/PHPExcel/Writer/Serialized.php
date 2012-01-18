<?php
/**
 * MF_PHPExcel
 *
 * Copyright (c) 2006 - 2010 MF_PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   MF_PHPExcel
 * @package    MF_PHPExcel_Writer
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.5, 2010-12-10
 */


/**
 * MF_PHPExcel_Writer_Serialized
 *
 * @category   MF_PHPExcel
 * @package    MF_PHPExcel_Writer
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 */
class MF_PHPExcel_Writer_Serialized implements MF_PHPExcel_Writer_IWriter
{
	/**
	 * Private MF_PHPExcel
	 *
	 * @var MF_PHPExcel
	 */
	private $_spreadSheet;

    /**
     * Create a new MF_PHPExcel_Writer_Serialized
     *
	 * @param 	MF_PHPExcel	$pMF_PHPExcel
     */
    public function __construct(MF_PHPExcel $pMF_PHPExcel = null)
    {
    	// Assign MF_PHPExcel
		$this->setMF_PHPExcel($pMF_PHPExcel);
    }

	/**
	 * Save MF_PHPExcel to file
	 *
	 * @param 	string 		$pFileName
	 * @throws 	Exception
	 */
	public function save($pFilename = null)
	{
		if (!is_null($this->_spreadSheet)) {
			// Garbage collect
			$this->_spreadSheet->garbageCollect();

			// Garbage collect...
			foreach ($this->_spreadSheet->getAllSheets() as $sheet) {
        		$sheet->garbageCollect();
			}

			// Create new ZIP file and open it for writing
			$objZip = new ZipArchive();

			// Try opening the ZIP file
			if ($objZip->open($pFilename, ZIPARCHIVE::OVERWRITE) !== true) {
				if ($objZip->open($pFilename, ZIPARCHIVE::CREATE) !== true) {
					throw new Exception("Could not open " . $pFilename . " for writing.");
				}
			}

			// Add media
			$sheetCount = $this->_spreadSheet->getSheetCount();
			for ($i = 0; $i < $sheetCount; ++$i) {
				for ($j = 0; $j < $this->_spreadSheet->getSheet($i)->getDrawingCollection()->count(); ++$j) {
					if ($this->_spreadSheet->getSheet($i)->getDrawingCollection()->offsetGet($j) instanceof MF_PHPExcel_Worksheet_BaseDrawing) {
						$imgTemp = $this->_spreadSheet->getSheet($i)->getDrawingCollection()->offsetGet($j);
						$objZip->addFromString('media/' . $imgTemp->getFilename(), file_get_contents($imgTemp->getPath()));
					}
				}
			}

			// Add MF_PHPExcel.xml to the document, which represents a PHP serialized MF_PHPExcel object
			$objZip->addFromString('MF_PHPExcel.xml', $this->_writeSerialized($this->_spreadSheet, $pFilename));

			// Close file
			if ($objZip->close() === false) {
				throw new Exception("Could not close zip file $pFilename.");
			}
		} else {
			throw new Exception("MF_PHPExcel object unassigned.");
		}
	}

	/**
	 * Get MF_PHPExcel object
	 *
	 * @return MF_PHPExcel
	 * @throws Exception
	 */
	public function getMF_PHPExcel() {
		if (!is_null($this->_spreadSheet)) {
			return $this->_spreadSheet;
		} else {
			throw new Exception("No MF_PHPExcel assigned.");
		}
	}

	/**
	 * Get MF_PHPExcel object
	 *
	 * @param 	MF_PHPExcel 	$pMF_PHPExcel	MF_PHPExcel object
	 * @throws	Exception
	 * @return MF_PHPExcel_Writer_Serialized
	 */
	public function setMF_PHPExcel(MF_PHPExcel $pMF_PHPExcel = null) {
		$this->_spreadSheet = $pMF_PHPExcel;
		return $this;
	}

	/**
	 * Serialize MF_PHPExcel object to XML
	 *
	 * @param 	MF_PHPExcel	$pMF_PHPExcel
	 * @param 	string		$pFilename
	 * @return 	string 		XML Output
	 * @throws 	Exception
	 */
	private function _writeSerialized(MF_PHPExcel $pMF_PHPExcel = null, $pFilename = '')
	{
		// Clone $pMF_PHPExcel
		$pMF_PHPExcel = clone $pMF_PHPExcel;

		// Update media links
		$sheetCount = $pMF_PHPExcel->getSheetCount();
		for ($i = 0; $i < $sheetCount; ++$i) {
			for ($j = 0; $j < $pMF_PHPExcel->getSheet($i)->getDrawingCollection()->count(); ++$j) {
				if ($pMF_PHPExcel->getSheet($i)->getDrawingCollection()->offsetGet($j) instanceof MF_PHPExcel_Worksheet_BaseDrawing) {
					$imgTemp =& $pMF_PHPExcel->getSheet($i)->getDrawingCollection()->offsetGet($j);
					$imgTemp->setPath('zip://' . $pFilename . '#media/' . $imgTemp->getFilename(), false);
				}
			}
		}

		// Create XML writer
		$objWriter = new xmlWriter();
		$objWriter->openMemory();
		$objWriter->setIndent(true);

		// XML header
		$objWriter->startDocument('1.0','UTF-8','yes');

		// MF_PHPExcel
		$objWriter->startElement('MF_PHPExcel');
		$objWriter->writeAttribute('version', '1.7.5');

			// Comment
			$objWriter->writeComment('This file has been generated using MF_PHPExcel v1.7.5 (http://www.codeplex.com/MF_PHPExcel). It contains a base64 encoded serialized version of the MF_PHPExcel internal object.');

			// Data
			$objWriter->startElement('data');
				$objWriter->writeCData( base64_encode(serialize($pMF_PHPExcel)) );
			$objWriter->endElement();

		$objWriter->endElement();

		// Return
		return $objWriter->outputMemory(true);
	}
}
