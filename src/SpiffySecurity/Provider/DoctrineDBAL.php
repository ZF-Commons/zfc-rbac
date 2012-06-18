<?php

namespace SpiffySecurity\Provider;

use InvalidArgumentException;
use Doctrine\DBAL\Connection;

class DoctrineDBAL extends PDO
{
    public function getRoles()
    {
        $connection = isset($this->options['connection']) ? $this->options['connection'] : null;
        if (is_string($connection)) {
            $connection = $this->serviceLocator->get($connection);
        }

        if (!$connection instanceof Connection) {
            throw new InvalidArgumentException('No DBAL connection configured');
        }

        $this->options['pdo'] = $connection->getWrappedConnection();

        return parent::getRoles();
    }
}