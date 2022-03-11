<?php if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) { header( "HTTP/1.1 404 Not Found"); die();} ?>
<?php
class DB {

    private $limit = 0;
    private $query, $result, $con;

    public function __construct() {
        /** @var string $location */
        /** @var string $username */
        /** @var string $password */
        /** @var string $database */
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
        $keys = '(`' . implode('`,`', array_keys($fields)) . '`)';
        $values = "('" . implode("','", $this->escapeArray(array_values($fields))) . "')";

        $query = "INSERT INTO `$table` " . $keys . ' VALUES ' . $values;

        return $this->fire($query);
    }

    public function select($table, $fields = []) {
        if($fields) {
            $list = $this->setSQLString($fields);
            return $this->fire("SELECT * FROM $table WHERE " . $list);
        } else {
            return $this->fire("SELECT * FROM $table");
        }
    }

    public function fetch($table, $fields = []) { return $this->select($table, $fields); }

    public function update($table, $match, $fields) {
        $match = $this->setSQLString($match);
        $fields = $this->setSQLString($fields, ",");

        return $this->fire("UPDATE $table SET " . $fields . " WHERE " . $match);
    }

    public function delete($table, $fields) {
        $list = $this->setSQLString($fields);
        return $this->fire("DELETE FROM $table WHERE " . $list);
    }

    public function getNumRows() { return count($this->result); }

    public function getSingleRow() { return $this->result[0]; }

    public function getAllRows() { return $this->result; }

    public function getError() { return mysqli_error($this->con); }

    public function getQuery() { return $this->query; }

    public function affectedRows() { return $this->con->affected_rows; }

    private function fetchAllRows() {
        $rows = [];
        while($row = mysqli_fetch_assoc($this->result)) {
            array_push($rows,$row);
        }
        return $rows;
    }

    private function printError() {
        if(ini_get('display_errors'))
            echo "query => $this->query<br>\nerror => " . $this->getError();
    }

    private function getQueryType() { return strtoupper(explode(' ', $this->query)[0]); }

    private function addLimit() {
        $this->query = $this->limit ? $this->query . ' LIMIT ' . $this->limit : $this->query;
        $this->limit = 0;
    }

    private function setSQLString(array $array, string $separator = 'AND') {
        return implode(" $separator ", array_map(function ($key, $value) {
            if(is_array($value)) {
                return '(' . implode(' OR ' , array_map(function ($value) use ($key) {
                        if(is_array($value)) {
                            return "(`$key` >= '$value[0]' AND '$value[1]' >= `$key`)";
                        } else {
                            return "`$key` = '$value'";
                        }
                    }, $this->escapeArray($value))) . ')';
            } else {
                return "`$key` = '$value'";
            }
        }, array_keys($array), $this->escapeArray(array_values($array))));
    }

    private function escapeArray(array $array) {
        return array_map(function ($item) {
            if(is_array($item)) return $item;
            return mysqli_real_escape_string($this->con, $item);
        }, $array);
    }
}