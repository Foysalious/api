<?php namespace Sheba\Database\Connectors;

use Illuminate\Database\Connectors\ConnectionFactory as IlluminateConnectionFactory;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SqlServerConnection;
use Sheba\Database\Connection;
use Sheba\Database\MySqlConnection;

class ConnectionFactory extends IlluminateConnectionFactory
{
    /**
     * @param string $driver
     * @param \Closure|\PDO $connection
     * @param string $database
     * @param string $prefix
     * @param array $config
     * @return \Illuminate\Database\Connection|\Illuminate\Database\MySqlConnection|\Illuminate\Database\PostgresConnection|\Illuminate\Database\SQLiteConnection|\Illuminate\Database\SqlServerConnection|\Sheba\Database\MySqlConnection
     */
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        if ($resolver = Connection::getResolver($driver)) {
            return $resolver($connection, $database, $prefix, $config);
        }

        switch ($driver) {
            case 'mysql':
                return new MySqlConnection($connection, $database, $prefix, $config);
            case 'pgsql':
                return new PostgresConnection($connection, $database, $prefix, $config);
            case 'sqlsrv':
                return new SqlServerConnection($connection, $database, $prefix, $config);
        }

        return parent::createConnection($driver, $connection, $database, $prefix, $config);
    }
}
