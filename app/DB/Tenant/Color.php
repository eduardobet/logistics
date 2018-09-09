<?php

namespace Logistics\DB\Tenant;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    public function allTranslations()
    {
        return $this->hasMany(ColorTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(ColorTranslation::class)->where('lang', localization()->getCurrentLocale());
    }

    public function translations($language = null)
    {
        if ($language == null) {
            $language = localization()->getCurrentLocale();
        }
        return $this->allTranslations()->where('lang', '=', $language);
    }

    public function scopeWithAndWhereHas($query, $relation, $constraint)
    {
        return $query->whereHas($relation, $constraint)
            ->with([$relation => $constraint]);
    }

    /**
     * Gets zone for input dropdown list.
     *
     * @param mixed $tenantId
     * @return \Illuminate\Support\Collection
     */
    public function getColorAsList()
    {
        $lang = localization()->getCurrentLocale();

        $key = "colors.list.{$lang}";

        $colors = cache()->get($key, function () use ($key, $lang) {
            $colors = $this->with('translation')->get();

            cache()->forever($key, $colors);

            return $colors;
        });

        return $colors;
    }
}
