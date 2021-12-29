<?php if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) { header( "HTTP/1.1 404 Not Found"); die();} ?>
<?php
class DB {

    private $limit = 0;
    private $query, $result, $con;

    public function __construct() {
        require __DIR__ . '/config.php';
        $this->con = mysqli_connect($location, $username, $password ,$database);
        if(!$this->con) {
            echo 'Cannot establish database connection';
            die();
        }
    }

    public function fire($query) {
        $this->query = $query;
        $this->addLimit(); // Adds the limit if set by user
        $this->result = mysqli_query($this->con, $this->query); // Fires the actuall query into MYSQL
        if(!$this->result) {
            $this->printError();
            return false; // Returns false if query failed to execute
        }
        if($this->getQueryType() == 'SELECT') $this->result = $this->fetchAllRows(); // Store an array in result variable if query is of type SELECT
        return $this->result;
    }

    public function insert($table, $fields) {
        $query = "INSERT INTO `$table` ";
        $keys = "(";
        $values = "(";
        
        foreach ($fields as $key => $value) {
            $keys .= $key . ',';
            $values .= "'" . mysqli_real_escape_string($this->con, $value) . "'" . ',';
        }

        $keys = $this->removeLastChar($keys);
        $values = $this->removeLastChar($values);

        $keys .= ')';
        $values .= ')';

        $query .= $keys . ' VALUES ' . $values;

        return $this->fire($query);
    }

    public function select($table, $fields = '') {
        if($fields) {
            $list = '';
            foreach ($fields as $key => $value) {
                if($list) $list .= ' AND ';
                $list .= $key . '=' . "'" . $value . "'";
            }
            return $this->fire("SELECT * FROM $table WHERE " . $list);
        } else {
            return $this->fire("SELECT * FROM $table");
        }
    }

    public function fetch($table, $fields = '') { return $this->select($table, $fields); }

    public function update($table, $match, $fields) {
        $list = '';
        $list2 = '';
        foreach ($fields as $key => $value) {
            if($list) $list .= ', ';
            $list .= $key . '=' . "'" . mysqli_real_escape_string($this->con,$value) . "'";
        }

        foreach ($match as $key => $value) {
            if($list2) $list2 .= ' AND ';
            $list2 .= $key . '=' . "'" . $value . "'";
        }

        return $this->fire("UPDATE $table SET " . $list . " WHERE " . $list2);    
    }

    public function delete($table, $fields) {
        $list = '';
        foreach ($fields as $key => $value) {
            if($list) $list .= ' AND ';
            $list .= $key . '=' . "'" . $value . "'";
        }
        return $this->fire("DELETE FROM $table WHERE " . $list);
    }

    public function getNumRows() { return count($this->result); }

    public function getSingleRow() { foreach($this->result as $row) return $row; }

    public function getAllRows() { return $this->result; }

    public function getError() { return mysqli_error($this->con); }

    public function getQuery() { return $this->query; }

    private function fetchAllRows() {
        $rows = [];
        while($row = mysqli_fetch_assoc($this->result)) {
            array_push($rows,$row);
        }
        return $rows;
    }
    
    private function removeLastChar($string) { return substr($string,0,-1); }

    private function printError() {
        if(ini_get('display_errors'))
            echo "query => $this->query<br> error => " . $this->getError();
    }

    private function isLocalhost() { return $_SERVER['SERVER_NAME'] == 'localhost'; }

    private function getQueryType() { return strtoupper(explode(' ', $this->query)[0]); }

    private function addLimit() {
        $this->query = $this->limit ? $this->query . ' LIMIT ' . $this->limit : $this->query;
        $this->limit = 0;
    }

}