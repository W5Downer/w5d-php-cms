<?php
namespace W5D;

/**
 * Turns a page document (content JSON or a generated document) into HTML.
 *
 * Document shape:
 *   id, path, title{}, description{}, kind, noindex?, blocks[]
 */
final class Page
{
    public static function render(array $doc, int $status = 200): array
    {
        Lang::$fellBack = false;
        $body = '';
        foreach ($doc['blocks'] ?? [] as $block) {
            $body .= View::block($block, $doc);
        }
        $lang = Lang::current();
        $title = loc($doc['title'] ?? ['en' => ''], $lang);
        $desc = loc($doc['description'] ?? ['en' => ''], $lang);
        $html = View::layout('site', [
            'doc' => $doc,
            'title' => $title,
            'description' => $desc,
            'body' => $body,
            'lang' => $lang,
            'dir' => Lang::isRtl($lang) ? 'rtl' : 'ltr',
            'template' => active_template(),
            'path' => $doc['path'] ?? '/',
            'fellBack' => Lang::$fellBack,
            'noindex' => !empty($doc['noindex']),
        ]);
        return self::response($html, $status, $doc['path'] ?? '/');
    }

    /** Wrap pre-rendered body HTML in the site layout. */
    public static function wrap(string $title, string $description, string $body, string $path, int $status = 200, bool $noindex = false, bool $private = false): array
    {
        Lang::$fellBack = false;
        $lang = Lang::current();
        $html = View::layout('site', [
            'doc' => ['id' => trim($path, '/'), 'path' => $path, 'blocks' => []],
            'title' => $title, 'description' => $description, 'body' => $body,
            'lang' => $lang, 'dir' => Lang::isRtl($lang) ? 'rtl' : 'ltr',
            'template' => active_template(), 'path' => $path,
            'fellBack' => false, 'noindex' => $noindex || $private,
        ]);
        return self::response($html, $status, $path, $private);
    }

    public static function response(string $html, int $status = 200, string $path = '/', bool $private = false): array
    {
        return [
            'status' => $status,
            'headers' => [
                'Content-Type' => 'text/html; charset=utf-8',
                'Cache-Control' => $private ? 'private, no-store' : 'public, max-age=300',
                'Vary' => 'Cookie, Accept-Language',
            ],
            'body' => $html,
        ];
    }

    /** Minimal page for a generated index/detail when no content JSON exists. */
    public static function fallbackDoc(string $path, string $titleEn): array
    {
        return ['id' => trim($path, '/'), 'path' => $path, 'title' => ['en' => $titleEn], 'blocks' => []];
    }
}
