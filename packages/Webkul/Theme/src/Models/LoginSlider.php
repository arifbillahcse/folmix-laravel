<?php

namespace Webkul\Theme\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Webkul\Theme\Contracts\LoginSlider as LoginSliderContract;

class LoginSlider extends Model implements LoginSliderContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'link',
        'sort_order',
        'status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['image_url'];

    /**
     * Get the full image url of the slider.
     *
     * @return string|null
     */
    public function getImageUrlAttribute()
    {
        if (empty($this->image)) {
            return null;
        }

        return Storage::url($this->image);
    }
}
