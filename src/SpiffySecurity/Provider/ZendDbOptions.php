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
    protected $table = 'role';

    /**
     * The id column of the role table.
     *
     * @var string
     */
    protected $idColumn = 'id';

    /**
     * The name column of the role table.
     *
     * @var string
     */
    protected $nameColumn = 'name';

    /**
     * The join column to the parent role. If left empty, then
     * no join is performed. Note: this is a limited implementation and
     * assumes that the join is done on the same table. If this
     * does not work for you create a new provider and use that instead.
     *
     * @var string
     */
    protected $joinColumn = 'parent_role_id';

    public function setIdColumn($idColumn)
    {
        $this->idColumn = $idColumn;
        return $this;
    }

    public function getIdColumn()
    {
        return $this->idColumn;
    }

    public function setJoinColumn($joinColumn)
    {
        $this->joinColumn = $joinColumn;
        return $this;
    }

    public function getJoinColumn()
    {
        return $this->joinColumn;
    }

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