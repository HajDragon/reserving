<?php

namespace App\Enums;

enum ApiTokenAbility: string
{
    case ProductsRead = 'products.read';
    case ProductsWrite = 'products.write';

    public function label(): string
    {
        return match ($this) {
            self::ProductsRead => 'Read products',
            self::ProductsWrite => 'Manage products',
        };
    }

    public static function options(): array
    {
        return array_reduce(self::cases(), function (array $options, self $ability): array {
            $options[$ability->value] = $ability->label();

            return $options;
        }, []);
    }

    public static function values(): array
    {
        return array_map(fn (self $ability): string => $ability->value, self::cases());
    }

    public static function labelsFor(array $abilities): array
    {
        return collect($abilities)
            ->map(fn (string $ability): string => self::tryFrom($ability)?->label() ?? $ability)
            ->values()
            ->all();
    }
}
