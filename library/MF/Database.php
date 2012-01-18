<?PHP
    class MF_Database
    {
        /**
         * 
         * Singleton object
         * @var MF_Database
         */
        private static $me;
		
        /**
         * 
         * The db conection
         * @var unknown_type
         */
        public $db;
        
        /**
         * 
         * MySQL hostname
         * @var mixed
         */
        public $host;
        
        /**
         * 
         * MySQL DB name
         * @var string
         */
        public $name;
        
        /**
         * 
         * username to login on MySQL
         * @var unknown_type
         */
        public $username;
        
        /**
         * 
         * password to login on MySQL
         * @var unknown_type
         */
        public $password;
        
        /**
         * 
         * If this is true the application will be die on a MySQL error
         * @var boolean
         */
        public $dieOnError;
        
        /**
         * 
         * Store here the las queries
         * @var array
         */
        public $queries;
        
        /**
         * 
         * The last resul for a query
         * @var unknown_type
         */
        public $result;

        /**
         * 
         * Singleton constructor
         * @param unknown_type $connect
         * @throws Exception
         */
        private function __construct($connect = false)
        {
            $this->host       = DB_HOST;
            $this->name       = DB_NAME;
            $this->username   = DB_USER;
            $this->password   = DB_PASS;
            $this->dieOnError = defined("DB_ON_DIE_ERROR")? DB_ON_DIE_ERROR: true;

            $this->db = false;
            $this->queries = array();

            if($connect === true)
                $this->connect();
        }

        /**
         * 
         * Waiting (not so) patiently for 5.3.0...
         * @param unknown_type $name
         * @param unknown_type $args
         * @return unknown_type
         */
        public static function __callStatic($name, $args)
        {
            return self::$me->__call($name, $args);
        }

        /**
         * 
         * Get Singleton object
         * @param unknown_type $connect
         * @return MF_Database
         */
        public static function getDatabase($connect = true)
        {
            if(is_null(self::$me))
                self::$me = new MF_Database($connect);
            return self::$me;
        }

        /**
         * 
         * Do we have a valid database connection?
         * @return boolean
         */
        public function isConnected()
        {
            return is_resource($this->db) && get_resource_type($this->db) == 'mysql link';
        }

        /**
         * 
         * Do we have a valid database connection and have we selected a database?
         * @return boolean
         */
        public function databaseSelected()
        {
            if(!$this->isConnected()) return false;
            $result = mysql_list_tables($this->name, $this->db);
            return is_resource($result);
        }
		
        /**
         * 
         * Just connect to the MySQL DB return true if the connection has been successful
         * @return boolean
         */
        public function connect() {
            if($this->isConnected()) return true;
            $this->db = mysql_connect($this->host, $this->username, $this->password) or $this->notify();
            if($this->db === false) return false;
            mysql_select_db($this->name, $this->db) or $this->notify();
            return $this->isConnected();
        }
        
        /**
         * 
         * Make a query t the MySQL DB
         * This returns the result for the mysql_query call
         * @param string $sql
         * @param unknown_type $args_to_prepare
         * @param unknown_type $exception_on_missing_args
         * @return unknown_type
         */
        public function query($sql, $args_to_prepare = null, $exception_on_missing_args = true) {
            if(!$this->isConnected()) $this->connect();

            // Allow for prepared arguments. Example:
            // query("SELECT * FROM table WHERE id = :id", array('id' => $some_val));
            if(is_array($args_to_prepare))
            {
                foreach($args_to_prepare as $name => $val)
                {
                    $val = $this->quote($val);
                    $sql = str_replace(":$name", $val, $sql, $count);
                    if($exception_on_missing_args && (0 == $count))
                        throw new Exception(":$name was not found in prepared SQL query.");
                }
            }

            $this->queries[] = $sql;
            $this->result = mysql_query($sql, $this->db) or $this->notify();
            return $this->result;
        }
		
        /**
         * 
         * Returns the number of rows.
         * You can pass in nothing, a string, or a db result
         * @param unknown_type $arg
         */
        public function numRows($arg = null)
        {
            $result = $this->resulter($arg);
            return ($result !== false) ? mysql_num_rows($result) : false;
        }

        /**
         * 
         * Returns true / false if the result has one or more rows
         * @param unknown_type $arg
         * @return boolean
         */
        public function hasRows($arg = null)
        {
            $result = $this->resulter($arg);
            return is_resource($result) && (mysql_num_rows($result) > 0);
        }

        /**
         * 
         * Returns the number of rows affected by the last operation
         * @return boolean
         */
        public function affectedRows() {
            if(!$this->isConnected()) return false;
            return mysql_affected_rows($this->db);
        }

        /**
         * 
         * Returns the auto increment ID generated by the previous insert statement
         * @return int
         */
        public function insertId() {
            if(!$this->isConnected()) return false;
            $id = mysql_insert_id($this->db);
            if($id === 0 || $id === false)
                return false;
            else
                return $id;
        }
		
        /**
         * 
         * Returns a single value.
         * You can pass in nothing, a string, or a db result
         * @param unknown_type $arg
         * @return int
         */
        public function getValue($arg = null)
        {
            $result = $this->resulter($arg);
            return $this->hasRows($result) ? mysql_result($result, 0, 0) : false;
        }
		
        /**
         * 
         * Returns an array of the first value in each row.
         * You can pass in nothing, a string, or a db result
         * @param unknown_type $arg
         */
        public function getValues($arg = null) {
            $result = $this->resulter($arg);
            if(!$this->hasRows($result)) return array();

            $values = array();
            mysql_data_seek($result, 0);
            while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                $values[] = array_shift($row);
            return $values;
        }
        
        /**
         * 
         * Returns the first row.
         * You can pass in nothing, a string, or a db result
         * @param unknown_type $arg
         */
        public function getRow($arg = null) {
            $result = $this->resulter($arg);
            return $this->hasRows($result) ? mysql_fetch_array($result, MYSQL_ASSOC) : false;
        }
        
        /**
         * 
         * Returns an array of all the rows.
         * You can pass in nothing, a string, or a db result
         * @param unknown_type $arg
         */
        public function getRows($arg = null) {
            $result = $this->resulter($arg);
            if(!$this->hasRows($result)) return array();

            $rows = array();
            mysql_data_seek($result, 0);
            while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                $rows[] = $row;
            return $rows;
        }

        /**
         * 
         * Escapes a value and wraps it in single quotes.
         * @param unknown_type $var
         */
        public function quote($var) {
            if(!$this->isConnected()) $this->connect();
            if( is_null($var) ){
            	return "NULL";
            }else{
            	return "'" . $this->escape($var) . "'";
            }
        }

        /**
         * 
         * Escapes a value.
         * @param unknown_type $var
         * @throws Exception
         */
        public function escape($var) {
            if(!$this->isConnected()) $this->connect();
            return mysql_real_escape_string($var, $this->db);
        }
        
        /**
         * 
         * Return the count of queries for the last operation
         * @return int
         */
        public function numQueries() {
            return count($this->queries);
        }
        
        /**
         * 
         * Return the last query or false if the numQueries is 0
         * @return mixed
         */
        public function lastQuery() {
            if($this->numQueries() > 0)
                return $this->queries[$this->numQueries() - 1];
            else
                return false;
        }
        
        /**
         * 
         * Die or show the MySQL error @see dieOnError
         */
        private function notify(){
            $err_msg = mysql_error($this->db);
            if( $this->dieOnError ) MF_Error::dieError($err_msg, 500);
            else MF_Error::showError($err_msg, 500);
        }
        
        /**
         * 
         * Takes nothing, a MySQL result, or a query string and returns
         * the correspsonding MySQL result resource or false if none available.
         * @param unknown_type $arg
         */	
        private function resulter($arg = null) {
            if(is_null($arg) && is_resource($this->result))
                return $this->result;
            elseif(is_resource($arg))
                return $arg;
            elseif(is_string($arg))
            {
                $this->query($arg);
                if(is_resource($this->result))
                    return $this->result;
                else
                    return false;
            }
            else
                return false;
        }
    }
