<?php

namespace App\Repositories\Concerns;

trait CustomQuery
{
    /**
     * @var array
     */
    protected $operators = array(
        'gt' => 'greaterThan', 'gteq' => 'greaterThanOrEqual', 'lt' => 'lessThan', 'lteq' => 'lessThanOrEqual',
        'eq' => 'equal', 'neq' => 'notEqual', 'in' => 'in', 'nin' => 'notIn', 'cons_f' => 'containsFirst', 'cons_l' => 'containsLast',
        'cons' => 'contains', 'lteqt' => 'lessThanOrEqualWithTime', 'gteqt' => 'greaterThanOrEqualWithTime', 'isnull' => 'isNull', 'notnull' => 'notNull'
    );

    /**
     * @var null
     */
    protected $builder = null;

    /**
     * @var null
     */
    protected $oldBuilder = null;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $queryParams = [];

    /**
     * @var string
     */
    protected $sortField;

    /**
     * @var string
     */
    protected $sortType = 'DESC';

    /**
     * @param null $table
     */
    public function init($table = null)
    {
        $this->queryParams = request()->all();
        $this->sortField = strpos($this->sortField, '.') === false ? ($this->table . '.id') : $this->sortField;
        $this->builder = $this;
        $this->oldBuilder = $this;
        $this->table = $table;
    }

    /**
     * @param array $query
     * @param array $columns
     * @return mixed
     */
    public function search($query = array(), $columns = [])
    {
        $this->resetBuilder();
        $this->queryParams = $query;
        if (empty($query)) {
            return $this->select($this->buildColumn($columns))->orderBy($this->sortField, $this->sortType);
        }
        // set sort
        isset($query['direction']) ? $this->sortType = $query['direction'] : null;
        isset($query['sort']) ? $this->sortField = $query['sort'] : null;
        // build sql
        foreach ($query as $key => $value) {
            if (is_array($value)) {
                $this->needWhereInOrNotIn($key, $value) ? $this->buildInOrNotInConditions($key, $value) : $this->buildConditions($key, $value);
                continue;
            }

            if (trim($value) !== '') {
                $this->buildConditions($key, $value);
            }
        }
        return $this->builder->select($this->buildColumn($columns))->orderBy($this->sortField, $this->sortType);
    }

    /**
     * @return mixed
     */
    protected function resetBuilder()
    {
        $this->builder = $this->oldBuilder;
    }

    /**
     * @param $fieldName
     * @param $value
     * @return bool
     */
    protected function needWhereInOrNotIn($fieldName, $value)
    {
        if (is_multi_array($value)) {
            return true;
        }
        return strpos($fieldName, '_in') !== false || strpos($fieldName, '_nin') !== false;
    }

    /**
     * @param $fieldName
     * @param $value
     * @return bool
     */
    protected function buildInOrNotInConditions($fieldName, $value)
    {
        $table = '';
        if (is_multi_array($value)) {
            $table = $fieldName;
            foreach ($value as $field => $v) {
                if (!$this->needWhereInOrNotIn($field, $v)) {
                    continue;
                }
                $this->mapCondition($field, $v, $table);
            }
            return true;
        }
        $this->mapCondition($fieldName, $value, $table);
    }

    /**
     * @param $fieldName
     * @param $value
     * @param string $table
     * @return bool
     */
    protected function buildConditions($fieldName, $value, $table = '')
    {
        if (!is_array($value) && trim($value) !== '') {
            return $this->mapCondition($fieldName, $value, $table);
        }
        if (empty($value)) {
            return false;
        }
        foreach ($value as $field => $val) {
            $this->buildConditions($field, $val, $fieldName);
        }
    }

    /**
     * @param $fieldName
     * @param $value
     * @param $table
     * @return bool
     */
    protected function mapCondition($fieldName, $value, $table)
    {
        $item = explode('_', $fieldName);
        if (count($item) < 2) {
            return false;
        }
        $operator = end($item);
        array_pop($item);
        $item = implode('_', $item);
        $field = $table ? $table . '.' . $item : $item;
        array_key_exists($operator, $this->operators) ? $this->{$this->operators[$operator]}($field, $value) : null;
        return true;
    }

    /**
     * @param $columns
     * @return array
     */
    protected function buildColumn($columns)
    {
        empty($columns) ? $columns = [$this->table . '.*'] : null;
        foreach ($columns as &$column) {
            $column = strpos($column, '.') === false ? $this->table . '.' . $column : $column;
        }
        return $columns;
    }

    /**
     * @param $field
     * @param $value
     */
    protected function equal($field, $value)
    {
        $this->builder = $this->builder->where($field, $value);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function notEqual($field, $value)
    {
        $this->builder = $this->builder->where($field, '!=', $value);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function greaterThan($field, $value)
    {
        $this->builder = $this->builder->where($field, '>', $value . '%');
    }

    /**
     * @param $field
     * @param $value
     */
    protected function greaterThanOrEqual($field, $value)
    {
        $this->builder = $this->builder->where($field, '>=', $value);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function greaterThanOrEqualWithTime($field, $value)
    {
        $value .= ' 00:00:00';
        $this->builder = $this->builder->where($field, '>=', $value);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function lessThan($field, $value)
    {
        $this->builder = $this->builder->where($field, '<', $value);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function lessThanOrEqual($field, $value)
    {
        $this->builder = $this->builder->where($field, '<=', $value);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function lessThanOrEqualWithTime($field, $value)
    {
        $value .= ' 23:59:59';
        $this->builder = $this->builder->where($field, '<=', $value);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function in($field, $value)
    {
        $this->builder = $this->builder->whereIn($field, (array)$value);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function notIn($field, $value)
    {
        $this->builder = $this->builder->whereNotIn($field, (array)$value);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function contains($field, $value)
    {
        $this->builder = $this->builder->where($field, 'LIKE', '%' . str_replace('%', '\%', $value) . '%');
    }

    /**
     * @param $field
     * @param $value
     */
    protected function containsFirst($field, $value)
    {
        $this->builder = $this->builder->where($field, 'LIKE', '%' . str_replace('%', '\%', $value));
    }

    /**
     * @param $field
     * @param $value
     */
    protected function containsLast($field, $value)
    {
        $this->builder = $this->builder->where($field, 'LIKE', str_replace('%', '\%', $value) . '%');
    }

    /**
     * @param $field
     * @param $value
     */
    protected function isNull($field, $value)
    {
        $this->builder = $this->builder->whereNull($field);
    }

    /**
     * @param $field
     * @param $value
     */
    protected function notNull($field, $value)
    {
        $this->builder = $this->builder->whereNotNull($field);
    }
}
