<?php

namespace Core\Database\Schema;

use Illuminate\Database\Schema\Blueprint;

class CustomBlueprint extends Blueprint
{
    /**
     * @param string $column
     * @param int $precision
     * @return \Illuminate\Database\Schema\ColumnDefinition|null
     */
    public function softDeletes($column = '', $precision = 0)
    {
        $column = !empty($column) ? $column : getConfig('model_field.deleted.flag');

        if (empty($column)) {
            return null;
        }

        return $this->char($column, '1')->default(0)->comment(getConfig('model_field_name.deleted_flag'));
    }

    /**
     * @param int $precision
     */
    public function timestamps($precision = 0)
    {
        $createdBy = getConfig('model_field.created.by');
        $createdAt = getConfig('model_field.created.at');
        $updatedBy = getConfig('model_field.updated.by');
        $updatedAt = getConfig('model_field.updated.at');
        $deletedBy = getConfig('model_field.deleted.by');
        $deletedAt = getConfig('model_field.deleted.at');

        if (!empty($createdBy)) $this->integer($createdBy)->unsigned()->nullable()->comment(getConfig('model_field_name.created_by'));
        if (!empty($createdAt)) $this->timestamp($createdAt)->nullable()->comment(getConfig('model_field_name.created_at'));
        if (!empty($updatedBy)) $this->integer($updatedBy)->unsigned()->nullable()->comment(getConfig('model_field_name.updated_by'));
        if (!empty($updatedAt)) $this->timestamp($updatedAt)->nullable()->comment(getConfig('model_field_name.updated_at'));
        if (!empty($deletedBy)) $this->integer($deletedBy)->unsigned()->nullable()->comment(getConfig('model_field_name.deleted_by'));
        if (!empty($deletedAt)) $this->timestamp($deletedAt)->nullable()->comment(getConfig('model_field_name.deleted_at'));
    }
}
