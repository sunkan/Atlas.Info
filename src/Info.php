<?php
declare(strict_types=1);

/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
 */
namespace Atlas\Info;

use Atlas\Pdo\Connection;

abstract class Info
{
    protected $connection;

    public static function new(Connection $connection) : Info
    {
        $driver = $connection->getDriverName();
        $class = __NAMESPACE__ . '\\' . ucfirst($driver) . 'Info';
        return new $class($connection);
    }

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    abstract public function fetchCurrentSchema() : string;

    public function fetchTableNames(string $schema = '') : array
    {
        if ($schema === '') {
            $schema = $this->fetchCurrentSchema();
        }

        $stm = '
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = :schema
            ORDER BY table_name
        ';

        return $this->connection->fetchColumn($stm, ['schema' => $schema]);
    }

    public function fetchColumns(string $table) : array
    {
        $pos = strpos($table, '.');
        if ($pos === false) {
            $schema = $this->fetchCurrentSchema();
        } else {
            $schema = substr($table, 0, $pos);
            $table = substr($table, $pos + 1);
        }

        $autoinc = $this->getAutoincSql();
        $stm = "
            SELECT
                columns.column_name as _name,
                columns.data_type as _type,
                COALESCE(
                    columns.character_maximum_length,
                    columns.numeric_precision
                ) AS _size,
                columns.numeric_scale AS _scale,
                CASE
                    WHEN columns.is_nullable = 'YES' THEN 0
                    ELSE 1
                END AS _notnull,
                columns.column_default AS _default,
                {$autoinc} AS _autoinc,
                CASE
                    WHEN table_constraints.constraint_type = 'PRIMARY KEY' THEN 1
                    ELSE 0
                END AS _primary
            FROM information_schema.columns
                LEFT JOIN information_schema.key_column_usage
                    ON columns.table_schema = key_column_usage.table_schema
                    AND columns.table_name = key_column_usage.table_name
                    AND columns.column_name = key_column_usage.column_name
                LEFT JOIN information_schema.table_constraints
                    ON key_column_usage.table_schema = table_constraints.table_schema
                    AND key_column_usage.table_name = table_constraints.table_name
                    AND key_column_usage.constraint_name = table_constraints.constraint_name
            WHERE columns.table_schema = :schema
            AND columns.table_name = :table
            ORDER BY columns.ordinal_position
        ";

        $columns = [];
        $defs = $this->connection->fetchAll($stm, ['schema' => $schema, 'table' => $table]);
        foreach ($defs as $def) {
            $columns[$def['_name']] = [
                'name' => $def['_name'],
                'type' => $def['_type'],
                'size' => isset($def['_size']) ? (int) $def['_size'] : null,
                'scale' => isset($def['_scale']) ? (int) $def['_scale'] : null,
                'notnull' => (bool) $def['_notnull'],
                'default' => $this->getDefault($def['_default']),
                'autoinc' => (bool) $def['_autoinc'],
                'primary' => (bool) $def['_primary']
            ];
        }

        return $columns;
    }

    public function fetchAutoincSequence(string $table) : ?string
    {
        return null;
    }

    abstract protected function getAutoincSql() : string;

    abstract protected function getDefault($default);
}