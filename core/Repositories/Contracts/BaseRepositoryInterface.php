<?php

namespace Core\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HigherOrderTapProxy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Foundation\Application;
use Exception;

interface BaseRepositoryInterface
{
    /**
     * Find a model by its primary key
     *
     * @param $id
     * @param array|string[] $columns
     * @return Builder|Builder[]|Collection|Model|mixed|null
     */
    public function find($id, array $columns = ['*']);

    /**
     * Find multiple models by their primary keys
     *
     * @param $ids
     * @param array|string[] $columns
     * @return Collection|mixed
     */
    public function findMany($ids, array $columns = ['*']);

    /**
     * Find data by field and value
     *
     * @param $field
     * @param $value
     * @param array|string[] $columns
     * @return Builder[]|Collection|mixed
     */
    public function findByField($field, $value, array $columns = ['*']);

    /**
     * Find a model by its primary key or throw an exception
     *
     * @param $id
     * @param array|string[] $columns
     * @return Builder|Builder[]|Collection|Model|mixed|null
     */
    public function findOrFail($id, array $columns = ['*']);

    /**
     * Find a model by its primary key or return fresh model instance
     *
     * @param $id
     * @param array|string[] $columns
     * @return Builder|Collection|Model|mixed|null
     */
    public function findOrNew($id, array $columns = ['*']);

    /**
     * Get the first record matching the attributes or instantiate it
     *
     * @param array $attributes
     * @param array $values
     * @return Builder|Model|mixed
     */
    public function firstOrNew(array $attributes, array $values = []);

    /**
     * Get the first record matching the attributes or create it
     *
     * @param array $attributes
     * @param array $values
     * @return Builder|Model|mixed
     */
    public function firstOrCreate(array $attributes, array $values = []);

    /**
     * Execute the query and get the first result or throw an exception
     *
     * @param array|string[] $columns
     * @return Builder|Model|mixed
     */
    public function firstOrFail(array $columns = ['*']);

    /**
     * Create or update a record matching the attributes, and fill it with values
     *
     * @param array $attributes
     * @param array $values
     * @return Builder|Model|mixed
     */
    public function updateOrCreate(array $attributes, array $values = []);

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return Builder|Model|mixed
     */
    public function create(array $attributes);

    /**
     * Save a new model and return the instance
     *
     * @param $attributes
     * @return Builder|Model|mixed
     */
    public function forceCreate($attributes);

    /**
     * Update an entity in repository by id
     *
     * @param $id
     * @param $attributes
     * @return false|HigherOrderTapProxy|mixed
     */
    public function update($id, $attributes);

    /**
     * force update
     *
     * @param $id
     * @param $attributes
     * @return mixed
     */
    public function forceUpdate($id, $attributes);

    /**
     * Delete an entity in repository by id
     *
     * @param $id
     * @return bool|mixed|null
     */
    public function delete($id);

    /**
     * Force a hard delete on a soft deleted model
     *
     * @param $id
     * @return bool|mixed|null
     */
    public function forceDelete($id);

    /**
     * Restore a soft-deleted model instance
     *
     * @param $id
     * @return mixed
     */
    public function restore($id);

    /**
     * Create a new model instance that is existing
     *
     * @param array $attributes
     * @param bool $exists
     * @return Model
     */
    public function newInstance(array $attributes = [], bool $exists = false): ?Model;

    /**
     * Execute the query as a "select" statement.
     *
     * @param array|string[] $columns
     * @return mixed
     */
    public function get(array $columns = ['*']);

    /**
     * Execute the query and get the first result
     *
     * @param array|string[] $columns
     * @return Builder|Model|mixed|object|null
     */
    public function first(array $columns = ['*']);

    /**
     * Chunk the results of the query
     *
     * @param $count
     * @param callable $callback
     * @return mixed
     */
    public function chunk($count, callable $callback);

    /**
     * Execute a callback over each item while chunking
     *
     * @param callable $callback
     * @param int $count
     * @return bool|mixed
     */
    public function each(callable $callback, int $count = 1000);

    /**
     * Paginate the given query
     *
     * @param null $perPage
     * @param array|string[] $columns
     * @param string $pageName
     * @param null $page
     * @return LengthAwarePaginator|mixed
     */
    public function paginate($perPage = null, array $columns = ['*'], string $pageName = 'page', $page = null);

    /**
     * Paginate the given query into a simple paginator
     *
     * @param null $perPage
     * @param array|string[] $columns
     * @param string $pageName
     * @param null $page
     * @return Paginator|mixed
     */
    public function simplePaginate($perPage = null, array $columns = ['*'], string $pageName = 'page', $page = null);

    /**
     * Retrieve the "count" result of the query
     *
     * @param string $columns
     * @return int|mixed
     */
    public function count(string $columns = '*');

    /**
     * @return Builder|mixed
     */
    public function getQuery();

    /**
     * @return Application|Model|mixed
     * @throws Exception
     */
    public function setModel();

    /**
     * get model
     *
     * @return Model
     */
    public function getModel(): ?Model;

    /**
     * Get a new query builder for the model's table
     *
     * @return Builder|Model
     */
    public function newQuery();

    /**
     * Trigger static method calls to the model
     *
     * @param $method
     * @param $arguments
     * @return false|mixed
     */
    public static function __callStatic($method, $arguments);

    /**
     * Trigger method calls to the model
     *
     * @param $method
     * @param $arguments
     * @return false|mixed
     */
    public function __call($method, $arguments);
}
