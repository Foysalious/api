<?php namespace Sheba\Database;

use Illuminate\Database\Connection as BaseConnection;

class Connection extends BaseConnection
{
    /**
     * The connection resolvers.
     *
     * @var array
     */
    protected static $resolvers = [];

    /**
     * Indicates if changes have been made to the database.
     *
     * @var int
     */
    protected $recordsModified = false;

    /**
     * Get the connection resolver for the given driver.
     *
     * @param string $driver
     * @return mixed
     */
    public static function getResolver($driver)
    {
        return static::$resolvers[$driver] ?? null;
    }

    /**
     * Get the current PDO connection used for reading.
     *
     * @return \PDO
     */
    public function getReadPdo()
    {
        if ($this->transactions >= 1) {
            return $this->getPdo();
        }

        if ($this->getConfig('sticky') && $this->recordsModified) {
            return $this->getPdo();
        }

        return $this->readPdo ?: $this->getPdo();
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($me, $query, $bindings) {
            if ($me->pretending()) {
                return true;
            }

            $statement = $me->getPdo()->prepare($query);
            $bindings = $me->prepareBindings($bindings);
            $this->recordsHaveBeenModified();
            return $statement->execute($bindings);
        });
    }

    /**
     * Indicate if any records have been modified.
     *
     * @param bool $value
     * @return void
     */
    public function recordsHaveBeenModified($value = true)
    {
        if (!$this->recordsModified) {
            $this->recordsModified = $value;
        }
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($me, $query, $bindings) {
            if ($me->pretending()) {
                return 0;
            }

            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use PDO to fetch the affected.
            $statement = $me->getPdo()->prepare($query);

            $statement->execute($me->prepareBindings($bindings));

            $this->recordsHaveBeenModified(($count = $statement->rowCount()) > 0);

            return $count;
        });
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param string $query
     * @return bool
     */
    public function unprepared($query)
    {
        return $this->run($query, [], function ($me, $query) {
            if ($me->pretending()) {
                return true;
            }

            $this->recordsHaveBeenModified($change = ($me->getPdo()->exec($query) === false ? false : true));

            return $change;
        });
    }
}
