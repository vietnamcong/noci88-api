<?php

namespace Core\Models\Relations;

use Illuminate\Database\Eloquent\Builder;
use Core\Models\Concerns\InteractsWithPivotTable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method self withoutTrashedPivots() Show only non-trashed records
 * @method self withTrashedPivots() Show all records
 * @method self onlyTrashedPivots() Show only trashed records
 * @method int forceDetach(\Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array  $ids, bool  $touch) Force detach records
 * @method int syncWithForceDetaching(mixed  $ids) Sync many-to-many relationship with force detaching
 */
class BelongsToManySoft extends BelongsToMany
{
    use InteractsWithPivotTable;

    /**
     * Indicates if soft deletes are available on the pivot table
     *
     * @var bool
     */
    public $withSoftDeletes = false;

    /**
     * The custom pivot table column for the deleted_at timestamp
     * @var string
     */
    protected $pivotDeletedAt;

    /**
     * Get the name of the "deleted at" column.
     *
     * @return string
     */
    public function deletedAt()
    {
        return $this->pivotDeletedAt;
    }

    public function deletedFlag()
    {
        return getConfig('model_field.deleted.flag');
    }

    /**
     * Get the fully qualified deleted at column name.
     *
     * @return string
     */
    public function getQualifiedDeletedAtColumnName()
    {
        return $this->getQualifiedColumnName($this->deletedAt());
    }

    /**
     * @return string
     */
    public function getQualifiedDeletedFlagColumnName()
    {
        return $this->getQualifiedColumnName(getConfig('model_field.deleted.flag'));
    }

    /**
     * @param false $isDeleted
     * @return null|string
     */
    public function getDeletedFlagValue(bool $isDeleted = false): ?string
    {
        return $isDeleted ? getConfig('deleted_flag.on') : getConfig('deleted_flag.off');
    }

    /**
     * Get the fully qualified column name.
     *
     * @param string $column
     * @return string
     */
    public function getQualifiedColumnName($column)
    {
        return $this->table . '.' . $column;
    }

    public function withSoftDeletes($deletedAt = 'deleted_at')
    {
        $this->withSoftDeletes = true;

        $this->pivotDeletedAt = $deletedAt;

        $this->macro('withoutTrashedPivots', function () {
            $this->query->withGlobalScope('withoutTrashedPivots', function (Builder $query) {
                // $query->whereNull($this->getQualifiedDeletedAtColumnName());
                $query->where($this->getQualifiedDeletedFlagColumnName(), '=', $this->getDeletedFlagValue());
            })->withoutGlobalScopes(['onlyTrashedPivots']);

            return $this;
        });

        $this->macro('withTrashedPivots', function () {
            $this->query->withoutGlobalScopes(['withoutTrashedPivots', 'onlyTrashedPivots']);
            return $this;
        });

        $this->macro('onlyTrashedPivots', function () {
            $this->query->withGlobalScope('onlyTrashedPivots', function (Builder $query) {
                // $query->whereNotNull($this->getQualifiedDeletedAtColumnName());
                $query->where($this->getQualifiedDeletedFlagColumnName(), '=', $this->getDeletedFlagValue(true));
            })->withoutGlobalScopes(['withoutTrashedPivots']);

            return $this;
        });

        $this->macro('forceDetach', function ($ids = null, $touch = true) {
            $this->withSoftDeletes = false;

            return tap($this->detach($ids, $touch), function () {
                $this->withSoftDeletes = true;
            });
        });

        $this->macro('syncWithForceDetaching', function ($ids) {
            $this->withSoftDeletes = false;

            return tap($this->sync($ids), function () {
                $this->withSoftDeletes = true;
            });
        });

        return $this->withPivot($this->deletedAt())->withoutTrashedPivots();
    }

    /**
     * Set the join clause for the relation query.
     *
     * @param \Illuminate\Database\Eloquent\Builder|null $query
     * @return $this
     */
    protected function performJoin($query = null)
    {
        $query = $query ?: $this->query;

        // We need to join to the intermediate table on the related model's primary
        // key column with the intermediate table's foreign key for the related
        // model instance. Then we can set the "where" for the parent models.
        $baseTable = $this->related->getTable();

        $key = $baseTable . '.' . $this->relatedKey;

        $query->join($this->table, $key, '=', $this->getQualifiedRelatedPivotKeyName());

        $query->when($this->withSoftDeletes, function (Builder $query) {
            // $query->whereNull($this->getQualifiedDeletedAtColumnName());
            $query->where($this->getQualifiedDeletedFlagColumnName(), '=', $this->getDeletedFlagValue());
        });

        return $this;
    }
}
