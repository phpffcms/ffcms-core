<?php

namespace Ffcms\Core\Migrations;


/**
 * Interface MigrationInterface. Basic migrations architecture interface
 * @package Ffcms\Console\Migrations
 */
interface MigrationInterface
{
    /**
     * Execute actions when migration is up
     * @return void
     */
    public function up();

    /**
     * Execute actions when migration is down
     * @return void
     */
    public function down();

    /**
     * Seed created table via up() method with some data
     * @return void
     */
    public function seed();
}