<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YandexReviewsController extends Controller
{
    public function index(Request $request): Response
    {
        $setting = $request->user()->yandexSetting;

        if (!$setting) {
            return Inertia::render('Yandex/Reviews', [
                'reviews' => ['data' => [], 'links' => [], 'prev_page_url' => null, 'next_page_url' => null, 'current_page' => 1, 'last_page' => 1],
                'rating' => null,
                'totalReviews' => 0,
                'orgName' => null,
                'hasSetting' => false,
                'sort' => 'newest',
            ]);
        }

        $sort = in_array($request->query('sort'), ['newest', 'oldest'], true)
            ? $request->query('sort')
            : 'newest';
        $query = $setting->reviews()->orderBy('review_date', $sort === 'oldest' ? 'asc' : 'desc');

        $reviews = $query->paginate(10)->withQueryString();

        return Inertia::render('Yandex/Reviews', [
            'reviews' => $reviews,
            'rating' => $setting->rating,
            'totalReviews' => $setting->total_reviews ?? $setting->reviews()->count(),
            'orgName' => $setting->org_name,
            'hasSetting' => true,
            'sort' => $sort,
        ]);
    }
}
