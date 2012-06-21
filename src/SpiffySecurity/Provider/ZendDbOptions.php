<?php

namespace SpiffySecurity\Provider;

use Zend\Stdlib\Options;

class ZendDbOptions extends Options
{
    /**
     * The name of the table the roles are stored in.
     *
     * @var string
     */
    protected $table;

    /**
     * The name column of the role table.
     *
     * @var string
     */
    protected $nameColumn;

    public function setNameColumn($nameColumn)
    {
        $this->nameColumn = $nameColumn;
        return $this;
    }

    public function getNameColumn()
    {
        return $this->nameColumn;
    }

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }
}