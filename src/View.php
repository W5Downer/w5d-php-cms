<?php
namespace W5D;

use RuntimeException;
use Throwable;

/**
 * View engine. Two-tier resolution (rule 2):
 *   views/templates/{ACTIVE}/blocks/{type}.php  ->  views/_blocks/{type}.php
 *
 * Every block is validated (B2): id, tier (E|I|X) and focus (1..5) are required.
 * The engine wraps each block in <section>, renders its title and its single
 * primary CTA, so individual block files only render their inner content.
 */
final class View
{
    /** @var string[] render trail for precise error messages */
    private static array $trail = [];

    public static function root(): string
    {
        return W5D_ROOT . '/views';
    }

    public static function resolveBlock(string $type, string $template): string
    {
        if (!in_array($type, Config::vocab('block'), true)) {
            throw new RuntimeException("Block type '{$type}' is not in config/vocab.php");
        }
        $tpl = self::root() . "/templates/{$template}/blocks/{$type}.php";
        if (is_file($tpl)) {
            return $tpl;
        }
        $base = self::root() . "/_blocks/{$type}.php";
        if (is_file($base)) {
            return $base;
        }
        throw new RuntimeException("No renderer for block type '{$type}'");
    }

    /** Validate block props (B2). Throws on a missing id, tier or focus. */
    public static function validate(array $block): array
    {
        $type = (string) ($block['type'] ?? '');
        $id = (string) ($block['id'] ?? '');
        $where = "{$type}#{$id}";
        if ($id === '') {
            throw new RuntimeException("Block {$type} has no id");
        }
        if (!in_array($block['tier'] ?? null, ['E', 'I', 'X'], true)) {
            throw new RuntimeException("Block {$where} has a missing or invalid tier");
        }
        if (!in_array((int) ($block['focus'] ?? 0), [1, 2, 3, 4, 5], true)) {
            throw new RuntimeException("Block {$where} has a missing or invalid focus");
        }
        if (isset($block['cta']) && !is_array($block['cta'])) {
            throw new RuntimeException("Block {$where} cta must be an object or null");
        }
        $block['focus'] = (int) $block['focus'];
        $block['gradient'] = $block['gradient'] ?? null;
        $block['cta'] = $block['cta'] ?? null;
        $block['data'] = $block['data'] ?? [];
        return $block;
    }

    public static function block(array $block, array $doc): string
    {
        $block = self::validate($block);
        $type = (string) $block['type'];
        $file = self::resolveBlock($type, active_template());
        self::$trail[] = $type . '#' . $block['id'];
        $lang = Lang::current();
        try {
            ob_start();
            (static function () use ($file, $block, $doc, $lang): void {
                include $file;
            })();
            $inner = (string) ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException('Render failed in ' . implode(' > ', self::$trail) . ': ' . $e->getMessage(), 0, $e);
        } finally {
            array_pop(self::$trail);
        }

        $grad = $block['gradient'] ? ' grad-' . e($block['gradient']) : '';
        $html = '<section id="' . e($block['id']) . '" class="blk blk-' . e($type) . ' tier-' . e($block['tier'])
            . ' focus-' . $block['focus'] . $grad . '">';
        if ($type !== 'hero' && !empty($block['title'])) {
            $html .= '<h2 class="blk-title">' . e(loc($block['title'], $lang)) . '</h2>';
        }
        $html .= $inner;
        if (is_array($block['cta'])) {
            $html .= self::cta($block['cta'], $lang);
        }
        return $html . '</section>';
    }

    /** One primary action per block. Every /app/register link carries its declared limit. */
    public static function cta(array $cta, string $lang): string
    {
        $href = (string) ($cta['href'] ?? '#');
        $class = in_array($cta['class'] ?? '', ['btn-peak', 'btn-focus', 'btn-ghost', 'btn-link'], true)
            ? (string) $cta['class'] : 'btn-ghost';
        $label = loc($cta['label'] ?? '', $lang);
        $out = '<p class="blk-actions"><a class="btn ' . $class . '" href="' . e(Router::link($href) ?? '#') . '">'
            . e($label) . '</a></p>';
        $limit = isset($cta['limit']) ? loc($cta['limit'], $lang) : '';
        if ($limit === '' && str_starts_with($href, '/app/register')) {
            $limit = t('cta_default_limit');
        }
        if ($limit !== '') {
            $out .= '<p class="cta-limit">' . e($limit) . ' <a href="/trust/statement/">' . e(t('read_limits')) . '</a></p>';
        }
        return $out;
    }

    /** Render a layout (templates first, then shared). */
    public static function layout(string $name, array $vars): string
    {
        $tpl = self::root() . '/templates/' . active_template() . "/layouts/{$name}.php";
        $file = is_file($tpl) ? $tpl : self::root() . "/_shared/layouts/{$name}.php";
        return self::capture($file, $vars);
    }

    public static function partial(string $name, array $vars = []): string
    {
        $tpl = self::root() . '/templates/' . active_template() . "/partials/{$name}.php";
        $file = is_file($tpl) ? $tpl : self::root() . "/_shared/partials/{$name}.php";
        return self::capture($file, $vars);
    }

    public static function page(string $name, array $vars = []): string
    {
        return self::capture(self::root() . "/_shared/pages/{$name}.php", $vars);
    }

    public static function capture(string $file, array $vars): string
    {
        if (!is_file($file)) {
            throw new RuntimeException("Missing view: {$file}");
        }
        extract($vars, EXTR_SKIP);
        ob_start();
        try {
            include $file;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
