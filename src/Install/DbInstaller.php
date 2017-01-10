<?php
/**
 * Copyright (c) 2016-2017 Invertus, JSC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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
