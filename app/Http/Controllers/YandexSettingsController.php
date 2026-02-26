<?php

namespace App\Http\Controllers;

use App\Services\YandexReviewsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YandexSettingsController extends Controller
{
    public function __construct(
        private YandexReviewsService $yandexService
    ) {}

    public function index(Request $request): Response
    {
        $setting = $request->user()->yandexSetting;

        return Inertia::render('Yandex/Settings', [
            'yandexUrl' => $setting?->yandex_url ?? 'https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'yandex_url' => ['required', 'string', 'url', 'max:2048'],
        ]);

        try {
            $this->yandexService->saveSettingAndFetchReviews(
                $request->user()->id,
                $request->input('yandex_url')
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['yandex_url' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withErrors(['yandex_url' => 'Не удалось загрузить отзывы. Проверьте ссылку и попробуйте позже.']);
        }

        return redirect()->route('reviews.index')->with('success', 'Ссылка сохранена, отзывы загружены.');
    }
}
