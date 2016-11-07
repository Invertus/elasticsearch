<?php

namespace Invertus\Brad\Install;

use Db;
use Exception;

/**
 * Class DbInstaller
 *
 * @package Invertus\Brad\Install
 */
class DbInstaller
{
    /**
     * @var Db
     */
    private $db;

    /**
     * @var string
     */
    private $modulePath;

    /**
     * DbInstaller constructor.
     *
     * @param Db $db
     * @param string $modulePath
     */
    public function __construct(Db $db, $modulePath)
    {
        $this->db = $db;
        $this->modulePath = $modulePath;
    }

    /**
     * Install database
     *
     * @return bool
     */
    public function install()
    {
        $installSqlFiles = glob($this->modulePath.'sql/install/*.sql');

        if (empty($installSqlFiles)) {
            return true;
        }

        foreach ($installSqlFiles as $sqlFile) {
            $sqlStatements = $this->getSqlStatements($sqlFile);

            if (!$this->execute($sqlStatements)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall database
     *
     * @return bool
     */
    public function uninstall()
    {
        $uninstallSqlFileName = $this->modulePath.'sql/uninstall/uninstall.sql';
        $sqlStatements = $this->getSqlStatements($uninstallSqlFileName);

        return (bool) $this->execute($sqlStatements);
    }

    /**
     * Execute SQL statements
     *
     * @param $sqlStatements
     *
     * @return bool
     *
     * @throws Exception
     */
    private function execute($sqlStatements)
    {
        try {
            $result = $this->db->execute($sqlStatements);
        } catch (Exception $e) {
            throw new Exception('Invalid SQL statements.');
        }

        return (bool) $result;
    }

    /**
     * Format and get sql statements from file
     *
     * @param string $fileName
     *
     * @return string
     */
    private function getSqlStatements($fileName)
    {
        $sqlStatements = file_get_contents($fileName);
        $sqlStatements = str_replace('PREFIX_', _DB_PREFIX_, $sqlStatements);
        $sqlStatements = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sqlStatements);

        return $sqlStatements;
    }
}
