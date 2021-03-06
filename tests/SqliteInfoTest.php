<?php
namespace Atlas\Info;

use Atlas\Info\InfoTest;

class SqliteInfoTest extends InfoTest
{
    protected function create()
    {
        $this->connection->query("ATTACH DATABASE ':memory:' AS {$this->schemaName2}");

        $this->connection->query("
            CREATE TABLE {$this->tableName} (
                id                     INTEGER PRIMARY KEY AUTOINCREMENT,
                name                   VARCHAR(50) NOT NULL,
                test_size_scale        NUMERIC(7,3),
                test_default_null      CHAR(3) DEFAULT NULL,
                test_default_string    VARCHAR(7) DEFAULT 'string',
                test_default_number    NUMERIC(5) DEFAULT 12345,
                test_default_ignore    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->connection->query("
            CREATE TABLE {$this->schemaName2}.{$this->tableName} (
                id                     INTEGER PRIMARY KEY AUTOINCREMENT,
                name                   VARCHAR(50) NOT NULL,
                test_size_scale        NUMERIC(7,3),
                test_default_null      CHAR(3) DEFAULT NULL,
                test_default_string    VARCHAR(7) DEFAULT 'string',
                test_default_number    NUMERIC(5) DEFAULT 12345,
                test_default_ignore    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    protected function drop()
    {
        // all in memory, so no need to drop
    }

    public function provideFetchTableNames()
    {
        return [
            [
                '',
                [$this->tableName, 'sqlite_sequence']
            ],
            [
                $this->schemaName2,
                [$this->tableName, 'sqlite_sequence']
            ],
        ];
    }

    public function provideFetchColumns()
    {
        $columns = [
            'id' => [
                'name' => 'id',
                'type' => 'INTEGER',
                'size' => null,
                'scale' => null,
                'notnull' => false,
                'default' => null,
                'autoinc' => true,
                'primary' => true
            ],
            'name' => [
                'name' => 'name',
                'type' => 'VARCHAR',
                'size' => 50,
                'scale' => null,
                'notnull' => true,
                'default' => null,
                'autoinc' => false,
                'primary' => false
            ],
            'test_size_scale' => [
                'name' => 'test_size_scale',
                'type' => 'NUMERIC',
                'size' => 7,
                'scale' => 3,
                'notnull' => false,
                'default' => null,
                'autoinc' => false,
                'primary' => false
            ],
            'test_default_null' => [
                'name' => 'test_default_null',
                'type' => 'CHAR',
                'size' => 3,
                'scale' => null,
                'notnull' => false,
                'default' => null,
                'autoinc' => false,
                'primary' => false
            ],
            'test_default_string' => [
                'name' => 'test_default_string',
                'type' => 'VARCHAR',
                'size' => 7,
                'scale' => null,
                'notnull' => false,
                'default' => 'string',
                'autoinc' => false,
                'primary' => false
            ],
            'test_default_number' => [
                'name' => 'test_default_number',
                'type' => 'NUMERIC',
                'size' => 5,
                'scale' => null,
                'notnull' => false,
                'default' => '12345',
                'autoinc' => false,
                'primary' => false
            ],
            'test_default_ignore' => [
                'name' => 'test_default_ignore',
                'type' => 'TIMESTAMP',
                'size' => null,
                'scale' => null,
                'notnull' => false,
                'default' => null,
                'autoinc' => false,
                'primary' => false
            ],
        ];

        return [
            [$this->tableName, $columns],
            ["{$this->schemaName2}.{$this->tableName}", $columns],
        ];
    }
}
