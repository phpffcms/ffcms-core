<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Serialize;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Str;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use Illuminate\Support\Collection;

/**
 * Class ActiveModel. Basic implementation of laravel active records model with predefined settings
 * @package Ffcms\Core\Arch
 * @method static ActiveModel where($field = null, $compare = null, $value = null)
 * @method static ActiveModel whereIn($field = null, array $values = [])
 * @method static ActiveModel orWhere($field = null, $compare = null, $value = null)
 * @method static ActiveModel whereNull($field)
 * @method static ActiveModel whereBetween($field = null, array $values = [])
 * @method static ActiveModel whereNotIn($field = null, array $values = [])
 * @method static ActiveModel orderBy($field, $sortType)
 * @method static ActiveModel search($query = null, $trashhold = 4)
 * @method ActiveModel groupBy($field)
 * @method static self|null first()
 * @method static self|null last()
 * @method static self|null firstWhere($field = null, $compare = null, $value = null)
 * @method static self|null find($id)
 * @method static self|null findOrNew($id)
 * @method static ActiveModel|null whereNotNull($field)
 * @method static ActiveModel|null orWhereNotNull($field)
 * @method ActiveModel skip($count)
 * @method ActiveModel take($count)
 * @method static ActiveModel pluck($column, $key = null)
 * @method ActiveModel whereYear($field = null, $compare = null, $value = null)
 * @method ActiveModel whereMonth($field = null, $compare = null, $value = null)
 * @method ActiveModel whereDay($field = null, $compare = null, $value = null)
 * @method static ActiveModel select($columns = null)
 * @method \Illuminate\Database\Eloquent\Collection get($columns = ['*'])
 * @method ActiveModel each(callable $items)
 * @method self|null forPage($pageNumber, $itemCount)
 * @method bool contains($field, $value)
 * @method bool isEmpty
 * @method bool isNotEmpty
 * @method ActiveModel map(callable &$item)
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
    public function getLocaled(string $attribute)
    {
        // if always decoded
        if (Any::isArray($this->{$attribute})) {
            return $this->{$attribute}[App::$Request->getLanguage()];
        }

        if (!Any::isStr($attribute) || Str::likeEmpty($this->{$attribute})) {
            return null;
        }

        return Serialize::getDecodeLocale($this->{$attribute});
    }
}
