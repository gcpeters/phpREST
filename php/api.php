<?php
require_once("Rest.inc.php");

class API extends REST {
	public $data = "";
	const DB_SERVER = "localhost";
	const DB_USER = "Database_Username";
	const DB_PASSWORD = "Database_Password";
	const DB = "Database_Name";

	private $db = NULL;

	public function __construct() {
		parent::__construct();// Init parent contructor
		$this->dbConnect();// Initiate Database connection
	}

	//Database connection
	private function dbConnect() {
		$this->db = mysql_connect(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD);
		
		if ($this->db) {
			mysql_select_db(self::DB, $this->db);
		}
	}

	//Public method for access api.
	//This method dynmically call the method based on the query string
	public function processApi() {
		$func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));
		
		if ( (int) method_exists($this,$func) > 0 ) {

			$this->$func();
		} else {

			$this->response('',404); 
		}
		// If the method not exist with in this class, response would be "Page not found".
	}

	private function login() {

	}

	private function workouts() {
		// Cross validation if the request method is GET else it will return "Not Acceptable" status
		if ($this->get_request_method() != "GET") {
			$this->response('',406);
		}

		$queryStr = "SELECT id, title, complete_date 
					 FROM workouts 
					 WHERE completed=1
				 	 ORDER BY completed_date DESC";

		$sql = mysql_query($queryStr, $this->db);

		if (mysql_num_rows($sql) > 0) {

			$result = array();

			while ($rlt = mysql_fetch_array($sql, MYSQL_ASSOC)) {
				$result[] = $rlt;
			}

			// If success everythig is good send header as "OK" and return list of workouts in JSON format
			$this->response($this->json($result), 200);
		}

		// If no records "No Content" status
		$this->response('', 204);
	}

	private function deleteWorkout() {
		if ($this->get_request_method() != "DELETE") {

			$this->response('',406);
		}

		$id = (int) $this->_request['id'];

		if($id > 0) {

			$queryStr = "DELETE FROM workouts WHERE id = $id";
			mysql_query($queryStr);
			$success = array('status' => "Success", "msg" => "Successfully one record deleted.");
			$this->response($this->json($success), 200);
		} else {

			$this->response('',204); // If no records "No Content" status
		}
	}

	//Encode array into JSON
	private function json($data) {
		if (is_array($data)) {

			return json_encode($data);
		}
	}
}

// Initiiate Library
$api = new API;
$api->processApi();
?>