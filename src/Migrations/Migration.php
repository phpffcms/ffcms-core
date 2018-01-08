<?php

namespace Ffcms\Core\Migrations;

use Apps\ActiveRecord\Migration as MigrationRecord;
use Illuminate\Database\Capsule\Manager as DatabaseManager;

/**
 * Class Migration. Basic features for migration
 * @package Ffcms\Core\Migrations
 */
class Migration
{
    /** @var string */
    public $now;
    /** @var string|null */
    private $connection;
    /** @var string */
    private $name;

    /**
     * Migration constructor. Pass connection name inside if exist
     * @param null $connectionName
     */
    public function __construct($migrationName, $connectionName = null)
    {
        $this->name = $migrationName;
        $this->connection = $connectionName;
        $this->now = date('Y-m-d H:i:s', time());
    }

    /**
     * Insert data into migration table
     */
    public function up()
    {
        $record = new MigrationRecord();
        if ($this->connection !== null) {
            $record->setConnection($this->connection);
        }
        $record->migration = $this->name;
        $record->save();
    }

    /**
     * Remove data from migration table
     */
    public function down()
    {
        $record = new MigrationRecord();
        if ($this->connection !== null) {
            $record->setConnection($this->connection);
        }
        $record->where('migration', $this->name)->delete();
    }

    /**
     * Set connection name
     * @param string|null $name
     * @return void
     */
    public function setConnection($name = null)
    {
        $this->connection = $name;
    }

    /**
     * Get database connection instance
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return DatabaseManager::connection($this->connection);
    }

    /**
     * Get database connection schema builder for current instance of connection
     * @return \Illuminate\Database\Schema\Builder
     */
    public function getSchema()
    {
        return DatabaseManager::schema($this->connection);
    }
}
