<?php
	class Database {
		var $err_msg="";
		private $host=DB_SERVER;
		private $db_user=DB_USER;
		private $db_password=DB_PASS;
		private $db_name=DB_NAME;
		private $conn;
		private $stmt;

		function __construct() {
			try {
				$this->conn=new PDO("mysql:host=".$this->host.";dbname=".$this->db_name, $this->db_user, $this->db_password);
			} catch (PDOException $e) {
				echo $e->getMessage();
				die();
			}
		}
		function query($query){
			try {
				$this->stmt=$this->conn->prepare($query);
				$result=$this->stmt->execute();
				if(!$result){
					echo $this->err_msg = '<div style="border:dashed 1px #ff0000; background:#ffffcc; padding:10px; margin:5px; color:#ff0000">'.$this->conn->errorInfo().'<br><br>'.$query.'</div>';
					return false;
				} else{
					return $result;
				}
			} catch (PDOException $e) {
				print $e->getMessage();
				die();
			}
		}
		function get_db_error(){
			return $this->conn->errorInfo();
		}
		function fetch_array(){
			$this->stmt->setFetchMode(PDO::FETCH_ASSOC);
			return $this->stmt->fetch();
		}
		function fetch_assoc(){
			$this->stmt->setFetchMode(PDO::FETCH_ASSOC);
			return $this->stmt->fetch();
		}
		function fetch_all() {
			$this->stmt->setFetchMode(PDO::FETCH_ASSOC);
			return $this->stmt->fetchAll();
		}
		function num_rows($result){
			return $this->stmt->rowCount();
		}
		function insert_id(){
			return intval($this->conn->lastInsertId());
		}
		function fetch_assoc_by_query($query, $arr=''){
			$result=$this->select($query, $arr);
			return $this->stmt->fetch(PDO::FETCH_ASSOC);
		}
		function fetch_array_by_query($query, $arr=''){
			$result=$this->select($query, $arr);
			return $this->stmt->fetch();
		}
		function select($query,$arr='') {
			$this->stmt=$this->conn->prepare($query);
			//print_r($this->stmt); die();
			if (is_array($arr)) {
				$result=$this->stmt->execute($arr);
			} else {
				$result=$this->stmt->execute();
			}
			return $result;
		}
		function get_insert_id(){
			return intval($this->conn->lastInsertId());
		}
		function get_error(){
			return $this->err_msg;
		}
		function get_row($id,$table_name){
			$query="select * from {$table_name} where id=?";
			$row=$this->fetch_array_by_query($query, array($id));
			return $row;
		}
		function insert($arr,$table_name){
			$cols=implode(",",array_keys($arr));
			$query_text="";
			$data=array();
			foreach ($arr as $key=>$values) {
				if ($query_text=="") {
					$query_text="?";
					$data[]=$values;
				} else {
					$query_text.=", ?";
					$data[]=$values;
				}
			}
			$query="insert into {$table_name} ($cols) values ($query_text)";
			
			$result=$this->select($query, $data);
			if($result){
				return $this->insert_id();
			} else{
				$this->err_msg=implode(":",$this->stmt->errorInfo());
				return false;
			}
		}
		function update($id,$arr,$table_name){
			$q='';
			$data=array();
			foreach($arr as $key=>$value){
				if($q=='') {
					$q.="$key=?";
					$data[]=$value;
				} else {
					$q.=",$key=?";
					$data[]=$value;
				}
			}
			$query="update {$table_name} set $q where id=".intval($id);
			$result = $this->select($query, $data);
			return $result;
		}
		function delete($id,$table_name){
			$query="delete from {$table_name} where id=".intval($id);
			$result=$this->query($query);
			return $result;
		}
		function fetch($table_name,$start=0,$total=0){
			if($total==0){
				$query="select * from {$table_name}";
			} else{
				$query="select * from {$table_name} limit $start,$total";
			}
			$result=$this->query($query);
			return $this->stmt->fetchAll();
		}
		function count_rows($table_name,$where=''){
			if($where=='') $where='1';
			$this->query("select count(*) as count from {$table_name} where $where");
			$this->stmt->setFetchMode(PDO::FETCH_ASSOC);
			$row=$this->stmt->fetch();
			return intval($row['count']);
		}
		function __destruct() {
			$this->conn=null;
		}
	}

	$db=new Database();
?>
