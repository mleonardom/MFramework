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
 * @package    MF_PHPExcel_Writer_Excel2007
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.5, 2010-12-10
 */


/**
 * MF_PHPExcel_Writer_Excel2007_Workbook
 *
 * @category   MF_PHPExcel
 * @package    MF_PHPExcel_Writer_Excel2007
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 */
class MF_PHPExcel_Writer_Excel2007_Workbook extends MF_PHPExcel_Writer_Excel2007_WriterPart
{
	/**
	 * Write workbook to XML format
	 *
	 * @param 	MF_PHPExcel	$pMF_PHPExcel
	 * @return 	string 		XML Output
	 * @throws 	Exception
	 */
	public function writeWorkbook(MF_PHPExcel $pMF_PHPExcel = null)
	{
		// Create XML writer
		$objWriter = null;
		if ($this->getParentWriter()->getUseDiskCaching()) {
			$objWriter = new MF_PHPExcel_Shared_XMLWriter(MF_PHPExcel_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
		} else {
			$objWriter = new MF_PHPExcel_Shared_XMLWriter(MF_PHPExcel_Shared_XMLWriter::STORAGE_MEMORY);
		}

		// XML header
		$objWriter->startDocument('1.0','UTF-8','yes');

		// workbook
		$objWriter->startElement('workbook');
		$objWriter->writeAttribute('xml:space', 'preserve');
		$objWriter->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
		$objWriter->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

			// fileVersion
			$this->_writeFileVersion($objWriter);

			// workbookPr
			$this->_writeWorkbookPr($objWriter);

			// workbookProtection
			$this->_writeWorkbookProtection($objWriter, $pMF_PHPExcel);

			// bookViews
			if ($this->getParentWriter()->getOffice2003Compatibility() === false) {
				$this->_writeBookViews($objWriter, $pMF_PHPExcel);
			}

			// sheets
			$this->_writeSheets($objWriter, $pMF_PHPExcel);

			// definedNames
			$this->_writeDefinedNames($objWriter, $pMF_PHPExcel);

			// calcPr
			$this->_writeCalcPr($objWriter);

		$objWriter->endElement();

		// Return
		return $objWriter->getData();
	}

	/**
	 * Write file version
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter $objWriter 		XML Writer
	 * @throws 	Exception
	 */
	private function _writeFileVersion(MF_PHPExcel_Shared_XMLWriter $objWriter = null)
	{
		$objWriter->startElement('fileVersion');
		$objWriter->writeAttribute('appName', 'xl');
		$objWriter->writeAttribute('lastEdited', '4');
		$objWriter->writeAttribute('lowestEdited', '4');
		$objWriter->writeAttribute('rupBuild', '4505');
		$objWriter->endElement();
	}

	/**
	 * Write WorkbookPr
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter $objWriter 		XML Writer
	 * @throws 	Exception
	 */
	private function _writeWorkbookPr(MF_PHPExcel_Shared_XMLWriter $objWriter = null)
	{
		$objWriter->startElement('workbookPr');

		if (MF_PHPExcel_Shared_Date::getExcelCalendar() == MF_PHPExcel_Shared_Date::CALENDAR_MAC_1904) {
			$objWriter->writeAttribute('date1904', '1');
		}

		$objWriter->writeAttribute('codeName', 'ThisWorkbook');

		$objWriter->endElement();
	}

	/**
	 * Write BookViews
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	MF_PHPExcel					$pMF_PHPExcel
	 * @throws 	Exception
	 */
	private function _writeBookViews(MF_PHPExcel_Shared_XMLWriter $objWriter = null, MF_PHPExcel $pMF_PHPExcel = null)
	{
		// bookViews
		$objWriter->startElement('bookViews');

			// workbookView
			$objWriter->startElement('workbookView');

			$objWriter->writeAttribute('activeTab', $pMF_PHPExcel->getActiveSheetIndex());
			$objWriter->writeAttribute('autoFilterDateGrouping', '1');
			$objWriter->writeAttribute('firstSheet', '0');
			$objWriter->writeAttribute('minimized', '0');
			$objWriter->writeAttribute('showHorizontalScroll', '1');
			$objWriter->writeAttribute('showSheetTabs', '1');
			$objWriter->writeAttribute('showVerticalScroll', '1');
			$objWriter->writeAttribute('tabRatio', '600');
			$objWriter->writeAttribute('visibility', 'visible');

			$objWriter->endElement();

		$objWriter->endElement();
	}

	/**
	 * Write WorkbookProtection
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	MF_PHPExcel					$pMF_PHPExcel
	 * @throws 	Exception
	 */
	private function _writeWorkbookProtection(MF_PHPExcel_Shared_XMLWriter $objWriter = null, MF_PHPExcel $pMF_PHPExcel = null)
	{
		if ($pMF_PHPExcel->getSecurity()->isSecurityEnabled()) {
			$objWriter->startElement('workbookProtection');
			$objWriter->writeAttribute('lockRevision',		($pMF_PHPExcel->getSecurity()->getLockRevision() ? 'true' : 'false'));
			$objWriter->writeAttribute('lockStructure', 	($pMF_PHPExcel->getSecurity()->getLockStructure() ? 'true' : 'false'));
			$objWriter->writeAttribute('lockWindows', 		($pMF_PHPExcel->getSecurity()->getLockWindows() ? 'true' : 'false'));

			if ($pMF_PHPExcel->getSecurity()->getRevisionsPassword() != '') {
				$objWriter->writeAttribute('revisionsPassword',	$pMF_PHPExcel->getSecurity()->getRevisionsPassword());
			}

			if ($pMF_PHPExcel->getSecurity()->getWorkbookPassword() != '') {
				$objWriter->writeAttribute('workbookPassword',	$pMF_PHPExcel->getSecurity()->getWorkbookPassword());
			}

			$objWriter->endElement();
		}
	}

	/**
	 * Write calcPr
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter $objWriter 		XML Writer
	 * @throws 	Exception
	 */
	private function _writeCalcPr(MF_PHPExcel_Shared_XMLWriter $objWriter = null)
	{
		$objWriter->startElement('calcPr');

		$objWriter->writeAttribute('calcId', 			'124519');
		$objWriter->writeAttribute('calcMode', 			'auto');
		$objWriter->writeAttribute('fullCalcOnLoad', 	'1');

		$objWriter->endElement();
	}

	/**
	 * Write sheets
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	MF_PHPExcel					$pMF_PHPExcel
	 * @throws 	Exception
	 */
	private function _writeSheets(MF_PHPExcel_Shared_XMLWriter $objWriter = null, MF_PHPExcel $pMF_PHPExcel = null)
	{
		// Write sheets
		$objWriter->startElement('sheets');
		$sheetCount = $pMF_PHPExcel->getSheetCount();
		for ($i = 0; $i < $sheetCount; ++$i) {
			// sheet
			$this->_writeSheet(
				$objWriter,
				$pMF_PHPExcel->getSheet($i)->getTitle(),
				($i + 1),
				($i + 1 + 3),
				$pMF_PHPExcel->getSheet($i)->getSheetState()
			);
		}

		$objWriter->endElement();
	}

	/**
	 * Write sheet
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	string 						$pSheetname 		Sheet name
	 * @param 	int							$pSheetId	 		Sheet id
	 * @param 	int							$pRelId				Relationship ID
	 * @param   string                      $sheetState         Sheet state (visible, hidden, veryHidden)
	 * @throws 	Exception
	 */
	private function _writeSheet(MF_PHPExcel_Shared_XMLWriter $objWriter = null, $pSheetname = '', $pSheetId = 1, $pRelId = 1, $sheetState = 'visible')
	{
		if ($pSheetname != '') {
			// Write sheet
			$objWriter->startElement('sheet');
			$objWriter->writeAttribute('name', 		$pSheetname);
			$objWriter->writeAttribute('sheetId', 	$pSheetId);
			if ($sheetState != 'visible' && $sheetState != '') {
				$objWriter->writeAttribute('state', $sheetState);
			}
			$objWriter->writeAttribute('r:id', 		'rId' . $pRelId);
			$objWriter->endElement();
		} else {
			throw new Exception("Invalid parameters passed.");
		}
	}

	/**
	 * Write Defined Names
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	MF_PHPExcel					$pMF_PHPExcel
	 * @throws 	Exception
	 */
	private function _writeDefinedNames(MF_PHPExcel_Shared_XMLWriter $objWriter = null, MF_PHPExcel $pMF_PHPExcel = null)
	{
		// Write defined names
		$objWriter->startElement('definedNames');

		// Named ranges
		if (count($pMF_PHPExcel->getNamedRanges()) > 0) {
			// Named ranges
			$this->_writeNamedRanges($objWriter, $pMF_PHPExcel);
		}

		// Other defined names
		$sheetCount = $pMF_PHPExcel->getSheetCount();
		for ($i = 0; $i < $sheetCount; ++$i) {
			// definedName for autoFilter
			$this->_writeDefinedNameForAutofilter($objWriter, $pMF_PHPExcel->getSheet($i), $i);

			// definedName for Print_Titles
			$this->_writeDefinedNameForPrintTitles($objWriter, $pMF_PHPExcel->getSheet($i), $i);

			// definedName for Print_Area
			$this->_writeDefinedNameForPrintArea($objWriter, $pMF_PHPExcel->getSheet($i), $i);
		}

		$objWriter->endElement();
	}

	/**
	 * Write named ranges
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	MF_PHPExcel					$pMF_PHPExcel
	 * @throws 	Exception
	 */
	private function _writeNamedRanges(MF_PHPExcel_Shared_XMLWriter $objWriter = null, MF_PHPExcel $pMF_PHPExcel)
	{
		// Loop named ranges
		$namedRanges = $pMF_PHPExcel->getNamedRanges();
		foreach ($namedRanges as $namedRange) {
			$this->_writeDefinedNameForNamedRange($objWriter, $namedRange);
		}
	}

	/**
	 * Write Defined Name for autoFilter
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	MF_PHPExcel_NamedRange			$pNamedRange
	 * @throws 	Exception
	 */
	private function _writeDefinedNameForNamedRange(MF_PHPExcel_Shared_XMLWriter $objWriter = null, MF_PHPExcel_NamedRange $pNamedRange)
	{
		// definedName for named range
		$objWriter->startElement('definedName');
		$objWriter->writeAttribute('name',			$pNamedRange->getName());
		if ($pNamedRange->getLocalOnly()) {
			$objWriter->writeAttribute('localSheetId',	$pNamedRange->getScope()->getParent()->getIndex($pNamedRange->getScope()));
		}

		// Create absolute coordinate and write as raw text
		$range = MF_PHPExcel_Cell::splitRange($pNamedRange->getRange());
		for ($i = 0; $i < count($range); $i++) {
			$range[$i][0] = '\'' . str_replace("'", "''", $pNamedRange->getWorksheet()->getTitle()) . '\'!' . MF_PHPExcel_Cell::absoluteCoordinate($range[$i][0]);
			if (isset($range[$i][1])) {
				$range[$i][1] = MF_PHPExcel_Cell::absoluteCoordinate($range[$i][1]);
			}
		}
		$range = MF_PHPExcel_Cell::buildRange($range);

		$objWriter->writeRawData($range);

		$objWriter->endElement();
	}

	/**
	 * Write Defined Name for autoFilter
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	MF_PHPExcel_Worksheet			$pSheet
	 * @param 	int							$pSheetId
	 * @throws 	Exception
	 */
	private function _writeDefinedNameForAutofilter(MF_PHPExcel_Shared_XMLWriter $objWriter = null, MF_PHPExcel_Worksheet $pSheet = null, $pSheetId = 0)
	{
		// definedName for autoFilter
		if ($pSheet->getAutoFilter() != '') {
			$objWriter->startElement('definedName');
			$objWriter->writeAttribute('name',			'_xlnm._FilterDatabase');
			$objWriter->writeAttribute('localSheetId',	$pSheetId);
			$objWriter->writeAttribute('hidden',		'1');

			// Create absolute coordinate and write as raw text
			$range = MF_PHPExcel_Cell::splitRange($pSheet->getAutoFilter());
			$range = $range[0];
			$range[0] = MF_PHPExcel_Cell::absoluteCoordinate($range[0]);
			$range[1] = MF_PHPExcel_Cell::absoluteCoordinate($range[1]);
			$range = implode(':', $range);

			$objWriter->writeRawData('\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!' . $range);

			$objWriter->endElement();
		}
	}

	/**
	 * Write Defined Name for PrintTitles
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	MF_PHPExcel_Worksheet			$pSheet
	 * @param 	int							$pSheetId
	 * @throws 	Exception
	 */
	private function _writeDefinedNameForPrintTitles(MF_PHPExcel_Shared_XMLWriter $objWriter = null, MF_PHPExcel_Worksheet $pSheet = null, $pSheetId = 0)
	{
		// definedName for PrintTitles
		if ($pSheet->getPageSetup()->isColumnsToRepeatAtLeftSet() || $pSheet->getPageSetup()->isRowsToRepeatAtTopSet()) {
			$objWriter->startElement('definedName');
			$objWriter->writeAttribute('name',			'_xlnm.Print_Titles');
			$objWriter->writeAttribute('localSheetId',	$pSheetId);

			// Setting string
			$settingString = '';

			// Columns to repeat
			if ($pSheet->getPageSetup()->isColumnsToRepeatAtLeftSet()) {
				$repeat = $pSheet->getPageSetup()->getColumnsToRepeatAtLeft();

				$settingString .= '\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!$' . $repeat[0] . ':$' . $repeat[1];
			}

			// Rows to repeat
			if ($pSheet->getPageSetup()->isRowsToRepeatAtTopSet()) {
				if ($pSheet->getPageSetup()->isColumnsToRepeatAtLeftSet()) {
					$settingString .= ',';
				}

				$repeat = $pSheet->getPageSetup()->getRowsToRepeatAtTop();

				$settingString .= '\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!$' . $repeat[0] . ':$' . $repeat[1];
			}

			$objWriter->writeRawData($settingString);

			$objWriter->endElement();
		}
	}

	/**
	 * Write Defined Name for PrintTitles
	 *
	 * @param 	MF_PHPExcel_Shared_XMLWriter	$objWriter 		XML Writer
	 * @param 	MF_PHPExcel_Worksheet			$pSheet
	 * @param 	int							$pSheetId
	 * @throws 	Exception
	 */
	private function _writeDefinedNameForPrintArea(MF_PHPExcel_Shared_XMLWriter $objWriter = null, MF_PHPExcel_Worksheet $pSheet = null, $pSheetId = 0)
	{
		// definedName for PrintArea
		if ($pSheet->getPageSetup()->isPrintAreaSet()) {
			$objWriter->startElement('definedName');
			$objWriter->writeAttribute('name',			'_xlnm.Print_Area');
			$objWriter->writeAttribute('localSheetId',	$pSheetId);

			// Setting string
			$settingString = '';

			// Print area
			$printArea = MF_PHPExcel_Cell::splitRange($pSheet->getPageSetup()->getPrintArea());

			$chunks = array();
			foreach ($printArea as $printAreaRect) {
				$printAreaRect[0] = MF_PHPExcel_Cell::absoluteCoordinate($printAreaRect[0]);
				$printAreaRect[1] = MF_PHPExcel_Cell::absoluteCoordinate($printAreaRect[1]);
				$chunks[] = '\'' . str_replace("'", "''", $pSheet->getTitle()) . '\'!' . implode(':', $printAreaRect);
			}

			$objWriter->writeRawData(implode(',', $chunks));

			$objWriter->endElement();
		}
	}
}
