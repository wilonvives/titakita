<?php

namespace TitaKita\Services\Application\Locale;

use TitaKita\Locale;
use Illuminate\Config\Repository;

class LocaleService
{
    public function __construct(
        private readonly Repository $config,
    )
    {
    }

    public function getLocaleOrDefault(?string $locale): string
    {
        $supportedLocales = Locale::getSupportedLocales();

        if (in_array($locale, $supportedLocales, true)) {
            return $locale;
        }

        $normalizedLocale = str_replace('_', '-', $locale);
        $baseLanguage = explode('-', $normalizedLocale)[0];

        foreach ($supportedLocales as $supportedLocale) {
            if (str_starts_with($supportedLocale, $baseLanguage)) {
                return $supportedLocale;
            }
        }

        return $this->config->get('app.locale');
    }
}
