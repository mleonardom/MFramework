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
 * @package    MF_PHPExcel_RichText
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.5, 2010-12-10
 */


/**
 * MF_PHPExcel_RichText
 *
 * @category   MF_PHPExcel
 * @package    MF_PHPExcel_RichText
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 */
class MF_PHPExcel_RichText implements MF_PHPExcel_IComparable
{
	/**
	 * Rich text elements
	 *
	 * @var MF_PHPExcel_RichText_ITextElement[]
	 */
	private $_richTextElements;

    /**
     * Create a new MF_PHPExcel_RichText instance
     *
     * @param 	MF_PHPExcel_Cell	$pParent
     * @throws	Exception
     */
    public function __construct(MF_PHPExcel_Cell $pCell = null)
    {
    	// Initialise variables
    	$this->_richTextElements = array();

    	// Rich-Text string attached to cell?
    	if (!is_null($pCell)) {
	    	// Add cell text and style
	    	if ($pCell->getValue() != "") {
	    		$objRun = new MF_PHPExcel_RichText_Run($pCell->getValue());
	    		$objRun->setFont(clone $pCell->getParent()->getStyle($pCell->getCoordinate())->getFont());
	    		$this->addText($objRun);
	    	}

	    	// Set parent value
	    	$pCell->setValueExplicit($this, MF_PHPExcel_Cell_DataType::TYPE_STRING);
    	}
    }

    /**
     * Add text
     *
     * @param 	MF_PHPExcel_RichText_ITextElement		$pText		Rich text element
     * @throws 	Exception
     * @return MF_PHPExcel_RichText
     */
    public function addText(MF_PHPExcel_RichText_ITextElement $pText = null)
    {
    	$this->_richTextElements[] = $pText;
    	return $this;
    }

    /**
     * Create text
     *
     * @param 	string	$pText	Text
     * @return	MF_PHPExcel_RichText_TextElement
     * @throws 	Exception
     */
    public function createText($pText = '')
    {
    	$objText = new MF_PHPExcel_RichText_TextElement($pText);
    	$this->addText($objText);
    	return $objText;
    }

    /**
     * Create text run
     *
     * @param 	string	$pText	Text
     * @return	MF_PHPExcel_RichText_Run
     * @throws 	Exception
     */
    public function createTextRun($pText = '')
    {
    	$objText = new MF_PHPExcel_RichText_Run($pText);
    	$this->addText($objText);
    	return $objText;
    }

    /**
     * Get plain text
     *
     * @return string
     */
    public function getPlainText()
    {
    	// Return value
    	$returnValue = '';

    	// Loop through all MF_PHPExcel_RichText_ITextElement
    	foreach ($this->_richTextElements as $text) {
    		$returnValue .= $text->getText();
    	}

    	// Return
    	return $returnValue;
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString() {
    	return $this->getPlainText();
    }

    /**
     * Get Rich Text elements
     *
     * @return MF_PHPExcel_RichText_ITextElement[]
     */
    public function getRichTextElements()
    {
    	return $this->_richTextElements;
    }

    /**
     * Set Rich Text elements
     *
     * @param 	MF_PHPExcel_RichText_ITextElement[]	$pElements		Array of elements
     * @throws 	Exception
     * @return MF_PHPExcel_RichText
     */
    public function setRichTextElements($pElements = null)
    {
    	if (is_array($pElements)) {
    		$this->_richTextElements = $pElements;
    	} else {
    		throw new Exception("Invalid MF_PHPExcel_RichText_ITextElement[] array passed.");
    	}
    	return $this;
    }

	/**
	 * Get hash code
	 *
	 * @return string	Hash code
	 */
	public function getHashCode() {
		$hashElements = '';
		foreach ($this->_richTextElements as $element) {
			$hashElements .= $element->getHashCode();
		}

    	return md5(
    		  $hashElements
    		. __CLASS__
    	);
    }

	/**
	 * Implement PHP __clone to create a deep clone, not just a shallow copy.
	 */
	public function __clone() {
		$vars = get_object_vars($this);
		foreach ($vars as $key => $value) {
			if (is_object($value)) {
				$this->$key = clone $value;
			} else {
				$this->$key = $value;
			}
		}
	}
}
