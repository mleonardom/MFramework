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
 * @package    MF_PHPExcel_Reader
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.5, 2010-12-10
 */


/** MF_PHPExcel root directory */
//if (!defined('MF_PHPExcel_ROOT')) {
	/**
	 * @ignore
	 */
	//define('MF_PHPExcel_ROOT', dirname(__FILE__) . '/../../');
	//require(MF_PHPExcel_ROOT . 'MF_PHPExcel/Autoloader.php');
//}

/**
 * MF_PHPExcel_Reader_Serialized
 *
 * @category   MF_PHPExcel
 * @package    MF_PHPExcel_Reader
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 */
class MF_PHPExcel_Reader_Serialized implements MF_PHPExcel_Reader_IReader
{
	/**
	 * Can the current MF_PHPExcel_Reader_IReader read the file?
	 *
	 * @param 	string 		$pFileName
	 * @return 	boolean
	 */
	public function canRead($pFilename)
	{
		// Check if file exists
		if (!file_exists($pFilename)) {
			throw new Exception("Could not open " . $pFilename . " for reading! File does not exist.");
		}

		return $this->fileSupportsUnserializeMF_PHPExcel($pFilename);
	}

	/**
	 * Loads MF_PHPExcel Serialized file
	 *
	 * @param 	string 		$pFilename
	 * @return 	MF_PHPExcel
	 * @throws 	Exception
	 */
	public function load($pFilename)
	{
		// Check if file exists
		if (!file_exists($pFilename)) {
			throw new Exception("Could not open " . $pFilename . " for reading! File does not exist.");
		}

		// Unserialize... First make sure the file supports it!
		if (!$this->fileSupportsUnserializeMF_PHPExcel($pFilename)) {
			throw new Exception("Invalid file format for MF_PHPExcel_Reader_Serialized: " . $pFilename . ".");
		}

		return $this->_loadSerialized($pFilename);
	}

	/**
	 * Load MF_PHPExcel Serialized file
	 *
	 * @param 	string 		$pFilename
	 * @return 	MF_PHPExcel
	 */
	private function _loadSerialized($pFilename) {
		$xmlData = simplexml_load_string(file_get_contents("zip://$pFilename#MF_PHPExcel.xml"));
		$excel = unserialize(base64_decode((string)$xmlData->data));

		// Update media links
		for ($i = 0; $i < $excel->getSheetCount(); ++$i) {
			for ($j = 0; $j < $excel->getSheet($i)->getDrawingCollection()->count(); ++$j) {
				if ($excel->getSheet($i)->getDrawingCollection()->offsetGet($j) instanceof PHPExcl_Worksheet_BaseDrawing) {
					$imgTemp =& $excel->getSheet($i)->getDrawingCollection()->offsetGet($j);
					$imgTemp->setPath('zip://' . $pFilename . '#media/' . $imgTemp->getFilename(), false);
				}
			}
		}

		return $excel;
	}

    /**
     * Does a file support UnserializeMF_PHPExcel ?
     *
	 * @param 	string 		$pFilename
	 * @throws 	Exception
	 * @return 	boolean
     */
    public function fileSupportsUnserializeMF_PHPExcel($pFilename = '') {
		// Check if file exists
		if (!file_exists($pFilename)) {
			throw new Exception("Could not open " . $pFilename . " for reading! File does not exist.");
		}

		// File exists, does it contain MF_PHPExcel.xml?
		return MF_PHPExcel_Shared_File::file_exists("zip://$pFilename#MF_PHPExcel.xml");
    }
}
