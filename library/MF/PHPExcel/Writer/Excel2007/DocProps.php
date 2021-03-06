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
 * MF_PHPExcel_Writer_Excel2007_DocProps
 *
 * @category   MF_PHPExcel
 * @package    MF_PHPExcel_Writer_Excel2007
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 */
class MF_PHPExcel_Writer_Excel2007_DocProps extends MF_PHPExcel_Writer_Excel2007_WriterPart
{
/**
	 * Write docProps/app.xml to XML format
	 *
	 * @param 	MF_PHPExcel	$pMF_PHPExcel
	 * @return 	string 		XML Output
	 * @throws 	Exception
	 */
	public function writeDocPropsApp(MF_PHPExcel $pMF_PHPExcel = null)
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

		// Properties
		$objWriter->startElement('Properties');
			$objWriter->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/officeDocument/2006/extended-properties');
			$objWriter->writeAttribute('xmlns:vt', 'http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes');

			// Application
			$objWriter->writeElement('Application', 	'Microsoft Excel');

			// DocSecurity
			$objWriter->writeElement('DocSecurity', 	'0');

			// ScaleCrop
			$objWriter->writeElement('ScaleCrop', 		'false');

			// HeadingPairs
			$objWriter->startElement('HeadingPairs');

				// Vector
				$objWriter->startElement('vt:vector');
					$objWriter->writeAttribute('size', 		'2');
					$objWriter->writeAttribute('baseType', 	'variant');

					// Variant
					$objWriter->startElement('vt:variant');
						$objWriter->writeElement('vt:lpstr', 	'Worksheets');
					$objWriter->endElement();

					// Variant
					$objWriter->startElement('vt:variant');
						$objWriter->writeElement('vt:i4', 		$pMF_PHPExcel->getSheetCount());
					$objWriter->endElement();

				$objWriter->endElement();

			$objWriter->endElement();

			// TitlesOfParts
			$objWriter->startElement('TitlesOfParts');

				// Vector
				$objWriter->startElement('vt:vector');
					$objWriter->writeAttribute('size', 		$pMF_PHPExcel->getSheetCount());
					$objWriter->writeAttribute('baseType',	'lpstr');

					$sheetCount = $pMF_PHPExcel->getSheetCount();
					for ($i = 0; $i < $sheetCount; ++$i) {
						$objWriter->writeElement('vt:lpstr', $pMF_PHPExcel->getSheet($i)->getTitle());
					}

				$objWriter->endElement();

			$objWriter->endElement();

			// Company
			$objWriter->writeElement('Company', 			$pMF_PHPExcel->getProperties()->getCompany());

			// Company
			$objWriter->writeElement('Manager', 			$pMF_PHPExcel->getProperties()->getManager());

			// LinksUpToDate
			$objWriter->writeElement('LinksUpToDate', 		'false');

			// SharedDoc
			$objWriter->writeElement('SharedDoc', 			'false');

			// HyperlinksChanged
			$objWriter->writeElement('HyperlinksChanged', 	'false');

			// AppVersion
			$objWriter->writeElement('AppVersion', 			'12.0000');

		$objWriter->endElement();

		// Return
		return $objWriter->getData();
	}

	/**
	 * Write docProps/core.xml to XML format
	 *
	 * @param 	MF_PHPExcel	$pMF_PHPExcel
	 * @return 	string 		XML Output
	 * @throws 	Exception
	 */
	public function writeDocPropsCore(MF_PHPExcel $pMF_PHPExcel = null)
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

		// cp:coreProperties
		$objWriter->startElement('cp:coreProperties');
			$objWriter->writeAttribute('xmlns:cp', 'http://schemas.openxmlformats.org/package/2006/metadata/core-properties');
			$objWriter->writeAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
			$objWriter->writeAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');
			$objWriter->writeAttribute('xmlns:dcmitype', 'http://purl.org/dc/dcmitype/');
			$objWriter->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

			// dc:creator
			$objWriter->writeElement('dc:creator',			$pMF_PHPExcel->getProperties()->getCreator());

			// cp:lastModifiedBy
			$objWriter->writeElement('cp:lastModifiedBy', 	$pMF_PHPExcel->getProperties()->getLastModifiedBy());

			// dcterms:created
			$objWriter->startElement('dcterms:created');
				$objWriter->writeAttribute('xsi:type', 'dcterms:W3CDTF');
				$objWriter->writeRawData(date(DATE_W3C, 	$pMF_PHPExcel->getProperties()->getCreated()));
			$objWriter->endElement();

			// dcterms:modified
			$objWriter->startElement('dcterms:modified');
				$objWriter->writeAttribute('xsi:type', 'dcterms:W3CDTF');
				$objWriter->writeRawData(date(DATE_W3C, 	$pMF_PHPExcel->getProperties()->getModified()));
			$objWriter->endElement();

			// dc:title
			$objWriter->writeElement('dc:title', 			$pMF_PHPExcel->getProperties()->getTitle());

			// dc:description
			$objWriter->writeElement('dc:description', 		$pMF_PHPExcel->getProperties()->getDescription());

			// dc:subject
			$objWriter->writeElement('dc:subject', 			$pMF_PHPExcel->getProperties()->getSubject());

			// cp:keywords
			$objWriter->writeElement('cp:keywords', 		$pMF_PHPExcel->getProperties()->getKeywords());

			// cp:category
			$objWriter->writeElement('cp:category', 		$pMF_PHPExcel->getProperties()->getCategory());

		$objWriter->endElement();

		// Return
		return $objWriter->getData();
	}

	/**
	 * Write docProps/custom.xml to XML format
	 *
	 * @param 	MF_PHPExcel	$pMF_PHPExcel
	 * @return 	string 		XML Output
	 * @throws 	Exception
	 */
	public function writeDocPropsCustom(MF_PHPExcel $pMF_PHPExcel = null)
	{
		$customPropertyList = $pMF_PHPExcel->getProperties()->getCustomProperties();
		if (count($customPropertyList) == 0) {
			return;
		}

		// Create XML writer
		$objWriter = null;
		if ($this->getParentWriter()->getUseDiskCaching()) {
			$objWriter = new MF_PHPExcel_Shared_XMLWriter(MF_PHPExcel_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
		} else {
			$objWriter = new MF_PHPExcel_Shared_XMLWriter(MF_PHPExcel_Shared_XMLWriter::STORAGE_MEMORY);
		}

		// XML header
		$objWriter->startDocument('1.0','UTF-8','yes');

		// cp:coreProperties
		$objWriter->startElement('Properties');
			$objWriter->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/officeDocument/2006/custom-properties');
			$objWriter->writeAttribute('xmlns:vt', 'http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes');


			foreach($customPropertyList as $key => $customProperty) {
				$propertyValue = $pMF_PHPExcel->getProperties()->getCustomPropertyValue($customProperty);
				$propertyType = $pMF_PHPExcel->getProperties()->getCustomPropertyType($customProperty);

				$objWriter->startElement('property');
					$objWriter->writeAttribute('fmtid', 	'{D5CDD505-2E9C-101B-9397-08002B2CF9AE}');
					$objWriter->writeAttribute('pid', 		$key+2);
					$objWriter->writeAttribute('name', 		$customProperty);

					switch($propertyType) {
						case 'i' :
							$objWriter->writeElement('vt:i4', 		$propertyValue);
							break;
						case 'f' :
							$objWriter->writeElement('vt:r8', 		$propertyValue);
							break;
						case 'b' :
							$objWriter->writeElement('vt:bool', 	($propertyValue) ? 'true' : 'false');
							break;
						case 'd' :
							$objWriter->startElement('vt:filetime');
								$objWriter->writeRawData(date(DATE_W3C, $propertyValue));
							$objWriter->endElement();
							break;
						default :
							$objWriter->writeElement('vt:lpwstr', 	$propertyValue);
							break;
					}

				$objWriter->endElement();
			}


		$objWriter->endElement();

		// Return
		return $objWriter->getData();
	}

}
