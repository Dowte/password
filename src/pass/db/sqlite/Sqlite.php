<?php

namespace Dowte\Password\pass\db\sqlite;


use Dowte\Password\pass\exceptions\BaseException;
use Dowte\Password\pass\Password;

class Sqlite extends \SQLite3
{
    /**
     * @var \SQLite3
     */
    private static $db;

    public function __construct()
    {
    }

    public static function getFromFile($tableName)
    {
        return '.' . $tableName . '.db';
    }

    public static function getDbResource($dbDir, $from)
    {
        return rtrim($dbDir, '/') . '/' . self::getFromFile($from);
    }

    public static function getDb()
    {
        if (self::$db === null) {
            $resource = self::getDbResource(Connection::$config['DB_DIR'], Connection::$config['DB_NAME']);
            if (! file_exists($resource)) {
                file_put_contents($resource, '');
                chmod($resource, 0600);
            }
            self::$db = new \SQLite3($resource, SQLITE3_OPEN_READWRITE, isset(Connection::$config['DB_KEY']) ? Connection::$config['DB_KEY'] : null);
        }
        return self::$db;
    }
}