<?php
	class MF_Paginator
	{
		private $items;
		private $per_page;
		private $current_page;
		
		public function __construct( $items, $per_page, $current_page = 0 ){
			$this->items = $items;
			$this->per_page = $per_page;
			$this->current_page = $current_page;
		}
		
		public function setCurrentPage( $current_page ){
			$this->current_page = $current_page;
		}
		
		public function getPageItems(){
			if( $this->getFirstIndex() >= count($this->items) ) return array();
			return array_slice($this->items,$this->getFirstIndex(),$this->per_page);
		}
		
		public function getLastPage(){
			$lp = floor(count($this->items)/$this->per_page);
			if( count($this->items)%$this->per_page == 0 ) return $lp-1;
			return $lp;
		}
		
		public function getNextPage( $circular = true ){
			if( $circular && !$this->haveNext() ) return 0;
			else return $this->current_page+1;
		}
		
		public function getPreviousPage( $circular = true ){
			if( $circular && !$this->havePrevious() ) return $this->getLastPage();
			else return $this->current_page-1;
		}
		
		public function havePrevious(){
			return $this->current_page > 0;
		}
		
		public function haveNext(){
			return ( $this->getFirstIndex()+$this->per_page < count($this->items) );
		}
		
		protected function getFirstIndex(){
			return $this->current_page*$this->per_page;
		}
		
	}