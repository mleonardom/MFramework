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
 * @package    MF_PHPExcel_Shared_Escher
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.5, 2010-12-10
 */

/**
 * MF_PHPExcel_Shared_Escher_DgContainer_SpgrContainer
 *
 * @category   MF_PHPExcel
 * @package    MF_PHPExcel_Shared_Escher
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 */
class MF_PHPExcel_Shared_Escher_DgContainer_SpgrContainer
{
	/**
	 * Parent Shape Group Container
	 *
	 * @var MF_PHPExcel_Shared_Escher_DgContainer_SpgrContainer
	 */
	private $_parent;

	/**
	 * Shape Container collection
	 *
	 * @var array
	 */
	private $_children = array();

	/**
	 * Set parent Shape Group Container
	 *
	 * @param MF_PHPExcel_Shared_Escher_DgContainer_SpgrContainer $parent
	 */
	public function setParent($parent)
	{
		$this->_parent = $parent;
	}

	/**
	 * Get the parent Shape Group Container if any
	 *
	 * @return MF_PHPExcel_Shared_Escher_DgContainer_SpgrContainer|null
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Add a child. This will be either spgrContainer or spContainer
	 *
	 * @param mixed $child
	 */
	public function addChild($child)
	{
		$this->_children[] = $child;
		$child->setParent($this);
	}

	/**
	 * Get collection of Shape Containers
	 */
	public function getChildren()
	{
		return $this->_children;
	}

	/**
	 * Recursively get all spContainers within this spgrContainer
	 *
	 * @return MF_PHPExcel_Shared_Escher_DgContainer_SpgrContainer_SpContainer[]
	 */
	public function getAllSpContainers()
	{
		$allSpContainers = array();

		foreach ($this->_children as $child) {
			if ($child instanceof MF_PHPExcel_Shared_Escher_DgContainer_SpgrContainer) {
				$allSpContainers = array_merge($allSpContainers, $child->getAllSpContainers());
			} else {
				$allSpContainers[] = $child;
			}
		}

		return $allSpContainers;
	}
}
