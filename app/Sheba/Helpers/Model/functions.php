<?php

if (!function_exists('concatNameWithType')) {

    /**
     * @param $author
     * @return string
     */
    function concatNameWithType($author)
    {
        return class_basename($author) . " - " . ($author->profile != null ? $author->profile->name : $author->name);
    }
}

if (!function_exists('createAuthorWithType')) {
    /**
     * @param $author
     * @return array|mixed
     */
    function createAuthorWithType($author)
    {
        $data                    = createAuthor($author);
        $data['created_by_type'] = "App\Models\\" . class_basename($author);
        return $data;
    }
}

if (!function_exists('createAuthor')) {
    /**
     * @param $author
     * @return array
     */
    function createAuthor($author)
    {
        $data                    = [];
        $data['created_by']      = $author->id;
        $data['created_by_name'] = concatNameWithType($author);
        return $data;
    }
}

if (!function_exists('updateAuthor')) {
    /**
     * @param $model
     * @param $author
     * @return mixed
     */
    function updateAuthor($model, $author)
    {
        $model->updated_by      = $author->id;
        $model->updated_by_name = concatNameWithType($author);
        return $model;
    }
}

if (!function_exists('removeRelationsFromModel')) {
    /**
     * @param $model
     */
    function removeRelationsFromModel($model)
    {
        foreach ($model->getRelations() as $key => $relation) {
            array_forget($model, $key);
        }
    }
}

if (!function_exists('removeRelationsAndFields')) {
    /**
     * @param $model
     * @param array $columns_to_remove
     * @return mixed
     */
    function removeRelationsAndFields($model, array $columns_to_remove = [])
    {
        removeRelationsFromModel($model);
        $model = removeSelectedFieldsFromModel($model, $columns_to_remove);
        return $model;
    }
}

if (!function_exists('removeSelectedFieldsFromModel')) {
    /**
     * @param $model
     * @param array $columns_to_remove
     * @return mixed
     */
    function removeSelectedFieldsFromModel($model, array $columns_to_remove = [])
    {
        array_forget($model, 'created_by');
        array_forget($model, 'updated_by');
        array_forget($model, 'updated_at');
        array_forget($model, 'created_by_name');
        array_forget($model, 'updated_by_name');
        array_forget($model, 'remember_token');
        foreach ($columns_to_remove as $column) {
            array_forget($model, $column);
        }
        return $model;
    }
}

if (!function_exists('createAuthor')) {
    /**
     * @param $model
     * @param $author
     * @return mixed
     */
    function createAuthor($model, $author)
    {
        $model->created_by      = $author->id;
        $model->created_by_name = concatNameWithType($author);
        return $model;
    }
}

if (!function_exists('updateAuthor')) {
    /**
     * @param $model
     * @param $author
     * @return mixed
     */
    function updateAuthor($model, $author)
    {
        $model->updated_by      = $author->id;
        $model->updated_by_name = concatNameWithType($author);
        return $model;
    }
}
