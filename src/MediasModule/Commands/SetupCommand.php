<?php

/**
 * This file is part of the Piko user module
 *
 * @package Piko\MediasModule
 * @copyright 2026 Sylvain PHILIP.
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/medias-module
 */

declare(strict_types=1);

namespace Piko\MediasModule\Commands;

use PDO;

/**
 * SetupCommand Class
 *
 * Command used to install database tables
 *
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class SetupCommand
{
    /**
     * A PDO instance
     *
     * @var PDO
     */
    protected PDO $db;

    public function setPDO(PDO $db): void
    {
        $this->db = $db;
    }

    public function successMsg(string $message): string
    {
        $green = "\033[32m";
        $reset = "\033[0m";

        return $green . $message . $reset;
    }

    public function errorMsg(string $message): string
    {
        $red = "\033[31m";
        $reset = "\033[0m";

        return $red . $message . $reset;
    }

    public function install(array $options = []): int
    {
        echo "Starting installation of database tables...\n";

        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        echo "Detected database driver: {$driver}\n";

        $file = \realpath(__DIR__ . '/../../../sql/install-' . $driver . '.sql');

        if ($file === false || !is_readable($file)) {
            echo $this->errorMsg("Error: Could not find or read SQL installation file for driver '{$driver}'.\n");
            return 1;
        }

        echo "Using SQL file: {$file}\n";

        $sql = \file_get_contents($file);

        if ($this->db->exec($sql) === false) {
            $error = $this->db->errorInfo();
            echo $this->errorMsg("Failed to execute SQL script.\n");
            echo $this->errorMsg("Database error [{$error[0]}]: {$error[2]}\n");
            return 1;
        }

        echo $this->successMsg("Success! Tables have been created in the database using '{$driver}' driver.\n");

        return 0;
    }
}
