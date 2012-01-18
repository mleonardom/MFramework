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
 * MF_PHPExcel_Reader_OOCalc
 *
 * @category   MF_PHPExcel
 * @package    MF_PHPExcel_Reader
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 */
class MF_PHPExcel_Reader_OOCalc implements MF_PHPExcel_Reader_IReader
{
	/**
	 * Read data only?
	 *
	 * @var boolean
	 */
	private $_readDataOnly = false;

	/**
	 * Restict which sheets should be loaded?
	 *
	 * @var array
	 */
	private $_loadSheetsOnly = null;

	/**
	 * Sheet index to read
	 *
	 * @var int
	 */
	private $_sheetIndex 	= 0;

	/**
	 * Formats
	 *
	 * @var array
	 */
	private $_styles = array();

	/**
	 * MF_PHPExcel_Reader_IReadFilter instance
	 *
	 * @var MF_PHPExcel_Reader_IReadFilter
	 */
	private $_readFilter = null;


	/**
	 * Read data only?
	 *
	 * @return boolean
	 */
	public function getReadDataOnly() {
		return $this->_readDataOnly;
	}

	/**
	 * Set read data only
	 *
	 * @param boolean $pValue
	 * @return MF_PHPExcel_Reader_OOCalc
	 */
	public function setReadDataOnly($pValue = false) {
		$this->_readDataOnly = $pValue;
		return $this;
	}

	/**
	 * Get which sheets to load
	 *
	 * @return mixed
	 */
	public function getLoadSheetsOnly()
	{
		return $this->_loadSheetsOnly;
	}

	/**
	 * Set which sheets to load
	 *
	 * @param mixed $value
	 * @return MF_PHPExcel_Reader_OOCalc
	 */
	public function setLoadSheetsOnly($value = null)
	{
		$this->_loadSheetsOnly = is_array($value) ?
			$value : array($value);
		return $this;
	}

	/**
	 * Set all sheets to load
	 *
	 * @return MF_PHPExcel_Reader_OOCalc
	 */
	public function setLoadAllSheets()
	{
		$this->_loadSheetsOnly = null;
		return $this;
	}

	/**
	 * Read filter
	 *
	 * @return MF_PHPExcel_Reader_IReadFilter
	 */
	public function getReadFilter() {
		return $this->_readFilter;
	}

	/**
	 * Set read filter
	 *
	 * @param MF_PHPExcel_Reader_IReadFilter $pValue
	 * @return MF_PHPExcel_Reader_OOCalc
	 */
	public function setReadFilter(MF_PHPExcel_Reader_IReadFilter $pValue) {
		$this->_readFilter = $pValue;
		return $this;
	}

	/**
	 * Create a new MF_PHPExcel_Reader_OOCalc
	 */
	public function __construct() {
		$this->_readFilter 	= new MF_PHPExcel_Reader_DefaultReadFilter();
	}

	/**
	 * Can the current MF_PHPExcel_Reader_IReader read the file?
	 *
	 * @param 	string 		$pFileName
	 * @return 	boolean
	 */
	public function canRead($pFilename)
	{
		// Check if zip class exists
		if (!class_exists('ZipArchive')) {
			return false;
		}

		// Check if file exists
		if (!file_exists($pFilename)) {
			throw new Exception("Could not open " . $pFilename . " for reading! File does not exist.");
		}

		// Load file
		$zip = new ZipArchive;
		if ($zip->open($pFilename) === true) {
			// check if it is an OOXML archive
			$mimeType = $zip->getFromName("mimetype");

			$zip->close();

			return ($mimeType === 'application/vnd.oasis.opendocument.spreadsheet');
		}

		return false;
	}

	/**
	 * Loads MF_PHPExcel from file
	 *
	 * @param 	string 		$pFilename
	 * @return 	MF_PHPExcel
	 * @throws 	Exception
	 */
	public function load($pFilename)
	{
		// Create new MF_PHPExcel
		$objMF_PHPExcel = new MF_PHPExcel();

		// Load into this instance
		return $this->loadIntoExisting($pFilename, $objMF_PHPExcel);
	}

	private static function identifyFixedStyleValue($styleList,&$styleAttributeValue) {
		$styleAttributeValue = strtolower($styleAttributeValue);
		foreach($styleList as $style) {
			if ($styleAttributeValue == strtolower($style)) {
				$styleAttributeValue = $style;
				return true;
			}
		}
		return false;
	}

	/**
	 * Loads MF_PHPExcel from file into MF_PHPExcel instance
	 *
	 * @param 	string 		$pFilename
	 * @param	MF_PHPExcel	$objMF_PHPExcel
	 * @return 	MF_PHPExcel
	 * @throws 	Exception
	 */
	public function loadIntoExisting($pFilename, MF_PHPExcel $objMF_PHPExcel)
	{
		// Check if file exists
		if (!file_exists($pFilename)) {
			throw new Exception("Could not open " . $pFilename . " for reading! File does not exist.");
		}

		$timezoneObj = new DateTimeZone('Europe/London');
		$GMT = new DateTimeZone('UTC');

		$zip = new ZipArchive;
		if ($zip->open($pFilename) === true) {
//			echo '<h1>Meta Information</h1>';
			$xml = simplexml_load_string($zip->getFromName("meta.xml"));
			$namespacesMeta = $xml->getNamespaces(true);
//			echo '<pre>';
//			print_r($namespacesMeta);
//			echo '</pre><hr />';

			$docProps = $objMF_PHPExcel->getProperties();
			$officeProperty = $xml->children($namespacesMeta['office']);
			foreach($officeProperty as $officePropertyData) {
				$officePropertyDC = array();
				if (isset($namespacesMeta['dc'])) {
					$officePropertyDC = $officePropertyData->children($namespacesMeta['dc']);
				}
				foreach($officePropertyDC as $propertyName => $propertyValue) {
					switch ($propertyName) {
						case 'title' :
								$docProps->setTitle($propertyValue);
								break;
						case 'subject' :
								$docProps->setSubject($propertyValue);
								break;
						case 'creator' :
								$docProps->setCreator($propertyValue);
								$docProps->setLastModifiedBy($propertyValue);
								break;
						case 'date' :
								$creationDate = strtotime($propertyValue);
								$docProps->setCreated($creationDate);
								$docProps->setModified($creationDate);
								break;
						case 'description' :
								$docProps->setDescription($propertyValue);
								break;
					}
				}
				$officePropertyMeta = array();
				if (isset($namespacesMeta['dc'])) {
					$officePropertyMeta = $officePropertyData->children($namespacesMeta['meta']);
				}
				foreach($officePropertyMeta as $propertyName => $propertyValue) {
					$propertyValueAttributes = $propertyValue->attributes($namespacesMeta['meta']);
					switch ($propertyName) {
						case 'initial-creator' :
								$docProps->setCreator($propertyValue);
								break;
						case 'keyword' :
								$docProps->setKeywords($propertyValue);
								break;
						case 'creation-date' :
								$creationDate = strtotime($propertyValue);
								$docProps->setCreated($creationDate);
								break;
						case 'user-defined' :
								$propertyValueType = MF_PHPExcel_DocumentProperties::PROPERTY_TYPE_STRING;
								foreach ($propertyValueAttributes as $key => $value) {
									if ($key == 'name') {
										$propertyValueName = (string) $value;
									} elseif($key == 'value-type') {
										switch ($value) {
											case 'date'	:
												$propertyValue = MF_PHPExcel_DocumentProperties::convertProperty($propertyValue,'date');
												$propertyValueType = MF_PHPExcel_DocumentProperties::PROPERTY_TYPE_DATE;
												break;
											case 'boolean'	:
												$propertyValue = MF_PHPExcel_DocumentProperties::convertProperty($propertyValue,'bool');
												$propertyValueType = MF_PHPExcel_DocumentProperties::PROPERTY_TYPE_BOOLEAN;
												break;
											case 'float'	:
												$propertyValue = MF_PHPExcel_DocumentProperties::convertProperty($propertyValue,'r4');
												$propertyValueType = MF_PHPExcel_DocumentProperties::PROPERTY_TYPE_FLOAT;
												break;
											default :
												$propertyValueType = MF_PHPExcel_DocumentProperties::PROPERTY_TYPE_STRING;
										}
									}
								}
								$docProps->setCustomProperty($propertyValueName,$propertyValue,$propertyValueType);
								break;
					}
				}
			}


//			echo '<h1>Workbook Content</h1>';
			$xml = simplexml_load_string($zip->getFromName("content.xml"));
			$namespacesContent = $xml->getNamespaces(true);
//			echo '<pre>';
//			print_r($namespacesContent);
//			echo '</pre><hr />';

			$workbook = $xml->children($namespacesContent['office']);
			foreach($workbook->body->spreadsheet as $workbookData) {
				$workbookData = $workbookData->children($namespacesContent['table']);
				$worksheetID = 0;
				foreach($workbookData->table as $worksheetDataSet) {
					$worksheetData = $worksheetDataSet->children($namespacesContent['table']);
//					print_r($worksheetData);
//					echo '<br />';
					$worksheetDataAttributes = $worksheetDataSet->attributes($namespacesContent['table']);
//					print_r($worksheetDataAttributes);
//					echo '<br />';
					if ((isset($this->_loadSheetsOnly)) && (isset($worksheetDataAttributes['name'])) &&
						(!in_array($worksheetDataAttributes['name'], $this->_loadSheetsOnly))) {
						continue;
					}

//					echo '<h2>Worksheet '.$worksheetDataAttributes['name'].'</h2>';
					// Create new Worksheet
					$objMF_PHPExcel->createSheet();
					$objMF_PHPExcel->setActiveSheetIndex($worksheetID);
					if (isset($worksheetDataAttributes['name'])) {
						$worksheetName = (string) $worksheetDataAttributes['name'];
						$objMF_PHPExcel->getActiveSheet()->setTitle($worksheetName);
					}

					$rowID = 1;
					foreach($worksheetData as $key => $rowData) {
//						echo '<b>'.$key.'</b><br />';
						switch ($key) {
							case 'table-header-rows':
								foreach ($rowData as $key=>$cellData) {
									$rowData = $cellData;
									break;
								}
							case 'table-row' :
								$columnID = 'A';
								foreach($rowData as $key => $cellData) {
									if (!is_null($this->getReadFilter())) {
										if (!$this->getReadFilter()->readCell($columnID, $rowID, $worksheetName)) {
											continue;
										}
									}

//									echo '<b>'.$columnID.$rowID.'</b><br />';
									$cellDataText = $cellData->children($namespacesContent['text']);
									$cellDataOfficeAttributes = $cellData->attributes($namespacesContent['office']);
									$cellDataTableAttributes = $cellData->attributes($namespacesContent['table']);

//									echo 'Office Attributes: ';
//									print_r($cellDataOfficeAttributes);
//									echo '<br />Table Attributes: ';
//									print_r($cellDataTableAttributes);
//									echo '<br />Cell Data Text';
//									print_r($cellDataText);
//									echo '<br />';
//
									$type = $formatting = $hyperlink = null;
									$hasCalculatedValue = false;
									$cellDataFormula = '';
									if (isset($cellDataTableAttributes['formula'])) {
										$cellDataFormula = $cellDataTableAttributes['formula'];
										$hasCalculatedValue = true;
									}

									if (isset($cellDataText->p)) {
//										echo 'Value Type is '.$cellDataOfficeAttributes['value-type'].'<br />';
										switch ($cellDataOfficeAttributes['value-type']) {
											case 'string' :
													$type = MF_PHPExcel_Cell_DataType::TYPE_STRING;
													$dataValue = $cellDataText->p;
													if (isset($dataValue->a)) {
														$dataValue = $dataValue->a;
														$cellXLinkAttributes = $dataValue->attributes($namespacesContent['xlink']);
														$hyperlink = $cellXLinkAttributes['href'];
													}
													break;
											case 'boolean' :
													$type = MF_PHPExcel_Cell_DataType::TYPE_BOOL;
													$dataValue = ($cellDataText->p == 'TRUE') ? True : False;
													break;
											case 'float' :
													$type = MF_PHPExcel_Cell_DataType::TYPE_NUMERIC;
													$dataValue = (float) $cellDataOfficeAttributes['value'];
													if (floor($dataValue) == $dataValue) {
														$dataValue = (integer) $dataValue;
													}
													break;
											case 'date' :
													$type = MF_PHPExcel_Cell_DataType::TYPE_NUMERIC;
												    $dateObj = new DateTime($cellDataOfficeAttributes['date-value'], $GMT);
													$dateObj->setTimeZone($timezoneObj);
													list($year,$month,$day,$hour,$minute,$second) = explode(' ',$dateObj->format('Y m d H i s'));
													$dataValue = MF_PHPExcel_Shared_Date::FormattedPHPToExcel($year,$month,$day,$hour,$minute,$second);
													if ($dataValue != floor($dataValue)) {
														$formatting = MF_PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX15.' '.MF_PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4;
													} else {
														$formatting = MF_PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX15;
													}
													break;
											case 'time' :
													$type = MF_PHPExcel_Cell_DataType::TYPE_NUMERIC;
													$dataValue = MF_PHPExcel_Shared_Date::PHPToExcel(strtotime('01-01-1970 '.implode(':',sscanf($cellDataOfficeAttributes['time-value'],'PT%dH%dM%dS'))));
													$formatting = MF_PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4;
													break;
										}
//										echo 'Data value is '.$dataValue.'<br />';
//										if (!is_null($hyperlink)) {
//											echo 'Hyperlink is '.$hyperlink.'<br />';
//										}
									}

									if ($hasCalculatedValue) {
										$type = MF_PHPExcel_Cell_DataType::TYPE_FORMULA;
//										echo 'Formula: '.$cellDataFormula.'<br />';
										$cellDataFormula = substr($cellDataFormula,strpos($cellDataFormula,':=')+1);
										$temp = explode('"',$cellDataFormula);
										foreach($temp as $key => &$value) {
											//	Only replace in alternate array entries (i.e. non-quoted blocks)
											if (($key % 2) == 0) {
												$value = preg_replace('/\[\.(.*):\.(.*)\]/Ui','$1:$2',$value);
												$value = preg_replace('/\[\.(.*)\]/Ui','$1',$value);
												$value = MF_PHPExcel_Calculation::_translateSeparator(';',',',$value,$inBraces);
											}
										}
										unset($value);
										//	Then rebuild the formula string
										$cellDataFormula = implode('"',$temp);
//										echo 'Adjusted Formula: '.$cellDataFormula.'<br />';
									}

									if (!is_null($type)) {
										$objMF_PHPExcel->getActiveSheet()->getCell($columnID.$rowID)->setValueExplicit((($hasCalculatedValue) ? $cellDataFormula : $dataValue),$type);
										if ($hasCalculatedValue) {
//											echo 'Forumla result is '.$dataValue.'<br />';
											$objMF_PHPExcel->getActiveSheet()->getCell($columnID.$rowID)->setCalculatedValue($dataValue);
										}
										if (($cellDataOfficeAttributes['value-type'] == 'date') ||
											($cellDataOfficeAttributes['value-type'] == 'time')) {
											$objMF_PHPExcel->getActiveSheet()->getStyle($columnID.$rowID)->getNumberFormat()->setFormatCode($formatting);
										}
										if (!is_null($hyperlink)) {
											$objMF_PHPExcel->getActiveSheet()->getCell($columnID.$rowID)->getHyperlink()->setUrl($hyperlink);
										}
									}

									//	Merged cells
									if ((isset($cellDataTableAttributes['number-columns-spanned'])) || (isset($cellDataTableAttributes['number-rows-spanned']))) {
										$columnTo = $columnID;
										if (isset($cellDataTableAttributes['number-columns-spanned'])) {
											$columnTo = MF_PHPExcel_Cell::stringFromColumnIndex(MF_PHPExcel_Cell::columnIndexFromString($columnID) + $cellDataTableAttributes['number-columns-spanned'] -2);
										}
										$rowTo = $rowID;
										if (isset($cellDataTableAttributes['number-rows-spanned'])) {
											$rowTo = $rowTo + $cellDataTableAttributes['number-rows-spanned'] - 1;
										}
										$cellRange = $columnID.$rowID.':'.$columnTo.$rowTo;
										$objMF_PHPExcel->getActiveSheet()->mergeCells($cellRange);
									}

									if (isset($cellDataTableAttributes['number-columns-repeated'])) {
//										echo 'Repeated '.$cellDataTableAttributes['number-columns-repeated'].' times<br />';
										$columnID = MF_PHPExcel_Cell::stringFromColumnIndex(MF_PHPExcel_Cell::columnIndexFromString($columnID) + $cellDataTableAttributes['number-columns-repeated'] - 2);
									}
									++$columnID;
								}
								++$rowID;
								break;
						}
					}
					++$worksheetID;
				}
			}

		}

		// Return
		return $objMF_PHPExcel;
	}

	/**
	 * Get sheet index
	 *
	 * @return int
	 */
	public function getSheetIndex() {
		return $this->_sheetIndex;
	}

	/**
	 * Set sheet index
	 *
	 * @param	int		$pValue		Sheet index
	 * @return MF_PHPExcel_Reader_OOCalc
	 */
	public function setSheetIndex($pValue = 0) {
		$this->_sheetIndex = $pValue;
		return $this;
	}
}
