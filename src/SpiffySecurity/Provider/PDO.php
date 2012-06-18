<?php

namespace SpiffySecurity\Provider;

use InvalidArgumentException;
use PDO as PhpPdo;

class PDO extends AbstractProvider
{
    public function getRoles()
    {
        $pdo = isset($this->options['pdo']) ? $this->options['pdo'] : null;
        if (is_string($pdo)) {
            $pdo = $this->serviceLocator->get($pdo);
        }

        if (!$pdo instanceof PhpPdo) {
            throw new InvalidArgumentException('No PDO configured');
        }

        $table      = $this->options['table'];
        $roleId     = $this->options['role_id_column'];
        $roleName   = $this->options['role_name_column'];
        $parentJoin = $this->options['parent_join_column'];

        $stmt = $pdo->prepare(<<<SQL
            SELECT role.{$roleName}   AS role,
                   parent.{$roleName} AS parent

            FROM   {$table} AS role
                   LEFT JOIN {$table} AS parent ON role.{$parentJoin} = parent.id;
SQL
        );

        $stmt->execute();

        $roles = array();
        while($row = $stmt->fetch(PhpPdo::FETCH_ASSOC)) {
            $roles[$row['role']][] = $row['parent'];
        }

        return $roles;
    }
}