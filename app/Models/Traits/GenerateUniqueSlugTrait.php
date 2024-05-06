<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait GenerateUniqueSlugTrait
{

    public static function bootGenerateUniqueSlugTrait(): void
    {
        static::saving(function ($model) {
            $slug = $model->slug;
            $model->slug = $model->generateUniqueSlug($slug);;
        });
    }

    public function generateUniqueSlug($slug): string
    {
        $originalSlug = $slug;
        $slugNumber = null;

        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            $slugNumber = $matches[1];
            $slug = Str::replaceLast("-$slugNumber", '', $slug);
        }

        $existingSlugs = $this->getExistingSlugs($slug, $this->getTable());

        if (!in_array($slug, $existingSlugs)) {
            // Slug is unique, no need to append numbers
            return $slug . ($slugNumber ? "-$slugNumber" : '');
        }

        $i = $slugNumber ? ($slugNumber + 1) : 1;
        $uniqueSlugFound = false;

        while (!$uniqueSlugFound) {
            $newSlug = $slug . '-' . $i;

            if (!in_array($newSlug, $existingSlugs)) {
                // Unique slug found
                return $newSlug;
            }

            $i++;
        }

        return $originalSlug . '-' . mt_rand(1000, 9999);
    }

    private function getExistingSlugs(string $slug, string $table): array
    {
        return $this->where('slug', 'LIKE', $slug . '%')
            ->where('id', '!=', $this->id ?? null) // Exclude the current model's ID
            ->pluck('slug')
            ->toArray();
    }

}
