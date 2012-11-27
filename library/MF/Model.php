<?php
    class MF_Model
    {
        public $id;
        public $tableName;
        public $idColumnName;

        protected $columns = array();
        protected $className;
        /**
         * Data for referenced models.
         * I.E: the Model Gallery has many images
         * Structure for Image Model:
         * $reference_models = array(
         *	 "gallery" => array( // Any text for reference becuse you can get 2 o more relations to a same table
         * 		"column" => "gallery_id", // INDEX column in the Image Model
         * 		"refModel" => "Gallery" // Model class name to reference
         * 		"refColumn" => "id" // Column in the other Model to reference
         * 	) // you can insert more here
         * );
         * @var array
         */
        protected $reference_models = array();

        protected function __construct($table_name){
        	defined('DB_TABLE_PREFIX') || define('DB_TABLE_PREFIX', '');
            $this->className    = get_class($this);
            $this->tableName    = DB_TABLE_PREFIX.$table_name;

            // A note on hardcoding $this->idColumnName = 'id'...
            // In three years working with this framework, I've used
            // a different id name exactly once - so I've decided to
            // drop the option from the constructor. You can overload
            // the constructor yourself if you have the need.
            if(!isset($this->idColumnName)) $this->idColumnName = 'id';
        }

        public function __get($key)
        {
            if(array_key_exists($key, $this->columns))
                return $this->columns[$key];

            if((substr($key, 0, 2) == '__') && array_key_exists(substr($key, 2), $this->columns))
                return htmlspecialchars($this->columns[substr($key, 2)]);

            $trace = debug_backtrace();
            trigger_error("Undefined property via Model::__get(): $key in {$trace[0]['file']} on line {$trace[0]['line']}", E_USER_NOTICE);
            return null;
        }

        public function __set($key, $value){
            $this->columns[$key] = $value;

            return $value; // Seriously.
        }
		
        public function toArray(){
        	return array_merge( array($this->idColumnName=>$this->id), $this->columns );
        }
        
        public function select($id, $column = null)
        {
            $db = MF_Database::getDatabase();

            $column = is_null($column)? $this->idColumnName : $column = $db->escape($column);

            $db->query("SELECT * FROM `{$this->tableName}` WHERE `$column` = :id LIMIT 1", array('id' => $id));
            if($db->hasRows()){
                $row = $db->getRow();
                $this->load($row);
                return true;
            }

            return false;
        }
		
		public function selectFromSQL($sql)
        {
            $db = MF_Database::getDatabase();

            $db->query($sql);
            if($db->hasRows()){
                $row = $db->getRow();
                $this->load($row);
                return true;
            }

            return false;
        }

        public function ok(){
            return !is_null($this->id);
        }

        public function save(){
            if(is_null($this->id))
                $this->insert();
            else
                $this->update();
            return $this->id;
        }

        public function insert($cmd = 'INSERT INTO'){
            $db = MF_Database::getDatabase();

            if(count($this->columns) == 0) return false;

            $data = array();
            foreach($this->columns as $k => $v){
                $data[$k] = $db->quote($v);
            }

            $columns = '`' . implode('`, `', array_keys($data)) . '`';
            $values = implode(',', $data);

            $db->query("$cmd `{$this->tableName}` ($columns) VALUES ($values)");
            $this->id = $db->insertId();
            return $this->id;
        }

        public function replace(){
            return $this->delete() && $this->insert();
        }

        public function update() {
            if(is_null($this->id)) return false;

            $db = MF_Database::getDatabase();

            if(count($this->columns) == 0) return;

            $sql = "UPDATE {$this->tableName} SET ";
            foreach($this->columns as $k => $v)
                $sql .= "`$k`=" . $db->quote($v) . ',';
            $sql[strlen($sql) - 1] = ' ';

            $sql .= "WHERE `{$this->idColumnName}` = " . $db->quote($this->id);
            $db->query($sql);

            return $db->affectedRows();
        }
		
        /**
         * Return the dependents objects for the $class_name model especified
         * @param string $class_name
         * @param string $reference_key
         */
        public function getDependentRows( $class_name, $reference_key = false ){
        	if(!class_exists($class_name)){
                return false;
        	}
        	$tmp_obj = new $class_name;
        	foreach($tmp_obj->reference_models as $k => $ref){
        		if( $ref['refModel'] == get_class($this) && (!$reference_key || $k == $reference_key ) ){
        			$ref_model = $ref;
        			break;
        		}
        	}
            $objects = MF_Model::glob($class_name,"SELECT * FROM `{$tmp_obj->tableName}` WHERE `{$ref_model['column']}`=".$this->{$ref_model['refColumn']});
            return $objects;
        }
		
		/**
         * Return the dependents objects for the $class_name model especified trouth the $through_class
         * @param string $class_name
         * @param string $through_class
         */
        public function getManyToManyRows( $class_name, $through_class ){
        	if(!class_exists($class_name) || !class_exists($through_class)){
                return false;
        	}
        	$tmp_obj = new $class_name;
			$tmp_through = new $through_class;
        	foreach($tmp_through->reference_models as $k => $ref){
        		if( $ref['refModel'] == get_class($this) ){
        			$ref_o_model = $ref;
        		}elseif( $ref['refModel'] == $class_name ){
        			$ref_d_model = $ref;
        		}
        	}
			$sql = "SELECT  `{$tmp_obj->tableName}`.* 
				FROM  `{$tmp_through->tableName}` 
				INNER JOIN  `{$tmp_obj->tableName}` ON `{$tmp_through->tableName}`.`{$ref_d_model['column']}` =  `{$tmp_obj->tableName}`.`{$ref_d_model['refColumn']}` 
				WHERE  `{$tmp_through->tableName}`.`{$ref_o_model['column']}`=".$this->{$ref_o_model['refColumn']};
            $objects = MF_Model::glob($class_name,$sql);
            return $objects;
        }
        
        public function getParent( $class_name, $reference_key = false ){
        	if(!class_exists($class_name)){
                return false;
        	}
        	$tmp_obj = new $class_name;
        	foreach($this->reference_models as $k => $ref){
        		if( $ref['refModel'] == $class_name && (!$reference_key || $k == $reference_key ) ){
        			$ref_model = $ref;
        			break;
        		}
        	}
        	if( $tmp_obj->select( $this->{$ref_model['column']}, $ref_model['refColumn'] ) ){
        		return $tmp_obj;
        	}
        	return false;
        }
        
        public function delete() {
            if(is_null($this->id)) return false;
            $db = MF_Database::getDatabase();
            $db->query("DELETE FROM `{$this->tableName}` WHERE `{$this->idColumnName}` = :id LIMIT 1", array('id' => $this->id));
            return $db->affectedRows();
        }

        public function load($row) {
            foreach($row as $k => $v) {
                if($k == $this->idColumnName){
                    $this->id = $v;
                }else{
                	$this->__set($k, $v);
                }
            }
        }

        // Grabs a large block of instantiated $class_name objects from the database using only one query.
        // Note: Once PHP 5.3 becomes widespread, we can use get_called_class() to rewrite glob() and avoid
        // having to call it via Model rather than the actual class we're targeting.
        public static function glob($class_name, $sql = null, $extra_columns = array()){
            $db = MF_Database::getDatabase();

            // Make sure the class exists before we instantiate it...
            if(!class_exists($class_name))
                return false;

            $tmp_obj = new $class_name;

            // Also, it needs to be a subclass of Model...
            if(!is_subclass_of($tmp_obj, 'MF_Model'))
                return false;

            if(is_null($sql))
                $sql = "SELECT * FROM `{$tmp_obj->tableName}`";

            $objs = array();
            $rows = $db->getRows($sql);
            foreach($rows as $row) {
                $o = new $class_name;
                $o->load($row);
                $objs[] = $o;

                foreach($extra_columns as $c){
                    $o->addColumn($c);
                    $o->$c = isset($row[$c]) ? $row[$c] : null;
                }
            }
            return $objs;
        }
    }