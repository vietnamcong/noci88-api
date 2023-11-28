<?php

namespace Core\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;

trait BaseSoftDelete
{
    use SoftDeletes;

    public static $applyDeletedFlag = true;

    public static function bootSoftDeletes()
    {
        static::addGlobalScope(new BaseSoftDeletingScope());
    }

    /**
     * Initialize the soft deleting trait for an instance.
     *
     * @return void
     */
    public function initializeSoftDeletes()
    {
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());
        $time = $this->freshTimestamp();

        $columns = [];

        // deleted_flag
        if (!empty($this->getDeletedFlag())) {
            $flagOn = $this->getDeletedFlagValue(true);
            $this->{$this->getDeletedFlag()} = $flagOn;
            $columns[$this->getDeletedFlag()] = $flagOn;
        }

        // deleted_by
        if (!empty($this->getDeletedByColumn())) {
            $this->{$this->getDeletedByColumn()} = $this->getCurrentGuardUser();
            $columns[$this->getDeletedByColumn()] = $this->getCurrentGuardUser();
        }

        // deleted_at
        if (!empty($this->getDeletedAtColumn())) {
            $this->{$this->getDeletedAtColumn()} = $time;
            $columns[$this->getDeletedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);
        $this->syncOriginalAttributes(array_keys($columns));
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @return bool|null
     */
    public function restore(): ?bool
    {
        // If the restoring event does not return false, we will proceed with this
        // restore operation. Otherwise, we bail out so the developer will stop
        // the restore totally. We will clear the deleted timestamp and save.
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        if (!empty($this->getDeletedFlag())) {
            $this->{$this->getDeletedFlag()} = $this->getDeletedFlagValue();
        }

        if (!empty($this->getDeletedByColumn())) {
            $this->{$this->getDeletedByColumn()} = null;
        }

        if (!empty($this->getDeletedAtColumn())) {
            $this->{$this->getDeletedAtColumn()} = null;
        }

        // Once we have saved the model, we will fire the "restored" event so this
        // developer will do anything they need to after a restore operation is
        // totally finished. Then we will return the result of the save call.
        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored', false);

        return $result;
    }

    /**
     * Determine if the model instance has been soft-deleted.
     *
     * @return bool
     */
    public function trashed(): bool
    {
        if (!empty($this->getDeletedFlag())) {
            return $this->{$this->getDeletedFlag()} == $this->getDeletedFlagValue(true);
        }

        if (!empty($this->getDeletedAtColumn())) {
            return !is_null($this->{$this->getDeletedAtColumn()});
        }

        return false;
    }

    /**
     * Get the name of the "deleted" column.
     *
     * @return null|string
     */
    public function getDeletedFlag(): ?string
    {
        return getConfig('model_field.deleted.flag');
    }

    /**
     * Get the fully qualified "deleted at" column.
     *
     * @return null|string
     */
    public function getQualifiedDeletedFlag(): ?string
    {
        return $this->qualifyColumn($this->getDeletedFlag());
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
     * Model::$applyDeletedFlag = false | true
     * false: query not with deleted_flag
     * true: query with deleted_flag
     *
     * @return bool
     */
    public function getApplyDeletedFlag(): bool
    {
        return self::$applyDeletedFlag;
    }

    public function getDeletedAtColumn()
    {
        return getConfig('model_field.deleted.at', null);
    }
}
