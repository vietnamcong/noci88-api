<?php

namespace Core\Repositories;

use Core\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HigherOrderTapProxy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Foundation\Application;
use Exception;

class BaseRepository implements BaseRepositoryInterface
{
    /** @var Model $model */
    protected $model;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->setModel();
    }

    /**
     * Find a model by its primary key
     *
     * @param $id
     * @param array|string[] $columns
     * @return Builder|Builder[]|Collection|Model|mixed|null
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->newQuery()->find($id, $columns);
    }

    /**
     * Find multiple models by their primary keys
     *
     * @param $ids
     * @param array|string[] $columns
     * @return Collection|mixed
     */
    public function findMany($ids, array $columns = ['*'])
    {
        return $this->newQuery()->findMany($ids, $columns);
    }

    /**
     * Find data by field and value
     *
     * @param $field
     * @param $value
     * @param array|string[] $columns
     * @return Builder[]|Collection|mixed
     */
    public function findByField($field, $value, array $columns = ['*'])
    {
        return $this->newQuery()->where($field, $value)->get($columns);
    }

    /**
     * Find a model by its primary key or throw an exception
     *
     * @param $id
     * @param array|string[] $columns
     * @return Builder|Builder[]|Collection|Model|mixed|null
     */
    public function findOrFail($id, array $columns = ['*'])
    {
        return $this->newQuery()->findOrFail($id, $columns);
    }

    /**
     * Find a model by its primary key or return fresh model instance
     *
     * @param $id
     * @param array|string[] $columns
     * @return Builder|Collection|Model|mixed|null
     */
    public function findOrNew($id, array $columns = ['*'])
    {
        return $this->newQuery()->findOrNew($id, $columns);
    }

    /**
     * Get the first record matching the attributes or instantiate it
     *
     * @param array $attributes
     * @param array $values
     * @return Builder|Model|mixed
     */
    public function firstOrNew(array $attributes, array $values = [])
    {
        return $this->newQuery()->firstOrNew($attributes, $values);
    }

    /**
     * Get the first record matching the attributes or create it
     *
     * @param array $attributes
     * @param array $values
     * @return Builder|Model|mixed
     */
    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->newQuery()->firstOrCreate($attributes, $values);
    }

    /**
     * Execute the query and get the first result or throw an exception
     *
     * @param array|string[] $columns
     * @return Builder|Model|mixed
     */
    public function firstOrFail(array $columns = ['*'])
    {
        return $this->newQuery()->firstOrFail($columns);
    }

    /**
     * Create or update a record matching the attributes, and fill it with values
     *
     * @param array $attributes
     * @param array $values
     * @return Builder|Model|mixed
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->newQuery()->updateOrCreate($attributes, $values);
    }

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return Builder|Model|mixed
     */
    public function create(array $attributes)
    {
        return $this->newQuery()->create($attributes);
    }

    /**
     * Save a new model and return the instance
     *
     * @param $attributes
     * @return Builder|Model|mixed
     */
    public function forceCreate($attributes)
    {
        return $this->newQuery()->forceCreate($attributes);
    }

    /**
     * Update an entity in repository by id
     *
     * @param $id
     * @param $attributes
     * @return false|HigherOrderTapProxy|mixed
     */
    public function update($id, $attributes)
    {
        $record = $this->find($id);

        if (empty($record)) {
            return false;
        }

        return tap($record, function ($instance) use ($attributes) {
            $instance->fill($attributes)->save();
        });
    }

    /**
     * force update
     *
     * @param $id
     * @param $attributes
     * @return false|HigherOrderTapProxy|mixed
     */
    public function forceUpdate($id, $attributes)
    {
        $record = $this->find($id);

        if (empty($record)) {
            return false;
        }

        return tap($record, function ($instance) use ($attributes) {
            $instance->forceFill($attributes)->save();
        });
    }

    /**
     * Delete an entity in repository by id
     *
     * @param $id
     * @return bool|mixed|null
     */
    public function delete($id)
    {
        $result = $this->find($id);

        if (empty($result)) {
            return false;
        }

        return $result->delete();
    }

    /**
     * Force a hard delete on a soft deleted model
     *
     * @param $id
     * @return bool|mixed|null
     */
    public function forceDelete($id)
    {
        $result = $this->find($id);

        if (empty($result)) {
            return false;
        }

        return $result->forceDelete();
    }

    /**
     * Restore a soft-deleted model instance
     *
     * @param $id
     * @return mixed
     */
    public function restore($id)
    {
        return $this->newQuery()->restore($id);
    }

    /**
     * Create a new model instance that is existing
     *
     * @param array $attributes
     * @param bool $exists
     * @return Model
     */
    public function newInstance(array $attributes = [], bool $exists = false): ?Model
    {
        return $this->getModel()->newInstance($attributes, $exists);
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param array|string[] $columns
     * @return mixed
     */
    public function get(array $columns = ['*'])
    {
        return $this->newQuery()->get($columns);
    }

    /**
     * Execute the query and get the first result
     *
     * @param array|string[] $columns
     * @return Builder|Model|mixed|object|null
     */
    public function first(array $columns = ['*'])
    {
        return $this->newQuery()->first($columns);
    }

    /**
     * Chunk the results of the query
     *
     * @param $count
     * @param callable $callback
     * @return bool|mixed
     */
    public function chunk($count, callable $callback)
    {
        return $this->newQuery()->chunk($count, $callback);
    }

    /**
     * Execute a callback over each item while chunking
     *
     * @param callable $callback
     * @param int $count
     * @return bool|mixed
     */
    public function each(callable $callback, int $count = 1000)
    {
        return $this->newQuery()->each($callback, $count);
    }

    /**
     * Paginate the given query
     *
     * @param null $perPage
     * @param array|string[] $columns
     * @param string $pageName
     * @param null $page
     * @return LengthAwarePaginator|mixed
     */
    public function paginate($perPage = null, array $columns = ['*'], string $pageName = 'page', $page = null)
    {
        return $this->newQuery()->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator
     *
     * @param null $perPage
     * @param array|string[] $columns
     * @param string $pageName
     * @param null $page
     * @return Paginator|mixed
     */
    public function simplePaginate($perPage = null, array $columns = ['*'], string $pageName = 'page', $page = null)
    {
        return $this->newQuery()->simplePaginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Retrieve the "count" result of the query
     *
     * @param string $columns
     * @return int|mixed
     */
    public function count(string $columns = '*')
    {
        return $this->newQuery()->count($columns);
    }

    /**
     * @param array $criteria
     * @return Builder|mixed
     */
    public function getQuery(array $criteria = [])
    {
        return $this->newQuery()->getQuery();
    }

    /**
     * Get a new query builder for the model's table
     *
     * @return Builder|Model
     */
    public function newQuery()
    {
        return $this->model instanceof Model
            ? $this->model->newQuery() :
            clone $this->model;
    }

    /**
     * @return Application|Model|mixed
     * @throws Exception
     */
    public function setModel()
    {
        if (empty($this->model)) {
            throw new Exception("Model is not defined.");
        }

        $model = app($this->model);

        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        $this->model = $model;

        return $this->model;
    }

    /**
     * get model
     *
     * @return Model
     */
    public function getModel(): ?Model
    {
        return $this->model instanceof Model
            ? clone $this->model
            : $this->model->getModel();
    }

    /**
     * Trigger static method calls to the model
     *
     * @param $method
     * @param $arguments
     * @return false|mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }

    /**
     * Trigger method calls to the model
     *
     * @param $method
     * @param $arguments
     * @return false|mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->model, $method], $arguments);
    }
}
