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
 * @package    MF_PHPExcel_Settings
 * @copyright  Copyright (c) 2006 - 2010 MF_PHPExcel (http://www.codeplex.com/MF_PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.5, 2010-12-10
 */

/** MF_PHPExcel root directory */
//if (!defined('MF_PHPExcel_ROOT')) {
	/**
	 * @ignore
	 */
	//define('MF_PHPExcel_ROOT', dirname(__FILE__) . '/../');
	//require(MF_PHPExcel_ROOT . 'MF_PHPExcel/Autoloader.php');
//}


class MF_PHPExcel_Settings
{
	public static function getCacheStorageMethod() {
		return MF_PHPExcel_CachedObjectStorageFactory::$_cacheStorageMethod;
	}	//	function getCacheStorageMethod()


	public static function getCacheStorageClass() {
		return MF_PHPExcel_CachedObjectStorageFactory::$_cacheStorageClass;
	}	//	function getCacheStorageClass()


	public static function setCacheStorageMethod($method = MF_PHPExcel_CachedObjectStorageFactory::cache_in_memory, $arguments = array()) {
		return MF_PHPExcel_CachedObjectStorageFactory::initialize($method,$arguments);
	}	//	function setCacheStorageMethod()


	public static function setLocale($locale){
		return MF_PHPExcel_Calculation::getInstance()->setLocale($locale);
	}	//	function setLocale()

}