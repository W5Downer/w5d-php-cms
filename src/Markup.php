<?php
namespace W5D;

/**
 * Inline markup allowed in content prose. Input is escaped first; only these
 * constructs are re-introduced: **bold**, *italic*, `code`, [label](target), [[https://url]].
 * Link targets: page:x, service:x, macro:x, project:x, layer:x, or / and https URLs.
 */
final class Markup
{
    public static function inline(string $text): string
    {
        $out = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $out = preg_replace('/`([^`]+)`/', '<code>$1</code>', $out) ?? $out;
        $out = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $out) ?? $out;
        $out = preg_replace('/(?<![\*\w])\*([^*\s][^*]*)\*(?!\w)/', '<em>$1</em>', $out) ?? $out;
        $out = preg_replace_callback('/\[\[(https?:\/\/[^\]\s]+)\]\]/', static function (array $m): string {
            $url = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            return '<a class="ext" href="' . htmlspecialchars($url, ENT_QUOTES) . '" rel="noopener noreferrer">' . htmlspecialchars($url, ENT_QUOTES) . '</a>';
        }, $out) ?? $out;
        $out = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', static function (array $m): string {
            $target = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
            $resolved = Router::link($target);
            if ($resolved === null) {
                return '<span class="pending-link" title="' . e(t('pending_link')) . '">' . $m[1] . '</span>';
            }
            return '<a href="' . htmlspecialchars($resolved, ENT_QUOTES) . '">' . $m[1] . '</a>';
        }, $out) ?? $out;
        return $out;
    }
}
