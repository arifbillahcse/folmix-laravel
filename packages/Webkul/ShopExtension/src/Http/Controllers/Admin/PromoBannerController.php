<?php

namespace Webkul\ShopExtension\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class PromoBannerController extends Controller
{
    /**
     * Create a controller instance.
     */
    public function __construct(protected ThemeCustomizationRepository $themeCustomizationRepository) {}

    /**
     * Update the promo banner theme customization.
     *
     * Handles its own image uploads so the shared core theme update flow
     * (which only understands the image_carousel/services_content shapes)
     * never has to know about this type.
     */
    public function update(int $id): RedirectResponse|JsonResponse
    {
        $theme = $this->themeCustomizationRepository->find($id);

        request()->validate([
            'name' => 'required',
            'sort_order' => 'required|numeric',
            'channel_id' => 'required|in:'.implode(',', core()->getAllChannels()->pluck('id')->toArray()),
            'theme_code' => 'required',
            'locale' => 'required',
            'options.heading' => 'nullable|string',
            'options.text' => 'nullable|string',
            'options.button_text' => 'nullable|string',
            'options.button_link' => 'nullable|string',
            'options.cards' => 'nullable|array',
            'options.cards.*.title' => 'nullable|string',
            'options.cards.*.text' => 'nullable|string',
            'options.cards.*.button_text' => 'nullable|string',
            'options.cards.*.button_link' => 'nullable|string',
            'options.cards.*.image' => 'nullable|image|extensions:jpeg,jpg,png,svg,webp',
        ]);

        $locale = request()->input('locale');

        $options = request()->input('options', []);

        foreach ($options['cards'] ?? [] as $index => $card) {
            $file = request()->file("options.cards.{$index}.image");

            if ($file instanceof UploadedFile) {
                $card['image'] = $this->storeImage($file, $theme->id);
            } elseif (isset($card['existing_image'])) {
                $card['image'] = $card['existing_image'];
            } else {
                $card['image'] = null;
            }

            unset($card['existing_image']);

            $options['cards'][$index] = $card;
        }

        $options['cards'] = array_values($options['cards'] ?? []);

        $data = [
            'locale' => $locale,
            'type' => 'promo_banner',
            'name' => request()->input('name'),
            'sort_order' => request()->input('sort_order'),
            'channel_id' => request()->input('channel_id'),
            'theme_code' => request()->input('theme_code'),
            'status' => request()->input('status') == 'on',
            $locale => [
                'options' => $options,
            ],
        ];

        $this->themeCustomizationRepository->update($data, $id);

        session()->flash('success', trans('admin::app.settings.themes.update-success'));

        return redirect()->route('admin.settings.themes.edit', $id);
    }

    /**
     * Encode and store an uploaded promo card image, mirroring the core
     * theme customization image pipeline (webp, theme/{id}/ prefix).
     */
    protected function storeImage(UploadedFile $file, int $themeId): string
    {
        $path = 'theme/'.$themeId.'/'.Str::random(40).'.webp';

        $encoded = image_manager()->read($file)->encodeByExtension('webp');

        Storage::put($path, (string) $encoded);

        return 'storage/'.$path;
    }
}
