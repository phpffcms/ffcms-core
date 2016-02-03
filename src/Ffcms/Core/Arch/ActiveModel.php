<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use \Illuminate\Database\Eloquent\Model as LaravelModel;

/**
 * Class ActiveModel. Basic implementation of laravel active records model with predefined settings
 * @package Ffcms\Core\Arch
 * @method static ActiveModel where($field = null, $compare = null, $value = null)
 * @method static ActiveModel orWhere($field = null, $compare = null, $value = null)
 * @method static ActiveModel orderBy($field, $sortType)
 * @method static ActiveModel|null first()
 * @method static ActiveModel|null find($id)
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
        if (!Obj::isString($attribute) || Str::likeEmpty($this->$attribute)) {
            return null;
        }

        return Serialize::getDecodeLocale($this->$attribute);
    }
}