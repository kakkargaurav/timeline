<?php

/**
 * Database Connection Class
 *
 * Handles MySQLi connection to MySQL database with error handling
 */
class Database
{
    private $connection;
    private $config;

    public function __construct()
    {
        $this->config = require_once __DIR__ . '/../config/database.php';
        $this->connect();
    }

    /**
     * Establish database connection
     */
    private function connect()
    {
        try {
            $this->connection = new mysqli(
                $this->config['host'],
                $this->config['username'],
                $this->config['password'],
                $this->config['database']
            );

            if ($this->connection->connect_error) {
                throw new Exception("Database connection failed: " . $this->connection->connect_error);
            }

            // Set charset
            $this->connection->set_charset($this->config['charset']);
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get MySQLi connection instance
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Execute a prepared statement with parameters
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }

            if (!empty($params)) {
                $types = $this->getParamTypes($params);
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            return $stmt;
        } catch (Exception $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch all rows from query
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $rows = [];
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        $stmt->close();
        return $rows;
    }

    /**
     * Fetch single row from query
     */
    public function fetch($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    /**
     * Insert data and return last insert ID
     */
    public function insert($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        $insertId = $this->connection->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * Get row count from last statement
     */
    public function rowCount($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        $rowCount = $stmt->affected_rows;
        $stmt->close();
        return $rowCount;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->connection->autocommit(false);
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        $result = $this->connection->commit();
        $this->connection->autocommit(true);
        return $result;
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $result = $this->connection->rollback();
        $this->connection->autocommit(true);
        return $result;
    }

    /**
     * Determine parameter types for prepared statement
     */
    private function getParamTypes($params)
    {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 's'; // Default to string
            }
        }
        return $types;
    }

    /**
     * Close database connection
     */
    public function close()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct()
    {
        $this->close();
    }
}