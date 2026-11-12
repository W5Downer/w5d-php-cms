<?php
declare(strict_types=1);

/**
 * Template helpers.
 *
 * These are the only functions a view author needs. Every one of them is
 * language-aware: the active language comes from the registry, so a template is
 * written once and rendered three times.
 */

use W5D\Content;
use W5D\Support\Text as TextHelper;
use W5D\Registry;
use W5D\Route;
use W5D\Support\Text;

if (!function_exists('e')) {
    /** Escape for HTML text and attribute contexts. */
    function e(mixed $value): string
    {
        return TextHelper::e($value);
    }
}

if (!function_exists('lang')) {
    function lang(): string
    {
        return Registry::code();
    }
}

if (!function_exists('dir')) {
    function dir(?string $lang = null): string
    {
        return Registry::lang()->direction($lang);
    }
}

if (!function_exists('t')) {
    /** UI string from lang/{code}.php with {name} interpolation. */
    function t(string $key, array $replace = [], ?string $lang = null): string
    {
        return Registry::lang()->t($key, $replace, $lang);
    }
}

if (!function_exists('tr')) {
    /** Localise a page-JSON value: string or {lang: value} map. */
    function tr(mixed $value, ?string $lang = null): string
    {
        return Content::tr($value, $lang ?? Registry::code(), (string) Registry::config('default_lang'));
    }
}

if (!function_exists('has_tr')) {
    function has_tr(mixed $value, ?string $lang = null): bool
    {
        return Content::hasLang($value, $lang ?? Registry::code());
    }
}

if (!function_exists('v')) {
    /** Formatted figure from config/variables.php, e.g. v('counts.services'). */
    function v(string $path): string
    {
        return Registry::vars()->format($path);
    }
}

if (!function_exists('vraw')) {
    function vraw(string $path): mixed
    {
        return Registry::vars()->value($path);
    }
}

if (!function_exists('vlabel')) {
    function vlabel(string $path): string
    {
        return Registry::vars()->label($path);
    }
}

if (!function_exists('vnote')) {
    function vnote(string $path): ?string
    {
        return Registry::vars()->note($path);
    }
}

if (!function_exists('vars_group')) {
    function vars_group(string $group): array
    {
        return Registry::vars()->flatten($group);
    }
}

if (!function_exists('type_options')) {
    function type_options(string $type): array
    {
        return Registry::vars()->options($type);
    }
}

if (!function_exists('type_label')) {
    function type_label(string $type, ?string $key): string
    {
        $key = (string) $key;
        $options = Registry::vars()->options($type);
        return (string) ($options[$key] ?? TextHelper::humanise($key));
    }
}

if (!function_exists('href')) {
    /** Language-aware link to a page / macro / service / project. */
    function href(string $kind, string $id, ?string $lang = null): string
    {
        return Route::href($kind, $id, $lang ?? Registry::code());
    }
}

if (!function_exists('asset')) {
    /** Static asset from the active template's folder. */
    function asset(string $file): string
    {
        return Route::asset($file);
    }
}

if (!function_exists('url')) {
    /** Language root, e.g. "/" or "/fr/". */
    function url(string $path = '/'): string
    {
        $config = Registry::config();
        return Route::compose($path, Registry::code(), (string) $config['default_lang'], (string) $config['base_url']);
    }
}

if (!function_exists('absolute')) {
    function absolute(string $path): string
    {
        return Route::absolute($path);
    }
}

if (!function_exists('fmt_date')) {
    /** Localised date from an ISO string; free text passes through. */
    function fmt_date(string $value, bool $short = false): string
    {
        return \W5D\Support\Formatter::date($value, Registry::code(), $short);
    }
}

if (!function_exists('paras')) {
    /**
     * Normalise a prose body into renderable items.
     * Accepts: a string, a list of strings, or a list of typed items
     * ({type:"p"|"h3"|"ul"|"ol"|"dl"|"quote"|"table"|"note"|"callout", …}).
     */
    function paras(mixed $body): array
    {
        if (is_string($body)) {
            $body = [$body];
        }
        if (!is_array($body)) {
            return [];
        }
        $out = [];
        foreach ($body as $item) {
            if (is_string($item)) {
                $out[] = ['type' => 'p', 'text' => $item];
            } elseif (is_array($item)) {
                $out[] = $item + ['type' => 'p'];
            }
        }
        return $out;
    }
}

if (!function_exists('status_class')) {
    /** Stable CSS hook for a status word: status--building, etc. */
    function status_class(?string $status): string
    {
        return 'status--' . TextHelper::slug((string) $status);
    }
}

if (!function_exists('stage_percent')) {
    /** Percentage on the nine-step ladder for a status word. */
    function stage_percent(?string $status): int
    {
        $stage = Registry::vars()->statusStage((string) $status);
        return $stage === 0 ? 0 : (int) round($stage / 9 * 100);
    }
}

if (!function_exists('render_block')) {
    /**
     * Render one prepared block with the two-tier block resolver.
     * Used by every layout so templates iterate the same way.
     *
     * @param array<string,mixed> $block ['type','id','title','data']
     * @param array<string,mixed> $doc
     */
    function render_block(array $block, array $doc, string $lang): string
    {
        return Registry::view()->block((string) $block['type'], [
            'block' => $block,
            'doc' => $doc,
            'lang' => $lang,
        ]);
    }
}

if (!function_exists('block_has')) {
    /** True when the current template overrides a block type. */
    function block_has(string $type): bool
    {
        return Registry::view()->exists('blocks.' . $type, true);
    }
}

if (!function_exists('is_open')) {
    /** True when a value is the shared "to be confirmed" placeholder. */
    function is_open(mixed $value): bool
    {
        $phrase = (string) (Registry::vars()->value('open_value') ?? 'to be confirmed');
        $text = trim(mb_strtolower(tr($value)));
        return $text === '' || $text === mb_strtolower($phrase);
    }
}
