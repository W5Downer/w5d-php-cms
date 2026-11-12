<?php
declare(strict_types=1);

namespace W5D\Support;

/**
 * Markup — the small, deliberately limited inline syntax allowed in page JSON.
 *
 * Page files stay readable without becoming a template language. Supported:
 *
 *   **bold**            → <strong>
 *   *italic*            → <em>
 *   `code`              → <code>
 *   [label](target)     → <a href>. The target is resolved first as an internal
 *                         route ("page:about", "service:heritage-digitisation",
 *                         "macro:cultures", "project:kernid",
 *                         "declaration:fifth-dimension") and otherwise used
 *                         verbatim, so external links need no special syntax.
 *   [[https://…]]       → bare external link with the URL as its label
 *
 * Everything is escaped before markup is applied, so a content file can never
 * inject HTML. There is no raw-HTML path in JSON by design; the only trusted
 * HTML is the `rich` block, which is marked `"trusted": true` and reviewed.
 */
final class Markup
{
    /** @var array<string,string> Human labels for pending links. */
    private const KIND_LABELS = [
        'page' => 'page',
        'macro' => 'macro work',
        'service' => 'service',
        'project' => 'project',
        'declaration' => 'declaration',
    ];

    public static function inline(?string $text, string $lang): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        $escaped = Text::e($text);

        // Links first: their labels may contain emphasis.
        $escaped = (string) preg_replace_callback(
            '/\[([^\]\[]+)\]\(([^)\s]+)\)/u',
            static function (array $m) use ($lang): string {
                [$label, $target] = [$m[1], html_entity_decode($m[2], ENT_QUOTES, 'UTF-8')];
                return self::link($label, $target, $lang);
            },
            $escaped
        );

        $escaped = (string) preg_replace_callback(
            '/\[\[([^\]]+)\]\]/u',
            static function (array $m): string {
                $url = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
                $safe = Text::e($url);
                return '<a class="link link--external" href="' . $safe . '" rel="noopener noreferrer">' . $safe . '</a>';
            },
            $escaped
        );

        $escaped = (string) preg_replace('/\*\*([^\*\n]+)\*\*/u', '<strong>$1</strong>', $escaped);
        $escaped = (string) preg_replace('/(?<![\*\w])\*([^\*\n]+)\*(?![\*\w])/u', '<em>$1</em>', $escaped);
        $escaped = (string) preg_replace('/`([^`\n]+)`/u', '<code>$1</code>', $escaped);

        return $escaped;
    }

    /** Resolve one link target into an anchor, honouring the "pending" state. */
    public static function link(?string $label, ?string $target, string $lang): string
    {
        $label = (string) $label;
        $target = (string) $target;

        if (str_contains($target, ':')) {
            [$kind, $id] = explode(':', $target, 2);
            if (isset(self::KIND_LABELS[$kind])) {
                $route = \W5D\Registry::route();
                $path = $route->path($kind . ':' . $id);
                if ($path !== null) {
                    $config = \W5D\Registry::config();
                    $href = \W5D\Route::compose($path, $lang, (string) $config['default_lang'], (string) $config['base_url']);
                    return '<a class="link" href="' . Text::e($href) . '">' . $label . '</a>';
                }
                // Link to a record that does not exist yet: visible as pending,
                // never a silent 404.
                return '<span class="link link--pending" title="Not published yet">' . $label . '</span>';
            }
        }

        if (preg_match('#^(https?:|mailto:|tel:)#i', $target) === 1) {
            $rel = str_starts_with($target, 'http') ? ' rel="noopener noreferrer"' : '';
            return '<a class="link link--external" href="' . Text::e($target) . '"' . $rel . '>' . $label . '</a>';
        }

        if (str_starts_with($target, '#')) {
            return '<a class="link" href="' . Text::e($target) . '">' . $label . '</a>';
        }

        // A raw absolute path: prefix the language folder.
        $config = \W5D\Registry::config();
        $href = \W5D\Route::compose($target, $lang, (string) $config['default_lang'], (string) $config['base_url']);
        return '<a class="link" href="' . Text::e($href) . '">' . $label . '</a>';
    }

    /**
     * Render a prose body: a string (split on blank lines) or a list of typed
     * items. Returns safe HTML.
     *
     * Item types: p | h2 | h3 | h4 | ul | ol | dl | quote | note | callout |
     *             table | figure | code
     */
    public static function body(mixed $body, string $lang): string
    {
        if (is_string($body)) {
            $body = self::split($body);
        }
        if (!is_array($body)) {
            return '';
        }

        $html = '';
        foreach ($body as $item) {
            if (is_string($item)) {
                $html .= '<p>' . self::inline($item, $lang) . '</p>';
                continue;
            }
            if (!is_array($item)) {
                continue;
            }

            $type = (string) ($item['type'] ?? 'p');

            switch ($type) {
                case 'h2':
                case 'h3':
                case 'h4':
                    $id = (string) ($item['id'] ?? Text::anchor(Content_or_string($item['text'] ?? '')));
                    $html .= "<{$type} id=\"" . Text::e($id) . '">' . self::inline((string) ($item['text'] ?? ''), $lang) . "</{$type}>";
                    break;

                case 'ul':
                case 'ol':
                    $html .= "<{$type} class=\"list\">";
                    foreach ((array) ($item['items'] ?? []) as $li) {
                        $html .= '<li>' . self::inline((string) $li, $lang) . '</li>';
                    }
                    $html .= "</{$type}>";
                    break;

                case 'dl':
                    $html .= '<dl class="deflist">';
                    foreach ((array) ($item['items'] ?? []) as $term => $definition) {
                        $label = is_array($definition) ? (string) ($definition['term'] ?? $term) : (string) $term;
                        $value = is_array($definition) ? (string) ($definition['value'] ?? '') : (string) $definition;
                        $html .= '<dt>' . self::inline($label, $lang) . '</dt><dd>' . self::inline($value, $lang) . '</dd>';
                    }
                    $html .= '</dl>';
                    break;

                case 'quote':
                    $html .= '<blockquote class="quote"><p>' . self::inline((string) ($item['text'] ?? ''), $lang) . '</p>';
                    if (!empty($item['cite'])) {
                        $html .= '<footer>' . self::inline((string) $item['cite'], $lang) . '</footer>';
                    }
                    $html .= '</blockquote>';
                    break;

                case 'note':
                case 'callout':
                    $tone = preg_class((string) ($item['tone'] ?? 'note'));
                    $html .= '<aside class="notice notice--' . $tone . '">';
                    if (!empty($item['title'])) {
                        $html .= '<p class="notice__title">' . self::inline((string) $item['title'], $lang) . '</p>';
                    }
                    $html .= '<div class="notice__body">' . self::body($item['body'] ?? [], $lang) . '</div></aside>';
                    break;

                case 'table':
                    $html .= self::table($item, $lang);
                    break;

                case 'figure':
                    $html .= '<figure class="figure">';
                    if (!empty($item['value'])) {
                        $html .= '<p class="figure__value">' . Text::e((string) $item['value']) . '</p>';
                    }
                    if (!empty($item['caption'])) {
                        $html .= '<figcaption>' . self::inline((string) $item['caption'], $lang) . '</figcaption>';
                    }
                    $html .= '</figure>';
                    break;

                case 'code':
                    $html .= '<pre class="code"><code>' . Text::e((string) ($item['text'] ?? '')) . '</code></pre>';
                    break;

                default:
                    $html .= '<p>' . self::inline((string) ($item['text'] ?? ''), $lang) . '</p>';
            }
        }

        return $html;
    }

    public static function table(array $item, string $lang): string
    {
        $head = (array) ($item['head'] ?? []);
        $rows = (array) ($item['rows'] ?? []);
        $caption = isset($item['caption']) ? (string) $item['caption'] : '';
        $class = 'table' . (!empty($item['compact']) ? ' table--compact' : '');

        $html = '<div class="tablewrap"><table class="' . $class . '">';
        if ($caption !== '') {
            $html .= '<caption>' . self::inline($caption, $lang) . '</caption>';
        }
        if ($head !== []) {
            $html .= '<thead><tr>';
            foreach ($head as $cell) {
                $html .= '<th scope="col">' . self::inline((string) $cell, $lang) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ((array) $row as $cell) {
                if (is_array($cell)) {
                    $html .= '<td>' . self::cell($cell, $lang) . '</td>';
                } else {
                    $html .= '<td>' . self::inline((string) $cell, $lang) . '</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        return $html;
    }

    /** A structured table cell: {text, chip:"building", href:"page:about"}. */
    private static function cell(array $cell, string $lang): string
    {
        $text = (string) ($cell['text'] ?? $cell['value'] ?? '');
        $out = '';
        if (!empty($cell['chip'])) {
            $chip = (string) $cell['chip'];
            $out .= '<span class="chip ' . status_class($chip) . '">' . Text::e(type_label('status', $chip)) . '</span>';
        } elseif (!empty($cell['badge'])) {
            $out .= '<span class="badge">' . Text::e((string) $cell['badge']) . '</span>';
        }
        if ($text !== '') {
            if (!empty($cell['href'])) {
                $out .= self::link(self::inline($text, $lang), (string) $cell['href'], $lang);
            } else {
                $out .= self::inline($text, $lang);
            }
        }
        if (!empty($cell['open'])) {
            $out .= ' <span class="openchip">' . Text::e((string) v('open_value')) . '</span>';
        }
        return $out;
    }

    /** Split a prose string into paragraphs on blank lines. */
    public static function split(string $text): array
    {
        $parts = preg_split('/\R{2,}/u', trim($text)) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $out[] = ['type' => 'p', 'text' => $part];
            }
        }
        return $out;
    }
}

/** Local helper so the heading-id line stays readable. */
function Content_or_string(mixed $value): string
{
    return is_scalar($value) ? (string) $value : '';
}

/** Sanitise a tone name to a CSS-safe token. */
function preg_class(string $value): string
{
    $safe = preg_replace('/[^a-z0-9-]/', '', strtolower($value));
    return $safe === '' || $safe === null ? 'note' : $safe;
}
