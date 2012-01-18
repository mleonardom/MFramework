<?php
class ExcelController extends MF_Controller
{
	public function readAction(){
		if (!file_exists(APPLICATION_PATH."/../test_docs/featuredemo.xlsx")) {
			MF_Error::dieError("Test file featuredemo.xlsx not exists.<br />", 500);
		}
		$xhtml = "reader4 logs:<br />";
		$xhtml .= date('H:i:s') . " Load from Excel2007 file<hr />";
		$objPHPExcel = MF_PHPExcel_IOFactory::load(APPLICATION_PATH."/../test_docs/featuredemo.xlsx");
		
		$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
		
		foreach($rowIterator as $row){
			$cellIterator = $row->getCellIterator();
			$xhtml .= "Row: {$row->getRowIndex()}<br />";
			foreach ($cellIterator as $cell) {
				$xhtml .= "Col: ".$cell->getColumn()." = ".$cell->getCalculatedValue().'<br />';
			}
			$xhtml .= "<hr />";
		}
		
		$this->view->xhtml = $xhtml;
	}
	
	public function generateSqlAction(){
		$request = MF_Request::getInstance();
		$file_path = $request->getParam('file',APPLICATION_PATH."/../test_docs/c_demo.xlsx");
		$table_name = $request->getParam('table','my_table');
		if( !file_exists($file_path) ){
			$this->view->xhtml = "<p>File $file_path not exists !</p>";
		}else{
			$query = "INSERT INTO `$table_name`";
			
			$objPHPExcel = MF_PHPExcel_IOFactory::load($file_path);
			$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
			foreach( $rowIterator as $row ){
				$cellIterator = $row->getCellIterator();
				foreach( $cellIterator as $cell ) {
					if( $row->getRowIndex() == 1 ){
						$cols[$cell->getColumn()] = "`{$cell->getCalculatedValue()}`";
					}else{
						$values[$row->getRowIndex()][$cell->getColumn()] = $cell->getCalculatedValue();
					}
				}
			}
			$query .= "(".implode(',', $cols).") VALUES";
			foreach( $values as $value ){
				$query .= "(";
				$qu = false;
				foreach( $cols as $k => $v ){
					if( !$qu ) $qu = true;
					else $query .= ", ";
					$query .= isset($value[$k])? "'{$value[$k]}'":'NULL';
				}
				$query .= "),";
			}
			$query[ strlen($query)-1 ] = ";";
			$this->view->xhtml = $query;
		}
	}
}