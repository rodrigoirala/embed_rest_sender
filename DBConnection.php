<?php

/**
 * @author Rodrigo Irala <rodrigo.irala@gmail.com>
 */
class DBConnection
{
	private static $instance = NULL;
	private $con;

	private function __construct()
	{
		$this->con = new PDO("mysql:host=localhost;dbname=ango_personas", "siu_labintegrado", "i1pPBm1w7S");
	}

	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new DBConnection();
		}
		return self::$instance;
	}

	public function beginTransaction()
	{
		$this->con->beginTransaction();
	}

	public function commit()
	{
		$this->con->commit();
	}

	public function rollbackTransaction()
	{
		$this->con->rollBack();
	}

	/**
	 * 
	 * @param unknown $sql
	 * @throws Exception
	 * @return multitype:
	 */
	public function executeQuery($sql)
	{

		try {
			$stmt = $this->con->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll();
		} catch (Exception $excep) {
			throw $excep;
		}
	}

	/**
	 * 
	 * @param unknown $sql
	 * @throws Exception
	 * @return number
	 */
	public function executeUpdate($sql)
	{

		try {
			return $this->con->exec($sql);
		} catch (Exception $excep) {
			throw $excep;
		}
	}

	/**
	 * 
	 * @param unknown $sql
	 * @throws Exception
	 * @return PDOStatement
	 */
	public function prepareStatement($sql)
	{

		try {
			return $this->con->prepare($sql);
		} catch (Exception $excep) {
			throw $excep;
		}
	}

	/**
	 * 
	 * @param PDOStatement $stm
	 * @throws Exception
	 * @return boolean
	 */
	public function executeStatement(PDOStatement $stm)
	{

		try {
			return $stm->execute();
		} catch (Exception $excep) {
			throw $excep;
		}
	}
}
