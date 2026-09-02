<?php

namespace Webkul\Theme\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Eloquent\Repository;
use Webkul\Theme\Contracts\LoginSlider;

class LoginSliderRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return LoginSlider::class;
    }

    /**
     * Create.
     *
     * @return mixed
     */
    public function create(array $attributes)
    {
        Event::dispatch('theme.login_slider.create.before');

        $slider = parent::create($attributes);

        $this->uploadImage($attributes, $slider);

        Event::dispatch('theme.login_slider.create.after', $slider);

        return $slider;
    }

    /**
     * Update.
     *
     * @param  int  $id
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        Event::dispatch('theme.login_slider.update.before', $id);

        $slider = parent::update($attributes, $id);

        $this->uploadImage($attributes, $slider);

        Event::dispatch('theme.login_slider.update.after', $slider);

        return $slider;
    }

    /**
     * Delete.
     *
     * @param  int  $id
     * @return void
     */
    public function delete($id)
    {
        Event::dispatch('theme.login_slider.delete.before', $id);

        $slider = parent::find($id);

        Storage::delete((string) $slider->image);

        parent::delete($id);

        Event::dispatch('theme.login_slider.delete.after', $id);
    }

    /**
     * Upload image.
     *
     * @param  array  $data
     * @param  \Webkul\Theme\Models\LoginSlider  $slider
     * @return void
     */
    public function uploadImage($data, $slider)
    {
        if (! isset($data['image'])) {
            return;
        }

        foreach ($data['image'] as $image) {
            if ($image instanceof UploadedFile) {
                Storage::delete((string) $slider->image);

                $slider->image = $image->store('login-sliders');

                $slider->save();
            }
        }
    }
}
