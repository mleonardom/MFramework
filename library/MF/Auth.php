<?php
    class MF_Auth
    {
        /**
         * 
         * Singleton object
         * @var MF_Auth
         */
        private static $_instance;
		
        /**
         * 
         * The Auth user id attribute
         * @var int
         */
        public $id;
        
        /**
         * 
         * The Auth user username attribute
         * @var string
         */
        public $username;
        
        /**
         * 
         * The Auth user active attribute
         * @var int
         */
        public $active;
        
        /**
         * 
         * The Auth user access level attribute
         * @var string
         */
        public $level;
        
        /**
         * 
         * Model User object (if available)
         * @var User
         */
        public $user;
		
        /**
         * 
         * true if any user is logged in
         * @var boolean
         */
        private $loggedIn;

        /**
         * 
         * Call with no arguments to attempt to restore a previous logged in session
         * which then falls back to a guest user (which can then be logged in using
         * $this->login($un, $pw). Or pass a user_id to simply login that user. The
         * $seriously is just a safeguard to be certain you really do want to blindly
         * login a user. Set it to true.
         */
        private function __construct() {
            $this->id             = null;
            $this->username       = null;
            $this->active         = null;
            $this->level          = 'guest';
            $this->user           = null;
            $this->loggedIn       = false;

            if(class_exists('User') && (is_subclass_of('User', 'MF_Model')))
                $this->user = new User();
                
            if($this->attemptSessionLogin()){
                return;
            }
        }

        /**
         * Standard singleton
         * @return Auth
         */
        public static function getInstance() {
            if( self::$_instance === null )
                self::$_instance = new MF_Auth();
            return self::$_instance;
        }

        /**
         * 
         * You'll typically call this function when a user logs in using
         * a form. Pass in their username and password.
         * Takes a username and a *plain text* password
         * @param string $un username
         * @param string $pw unhashed password
         */
        public function login($un, $pw) {
            $pw = $this->createHashedPassword($pw);
            if( !$this->attemptLogin($un, $pw) ) return false;
            
            if( class_exists("User") && is_subclass_of("User", "MF_Model") ){
            	$this->user->last_login = date("Y-m-d H:i:s");
            	$this->user->save();
            }
            return true;
        }
		
        /**
         * 
         * Close the user session
         */
        public function logout(){
            $this->id             = null;
            $this->username       = null;
            $this->active         = null;
            $this->level          = 'guest';
            $this->user           = null;
            $this->loggedIn       = false;

            if(class_exists('User') && (is_subclass_of('User', 'MF_Model')))
                $this->user = new User();

            unset($_SESSION['user_id']);
        }
		
        /**
         * 
         * Is a user logged in? This was broken out into its own function
         * in case extra logic is ever required beyond a simple bool value.
         * @return loggedIn
         */
        public function isLogged() {
            return $this->loggedIn;
        }
		
        // 
        // 
        // 
        // 
        /**
         * 
         * Login a user simply by passing in their username or id. Does
         * not check against a password. Useful for allowing an admin user
         * to temporarily login as a standard user for troubleshooting.
         * @param unknown_type $user_to_impersonate Takes an id or username
         */
        public function impersonate($user_to_impersonate){
        	if(class_exists('User') && (is_subclass_of('User', 'MF_Model'))){
                $this->user = new User();
                $table_name = $this->user->tableName;
        	}else{
        		$table_name = 'users';
        	}
            $db = MF_Database::getDatabase();

            if(ctype_digit($user_to_impersonate))
                $row = $db->getRow('SELECT * FROM `'.$table_name.'` WHERE id = ' . $db->quote($user_to_impersonate));
            else
                $row = $db->getRow('SELECT * FROM `'.$table_name.'` WHERE username = ' . $db->quote($user_to_impersonate));

            if(is_array($row))
            {
                $this->id       = $row['id'];
                $this->username = $row['username'];
                $this->active = $row['active'];
                $this->level    = $row['level'];

                // Load any additional user info if Model and User are available
                if(class_exists('User') && (is_subclass_of('User', 'MF_Model')))
                {
                    $this->user = new User();
                    $this->user->id = $row['id'];
                    $this->user->load($row);
                }

                $row['password'] = $this->createHashedPassword($row['password']);

                $this->storeSessionData($this->id);
                $this->loggedIn = true;

                return true;
            }

            return false;
        }
        
        /**
         * 
         * Attempt to login using data stored in the current session
         */
        private function attemptSessionLogin(){
            if(isset($_SESSION['user_id']))
                return $this->impersonate($_SESSION['user_id']);
            else
                return false;
        }
		
        /**
         * 
         * The function that actually verifies an attempted login and
         * processes it if successful.
         * @param password $un username
         * @param unknown_type $pw <strong>hashed</strong> password
         * @return boolean true if the attemp is successful
         */
        private function attemptLogin($un, $pw)
        {
        	if(class_exists('User') && (is_subclass_of('User', 'MF_Model'))){
                $this->user = new User();
                $table_name = $this->user->tableName;
        	}else{
        		$table_name = 'users';
        	}
        	
            $db = MF_Database::getDatabase();
            
            // We SELECT * so we can load the full user record into the user Model later
            $row = $db->getRow('SELECT * FROM `'.$table_name.'` WHERE username = ' . $db->quote($un));
            if($row === false) return false;
            
            //$row['password'] = $this->createHashedPassword($row['password']);

            if($pw != $row['password']) return false;

            $this->id       = $row['id'];
            $this->username = $row['username'];
            $this->active = $row['active'];
            $this->level    = $row['level'];

            // Load any additional user info if Model and User are available
            if(class_exists('User') && (is_subclass_of('User', 'MF_Model'))) {
                $this->user->id = $row['id'];
                $this->user->load($row);
            }else{
            	MF_Error::dieError( "Class User dont exists or is not a subclass of MF_Model", 500 );
            }
			
            if( $this->active ) $this->storeSessionData($this->id);
            $this->loggedIn = true;

            return true;
        }

        // Takes a username and a *hashed* password
        /**
         * 
         * Store a user in the user session data
         * @param int $u_id User id
         */
        private function storeSessionData($u_id)
        {
            if(headers_sent()) return false;
            $_SESSION['user_id'] = $u_id;
        }
		
        /**
         * 
         * Hashed and return a password
         * @param string $pw unhashed password
         * @return hashed_password
         */
        private function createHashedPassword($pw) {
            return md5($pw);
        }
    }
