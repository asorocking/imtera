<?php

namespace App\Services;

use App\Models\YandexSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Carbon;

class YandexReviewsService
{
    /**
     * Extract organization ID from Yandex Maps URL.
     * Example: https://yandex.com/maps/org/slug/1010501395/reviews/ -> 1010501395
     */
    public function parseOrgIdFromUrl(string $url): ?string
    {
        if (preg_match('#(?:yandex\.(?:ru|com)/maps/org/[^/]+/(\d+))#u', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Normalize URL to reviews page (yandex.com).
     */
    public function normalizeUrl(string $url): string
    {
        $url = trim($url);
        $url = preg_replace('#^https?://yandex\.ru/#', 'https://yandex.com/', $url);
        if (!str_contains($url, '/reviews')) {
            $url = rtrim($url, '/') . '/reviews/';
        }
        return $url;
    }

    /**
     * Fetch page and parse rating + total count from HTML (meta or inline JSON).
     */
    public function fetchRatingAndCount(string $url): array
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8',
            ])
            ->get($url);

        if (!$response->successful()) {
            return ['rating' => null, 'total_reviews' => null, 'org_name' => null];
        }

        $html = $response->body();
        $rating = $this->extractRating($html);
        $totalReviews = $this->extractTotalReviews($html);
        $orgName = $this->extractOrgName($html);

        return [
            'rating' => $rating,
            'total_reviews' => $totalReviews,
            'org_name' => $orgName,
        ];
    }

    /**
     * Extract reviews from HTML. Yandex may embed in script or render in DOM.
     * Falls back to parsing visible structure (list items / cards).
     */
    public function fetchAndParseReviews(string $url): array
    {
        $response = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8',
            ])
            ->get($url);

        if (!$response->successful()) {
            return [];
        }

        $html = $response->body();

        return $this->extractReviewsFromHtml($html);
    }

    /**
     * Save or update Yandex setting and fetch + store reviews.
     */
    public function saveSettingAndFetchReviews(int $userId, string $yandexUrl): YandexSetting
    {
        $normalizedUrl = $this->normalizeUrl($yandexUrl);
        $orgId = $this->parseOrgIdFromUrl($normalizedUrl);

        if (!$orgId) {
            throw new \InvalidArgumentException('Некорректная ссылка на организацию в Яндекс.Картах.');
        }

        $meta = $this->fetchRatingAndCount($normalizedUrl);
        $reviewsData = $this->fetchAndParseReviews($normalizedUrl);

        $setting = YandexSetting::updateOrCreate(
            ['user_id' => $userId],
            [
                'yandex_url' => $normalizedUrl,
                'org_id' => $orgId,
                'org_name' => $meta['org_name'],
                'rating' => $meta['rating'],
                'total_reviews' => $meta['total_reviews'] ?? count($reviewsData),
            ]
        );

        $setting->reviews()->delete();

        foreach (array_values($reviewsData) as $index => $r) {
            $externalId = $r['external_id'] ?? ('rev-' . $setting->id . '-' . $index . '-' . substr(md5(Str::limit($r['text'], 200)), 0, 8));
            $setting->reviews()->create([
                'author_name' => $r['author_name'] ?? null,
                'author_phone' => $r['author_phone'] ?? null,
                'branch_name' => $r['branch_name'] ?? null,
                'review_date' => isset($r['review_date']) ? \Carbon\Carbon::parse($r['review_date']) : null,
                'text' => $r['text'],
                'rating' => $r['rating'] ?? null,
                'external_id' => $externalId,
            ]);
        }

        return $setting->fresh(['reviews']);
    }

    private function extractRating(string $html): ?float
    {
        $crawler = new Crawler($html);
        $rating = $crawler->filter('.business-summary-rating-badge-view__rating')->text();
        $rating = str_replace(',', '.', substr($rating, -3));
        if ($rating !== '') {
            return $rating;
        }

        return null;
    }

    private function extractTotalReviews(string $html): ?int
    {
        if (preg_match('/"reviewCount"\s*:\s*(\d+)/', $html, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/reviewCount["\']?\s*:\s*["\']?(\d+)/', $html, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(\d{1,5})\s+reviews?/ui', $html, $m)) {
            return (int) str_replace(' ', '', $m[1]);
        }
        if (preg_match('/(\d{1,5})\s+ratings?/ui', $html, $m)) {
            return (int) str_replace(' ', '', $m[1]);
        }
        if (preg_match('/Всего отзывов:\s*([\d\s]+)/u', $html, $m)) {
            return (int) preg_replace('/\s/', '', $m[1]);
        }
        return null;
    }

    private function extractOrgName(string $html): ?string
    {
        if (preg_match('/<title>([^<]+)<\/title>/ui', $html, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1])));
            return $title ?: null;
        }
        return null;
    }

    /**
     * Parse review-like blocks from HTML (author, date, text).
     */
    private function extractReviewsFromHtml(string $html): array
    {
        $html = preg_replace('/\s+/', ' ', $html);
        $crawler = new Crawler($html);

        return $crawler->filter('.business-review-view')->each(function (Crawler $node) {
            $author = $node->filter('span[itemprop="name"]')->count() > 0
                ? trim($node->filter('span[itemprop="name"]')->text())
                : 'Гость';

            $rawDate = $node->filter('meta[itemprop="datePublished"]')->count() > 0
                ? $node->filter('meta[itemprop="datePublished"]')->attr('content')
                : now()->toIso8601String();
            $formattedDate = Carbon::parse($rawDate)->format('d-m-Y H:i');

            $text = $node->filter('.spoiler-view__text-container')->count() > 0
                ? trim($node->filter('.spoiler-view__text-container')->text())
                : '';

            return [
                'author_name' => $author,
                'review_date'   => $formattedDate,
                'text'   => $text,
            ];
        });
    }
}
