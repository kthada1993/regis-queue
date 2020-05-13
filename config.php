


<?php

date_default_timezone_set("Asia/Bangkok");

class ConfigDB
{


	function connectDBMaster($dbName)
	{
		try {
			$con = new PDO(
				"mysql:host=IPHOSXP;dbname=hos",
				"u", //username
				"p", //password
				array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
			);
			return $con;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	function connectDBSlave($dbName)
	{
		try {
			$con = new PDO(
				"mysql:host=IPQ4U;dbname=queue",
				"u", //username
				"p", //password
				array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
			);
			return $con;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
}

class Query extends ConfigDB
{


	function selectDBSlave($sql, $dbName)
	{
		try {
			$pdo = $this->connectDBSlave($dbName);
			$stmt = $pdo->query($sql);
			//$sql = mysql_query($sql);
			$data = array();
			while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
				$data[] = $row;
			}

			/*while ($row = mysql_fetch_object($sql)) {
			$data[] = $row;
		}*/
			return $data;
		} catch (PDOException $e) {
			return false;
		}
	}

	function selectDBMaster($sql, $dbName)
	{
		try {
			$pdo = $this->connectDBMaster($dbName);
			$stmt = $pdo->query($sql);
			//$sql = mysql_query($sql);
			$data = array();
			while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
				$data[] = $row;
			}

			/*while ($row = mysql_fetch_object($sql)) {
			$data[] = $row;
		}*/
			return $data;
		} catch (PDOException $e) {
			return false;
		}
	}

	function queryDbMaster()
	{
	}

	function queryDbSlave($sql, $dbName)
	{
		try {
			$pdo = $this->connectDBSlave($dbName);
			$stmt = $pdo->exec($sql);
			return true;
		} catch (PDOException $e) {
			return false;
		}
	}
}


class getData extends Query
{

	function getQueueHosXp($type)
	{
		$date = date('Y-m-d');

		$sql = "SELECT " . $type . " from ovst o WHERE o.vstdate ='$date' and o.main_dep in (001,002,058,010,043,061,062,055) ORDER BY o.vsttime";
		$row = $this->selectDBMaster($sql, 'hos');
		return $row;
	}

	function getOvstHosXp()
	{
		$date = date('Y-m-d');
		$vn = $this->getVnQ4u();
		$sql = "SELECT vn,hn,vstdate,main_dep,cur_dep from ovst where vstdate='$date' and vn not in ($vn)";
		$row = $this->selectDBMaster($sql, 'hos');
		return $row;
	}

	function getQueueQ4u()
	{
		$date = date('Y-m-d');
		$sql = "SELECT vn from q4u_queue q WHERE q.date_serv ='$date'";
		$row = $this->selectDBSlave($sql, 'queue');
		return $row;
	}

	function getVnQ4u()
	{
		$row = $this->getQueueQ4u();
		$notinVN = "";
		for ($i = 0; $i < count($row); $i++) {
			if ($i == count($row) - 1) {
				$notinVN .= "'" . $row[$i]->vn . "'";
			} else {
				$notinVN .= "'" . $row[$i]->vn . "',";
			}
		}

		if ($notinVN == '') {
			$notinVN = 0;
		} else {
			$notinVN = $notinVN;
		}

		return $notinVN;
	}

	function getQ4uServicePoint($code)
	{

		$sql = "SELECT service_point_id from q4u_service_points WHERE local_code ='$code'";
		$row = $this->selectDBSlave($sql, 'queue');
		return $row[0]->service_point_id;
	}

	function getQ4uPerson()
	{
		$sql = "SELECT q.hn from q4u_person q ";
		$row = $this->selectDBSlave($sql, 'queue');
		return $row;
	}

	function getQ4uPersonCount($hn)
	{
		$sql = "SELECT count(q.hn) as cc from q4u_person q WHERE q.hn='$hn'";
		$row = $this->selectDBSlave($sql, 'queue');
		return $row[0]->cc;
	}

	function intersecHosXpQ4u()
	{

		$getQueueHosXp = $this->getQueueHosXp('vn');
		$getQueueQ4u = $this->getQueueQ4u();
		$intersec = array_diff($this->convertObjectToArrayHN($getQueueHosXp, 'vn'), $this->convertObjectToArrayHN($getQueueQ4u, 'vn'));
		return $intersec;
	}


	function intersecHosXpQ4uPerson()
	{

		$getQueueHosXp = $this->getQueueHosXp('hn');
		$getQ4uPerson = $this->getQ4uPerson();
		$intersec = array_diff($this->convertObjectToArrayHN($getQueueHosXp, 'hn'), $this->convertObjectToArrayHN($getQ4uPerson, 'hn'));
		return $intersec;
	}

	function convertObjectToArrayHN($object, $type)
	{

		$data = array();
		for ($i = 0; $i < count($object); $i++) {
			$data[$i] = $object[$i]->$type;
		}
		return $data;
	}


	function prepareDataQ4u($vn)
	{
		$sql = "SELECT o.hn,o.vn,k.opd_qs_room_id as service_point_id,o.pt_priority+1 as priority_id,o.vstdate as date_serv,o.vsttime as time_serv,
		concat(k.display_text,o.oqueue) as queue_number,k.depcode
		from ovst o 
		INNER JOIN kskdepartment k on o.main_dep = k.depcode
		WHERE o.vn ='$vn'";

		/*$sql = "SELECT o.hn,o.vn,k.opd_qs_room_id as service_point_id,o.pt_priority+1 as priority_id,o.vstdate as date_serv,o.vsttime as time_serv,
		o.oqueue as queue_number,k.depcode
		from ovst o 
		INNER JOIN kskdepartment k on o.main_dep = k.depcode
		WHERE o.vn ='$vn'";*/

		$row = $this->selectDBMaster($sql, 'hos');
		return $row;
	}

	function prepareDataPersonQ4u($hn)
	{

		$sql = "SELECT hn,pname as title,fname as first_name,lname as last_name,birthday as birthdate,sex from patient WHERE hn='$hn'";
		$row = $this->selectDBMaster($sql, 'hos');
		return $row;
	}

	function getQueueInterView()
	{
		$date = date('Y-m-d');
		$sql = "SELECT count(q.queue_id)+1 as cc from q4u_queue q WHERE q.date_serv ='$date'";
		$row = $this->selectDBSlave($sql, 'queue');
		return $row[0]->cc;
	}

	function checkCurrentHosxPQ($vn)
	{
		$sql = "SELECT main_dep,cur_dep from ovst WHERE vn= '$vn'";
		$row = $this->selectDBMaster($sql, 'hos');
		return $row;
	}

	function getServicePointHosxp($depcode)
	{
		$sql = "SELECT opd_qs_room_id from kskdepartment WHERE depcode='$depcode'";
		$row = $this->selectDBMaster($sql, 'hos');
		return $row[0]->opd_qs_room_id;
	}

	function getServicepoint()
	{
		$sql = "SELECT * from q4u_service_points";
		$row = $this->selectDBSlave($sql, 'queue');
		return $row;
	}

	function clearQueue()
	{
		$date = date('Y-m-d');
		$sql = "DELETE from q4u_queue where date_serv <> '$date'";
		$sql2 = "DELETE from q4u_queue_detail where date_serv <> '$date'";
		$sql3 = "DELETE from q4u_queue_group_detail where date_serv <> '$date'";
		$this->queryDbSlave($sql, 'queue');
		$this->queryDbSlave($sql2, 'queue');
		$this->queryDbSlave($sql3, 'queue');
	}
}

?>