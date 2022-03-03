<?php


namespace App\Casts;


use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Image implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     */
    public function get($model, $key, $value, $attributes)
    {
        if ($value) {
            return Storage::url($value);
        }
        else return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if (File::isFile($value)) {
            return Storage::put($model::IMAGE_PATH ?? $model->getTable(), $value);
        }
        return null;
    }
}
