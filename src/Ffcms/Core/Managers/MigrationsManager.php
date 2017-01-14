<?php

namespace Ffcms\Core\Managers;


use Apps\ActiveRecord\Migration;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\FileSystem\Normalize;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class MigrationsManager. Manage migration files and database.
 * @package Ffcms\Core\Managers
 */
class MigrationsManager
{
    const DEFAULT_DIR = '/Private/Migrations';

    private $dir;
    private $connection;
    private $customDir = false;

    /**
     * MigrationsManager constructor. Pass directory inside or use default
     * @param string|null $dir
     * @param string|null $connectionName
     */
    public function __construct($dir = null, $connectionName = null)
    {
        if ($dir === null || !Obj::isString($dir)) {
            $dir = static::DEFAULT_DIR;
        }

        $this->dir = rtrim($dir, '/');
        $this->connection = $connectionName;

        if ($this->dir !== static::DEFAULT_DIR) {
            $this->customDir = true;
        }
    }

    /**
     * Search migration files. If $exists = false search only not installed files, if true - only installed
     * @param string|null $query
     * @param bool $exist
     * @return array|false
     */
    public function search($query = null, $exist = false)
    {
        // initialize db migration record
        $records = new Migration();
        if ($this->connection !== null) {
            $records->setConnection($this->connection);
        }
        // get installed migrations
        $dbmigrations = Arr::pluck('migration', $records->get()->toArray());

        // list migrations
        $migrations = File::listFiles($this->dir, ['.php'], true);
        if (!Obj::isArray($migrations) || count($migrations) < 1) {
            return false;
        }

        $found = false;
        foreach ($migrations as $migration) {
            // parse migration fullname
            $fullName = Str::cleanExtension($migration);
            // check if name contains search query conditions
            if (!Str::likeEmpty($query) && !Str::contains($query, $fullName)) {
                continue;
            }
            // check initialize conditions (equals to $exist)
            if (File::exist($this->dir . '/' . $migration)) {
                if (Arr::in($fullName, $dbmigrations) === $exist) {
                    $found[] = $migration;
                }
            }
        }

        return $found;
    }

    /**
     * Run migration up
     * @param string|array $file
     * @return bool
     */
    public function makeUp($file)
    {
        // check if argument is array of files and run recursion
        if (Obj::isArray($file)) {
            $success = true;
            foreach ($file as $single) {
                $exec = $this->makeUp($single);
                if (!$exec) {
                    $success = false;
                }
            }
            return $success;
        }

        // check if migration file is exists
        if (!File::exist($this->dir . '/' . $file)) {
            return false;
        }

        // check if migration file located in extend directory and copy to default
        if (Normalize::diskFullPath($this->dir) !== Normalize::diskFullPath(static::DEFAULT_DIR)) {
            File::copy($this->dir . DIRECTORY_SEPARATOR . $file, static::DEFAULT_DIR . DIRECTORY_SEPARATOR . $file);
        }

        // include migration and get class name
        File::inc($this->dir . '/' . $file, false, false);
        $fullName = Str::cleanExtension($file);
        $class = Str::firstIn($fullName, '-');

        // check if class is instance of migration interface
        if (!class_exists($class) || !is_a($class, 'Ffcms\Core\Migrations\MigrationInterface', true)) {
            return false;
        }

        // implement migration
        $init = new $class($fullName, $this->connection);
        $init->up();
        $init->seed();

        return true;
    }

    /**
     * Make migration down
     * @param array|string $file
     * @return bool
     */
    public function makeDown($file)
    {
        if (Obj::isArray($file)) {
            $success = true;
            foreach ($file as $item) {
                $exec = $this->makeDown($file);
                if (!$exec) {
                    $success = false;
                }
                return $success;
            }
        }

        // check if exists
        if (!File::exist($this->dir . '/' . $file)) {
            return false;
        }

        File::inc($this->dir . '/' . $file, false, false);
        $fullName = Str::cleanExtension($file);
        $class = Str::firstIn($fullName, '-');

        // check if class is instance of migration interface
        if (!class_exists($class) || !is_a($class, 'Ffcms\Core\Migrations\MigrationInterface', true)) {
            return false;
        }

        // init migration and execute down method
        $init = new $class($fullName, $this->connection);
        $init->down();

        return true;
    }
}