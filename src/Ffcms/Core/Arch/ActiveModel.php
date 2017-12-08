<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Illuminate\Database\Eloquent\Model as LaravelModel;

/**
 * Class ActiveModel. Basic implementation of laravel active records model with predefined settings
 * @package Ffcms\Core\Arch
 * @method static ActiveModel where($field = null, $compare = null, $value = null)
 * @method static ActiveModel whereIn($field = null, array $values = [])
 * @method static ActiveModel orWhere($field = null, $compare = null, $value = null)
 * @method static ActiveModel orderBy($field, $sortType)
 * @method ActiveModel groupBy($field)
 * @method static ActiveModel|null first()
 * @method static ActiveModel|null find($id)
 * @method static ActiveModel|null whereNotNull($field)
 * @method static ActiveModel|null orWhereNotNull($field)
 * @method ActiveModel skip($count)
 * @method ActiveModel take($count)
 * @method static ActiveModel pluck($column, $key = null)
 * @method ActiveModel whereYear($field = null, $compare = null, $value = null)
 * @method ActiveModel whereMonth($field = null, $compare = null, $value = null)
 * @method ActiveModel whereDay($field = null, $compare = null, $value = null)
 * @method static ActiveModel select($columns = null)
 * @method int count()
 * @inheritdoc
 */
class ActiveModel extends LaravelModel
{
    /**
     * Special function for locale stored attributes under serialization.
     * @param string $attribute
     * @return array|null|string
     */
    public function getLocaled($attribute)
    {
        // if always decoded
        if (Obj::isArray($this->{$attribute})) {
            return $this->{$attribute}[App::$Request->getLanguage()];
        }

        if (!Obj::isString($attribute) || Str::likeEmpty($this->{$attribute})) {
            return null;
        }

        return Serialize::getDecodeLocale($this->{$attribute});
    }

    /**
     * Set model attribute. Extend laravel attribute casting mutators by serialized array
     * @param string $key
     * @param mixed $value
     * @return LaravelModel
     */
    public function setAttribute($key, $value)
    {
        if ($value !== null && $this->isSerializeCastable($key)) {
            $value = $this->asSerialize($value);
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Cast model attribute. Extend laravel attribute casting mutators by serialized array
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if ($value === null) {
            return $value;
        }

        if ($this->getCastType($key) === 'serialize') {
            return $this->fromSerialize($value);
        }

        return parent::castAttribute($key, $value);
    }

    /**
     * Check if key is castable to be serialized
     * @param string $key
     * @return bool
     */
    public function isSerializeCastable($key)
    {
        return $this->hasCast($key, 'serialize');
    }

    /**
     * Serialize value
     * @param $value
     * @return Serialize
     */
    public function asSerialize($value)
    {
        return serialize($value);
    }

    /**
     * Unserialize value
     * @param $value
     * @return mixed
     */
    public function fromSerialize($value)
    {
        return unserialize($value);
    }
}