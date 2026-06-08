<?php

declare(strict_types=1);

namespace App\Data;

class ProfileSearchFilters
{
    public function __construct(
        public readonly ?int $cityId = null,
        public readonly ?int $categoryId = null,
        public readonly ?int $subcategoryId = null,
        public readonly ?string $providerType = null,
        public readonly bool $remote = false,
        public readonly ?string $keyword = null,
        public readonly int $perPage = 15,
        public readonly int $page = 1,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cityId: isset($data['city_id']) ? (int) $data['city_id'] : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            subcategoryId: isset($data['subcategory_id']) ? (int) $data['subcategory_id'] : null,
            providerType: isset($data['provider_type']) && $data['provider_type'] !== ''
                ? (string) $data['provider_type']
                : null,
            remote: filter_var($data['remote'] ?? false, FILTER_VALIDATE_BOOL),
            keyword: isset($data['keyword']) && strlen(trim($data['keyword'])) >= 2
                ? trim($data['keyword'])
                : null,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 15,
            page: isset($data['page']) ? (int) $data['page'] : 1,
        );
    }
}
