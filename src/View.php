<?php
declare(strict_types=1);

namespace W5D;

/**
 * View — a Blade-inspired compiler with two-tier resolution.
 *
 * Resolution order (first match wins):
 *
 *   Layouts/pages:  views/{ACTIVE_TEMPLATE}/…  →  views/_shared/…
 *   Blocks:         views/{ACTIVE_TEMPLATE}/blocks/{type}.php
 *                     →  views/_blocks/{type}.php
 *
 * That second tier is what makes a page portable between templates: the page
 * JSON names blocks by *type*, never by file, so the same content renders in
 * every installed template. A template overrides only the blocks whose look it
 * changes; everything else falls through to the shared renderer.
 *
 * Compiled output is cached in storage/cache/views keyed by path + mtime, so a
 * template edit invalidates only the files that changed.
 */
final class View
{
    private string $root;
    private array $config;
    private string $template = 'ledger';
    private string $cacheDir;
    private bool $cacheEnabled = true;

    /** @var list<string> */
    private array $stack = [];

    public function __construct(string $root, array $config)
    {
        $this->root = $root;
        $this->config = $config;
        $this->template = (string) ($config['active_template'] ?? 'ledger');
        $this->cacheDir = (string) ($config['cache_dir'] ?? $root . '/storage/cache') . '/views';
    }

    public function setTemplate(string $template): void
    {
        if (in_array($template, (array) ($this->config['templates'] ?? []), true)) {
            $this->template = $template;
        }
    }

    public function template(): string
    {
        return $this->template;
    }

    public function setCache(bool $enabled): void
    {
        $this->cacheEnabled = $enabled;
    }

    // ── Resolution ─────────────────────────────────────────────────────────

    /**
     * Locate a view file.
     * @param string $name e.g. "layouts.app", "pages.about", "blocks.hero"
     */
    public function find(string $name, bool $block = false): ?string
    {
        $relative = str_replace('.', '/', $name) . '.php';
        $candidates = [];

        if ($block) {
            // A block override may live in views/{template}/blocks/.
            $leaf = basename($relative);
            $candidates[] = $this->root . '/views/' . $this->template . '/blocks/' . $leaf;
            $candidates[] = $this->root . '/views/_blocks/' . $leaf;
        } else {
            $candidates[] = $this->root . '/views/' . $this->template . '/' . $relative;
            $candidates[] = $this->root . '/views/_shared/' . $relative;
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    /** Which of the two tiers served a file — shown in the admin template screen. */
    public function tier(string $name, bool $block = false): string
    {
        $file = $this->find($name, $block);
        if ($file === null) {
            return 'missing';
        }
        return str_contains($file, '/views/_shared/') || str_contains($file, '/views/_blocks/') ? 'shared' : 'template';
    }

    // ── Rendering ──────────────────────────────────────────────────────────

    /**
     * Render a view to a string.
     * @param array<string,mixed> $data
     */
    public function render(string $name, array $data = [], bool $block = false): string
    {
        $file = $this->find($name, $block);
        if ($file === null) {
            throw new \RuntimeException("View not found: {$name} (template: {$this->template})");
        }
        return $this->evaluate($file, $data);
    }

    /** Render an arbitrary block type, falling through to the shared tier. */
    public function block(string $type, array $data = []): string
    {
        return $this->render('blocks.' . $type, $data, true);
    }

    public function exists(string $name, bool $block = false): bool
    {
        return $this->find($name, $block) !== null;
    }

    /**
     * Include another view from inside a template.
     * @param array<string,mixed> $data
     */
    public function include(string $name, array $data = []): string
    {
        return $this->render($name, $data);
    }

    /** Path stack, used only to keep error messages readable. */
    public function depth(): int
    {
        return count($this->stack);
    }

    private function evaluate(string $file, array $data): string
    {
        $this->stack[] = $file;
        try {
            $compiled = $this->compile($file);
            $render = static function (string $__compiled, array $__data, View $__view): string {
                extract($__data, EXTR_SKIP);
                $view = $__view;
                ob_start();
                try {
                    eval('?>' . $__compiled);
                } finally {
                    $out = (string) ob_get_clean();
                }
                return $out;
            };
            return $render($compiled, $data, $this);
        } finally {
            array_pop($this->stack);
        }
    }

    // ── Compilation ────────────────────────────────────────────────────────

    private function compile(string $file): string
    {
        $mtime = (int) filemtime($file);
        $cacheFile = $this->cacheDir . '/' . sha1($file) . '-' . $mtime . '.php';

        if ($this->cacheEnabled && is_file($cacheFile)) {
            $cached = file_get_contents($cacheFile);
            if ($cached !== false) {
                return $cached;
            }
        }

        $source = file_get_contents($file);
        if ($source === false) {
            throw new \RuntimeException("Cannot read view: {$file}");
        }

        $compiled = $this->compileString($source);

        if ($this->cacheEnabled) {
            if (!is_dir($this->cacheDir)) {
                @mkdir($this->cacheDir, 0o775, true);
            }
            @file_put_contents($cacheFile, $compiled);
        }

        return $compiled;
    }

    /** The directive compiler. Order matters: raw output first, then directives. */
    public function compileString(string $source): string
    {
        // A template file opens with declare(strict_types=1); that is illegal
        // inside eval(), so only the declare statement is removed — the opening
        // <?php tag is kept, otherwise the rest of the file would be emitted as
        // literal text instead of executing. The engine's own strict types stay
        // in force because src/*.php declares them.
        $source = (string) preg_replace(
            '/^\s*<\?php\s+declare\(strict_types=1\);?/',
            '<?php',
            $source
        );

        // 1. Raw echo {!! … !!} — must be handled before the escaped form.
        $source = (string) preg_replace(
            '/\{!!\s*(.+?)\s*!!\}/s',
            '<?php echo $1; ?>',
            $source
        );

        // 2. Escaped echo {{ … }}.
        $source = (string) preg_replace(
            '/\{\{\s*(.+?)\s*\}\}/s',
            '<?php echo \\W5D\\Support\\Text::e($1); ?>',
            $source
        );

        // 3. Comments {{-- … --}}.
        $source = (string) preg_replace('/\{\{--[\s\S]*?--\}\}/', '', $source);

        // 4. @extends('layouts.app', ['title' => 'x'])
        $source = (string) preg_replace_callback(
            '/@extends\(\s*([\'"])(.+?)\1\s*(?:,\s*(\[.*?\]))?\s*\)/',
            function (array $m): string {
                $data = isset($m[3]) && $m[3] !== '' ? $m[3] : '[]';
                return '<?php echo $view->render(' . var_export($m[2], true) . ', array_merge(get_defined_vars(), ' . $data . ')); ?>';
            },
            $source
        );

        // 5. @section('name') … @endsection / @show
        $source = (string) preg_replace(
            '/@section\(\s*([\'"])(.+?)\1\s*\)/',
            '<?php $__sec = $1$2$1; ob_start(); ?>',
            $source
        );
        $source = str_replace('@endsection', '<?php $__sections[$__sec] = ob_get_clean(); ?>', $source);
        $source = str_replace('@show', '<?php $__sections[$__sec] = ob_get_clean(); echo $__sections[$__sec]; ?>', $source);

        // 6. @yield('name', 'default')
        $source = (string) preg_replace_callback(
            '/@yield\(\s*([\'"])(.+?)\1\s*(?:,\s*(.+?))?\s*\)/',
            function (array $m): string {
                $default = isset($m[3]) && trim((string) $m[3]) !== '' ? $m[3] : "''";
                return '<?php echo $__sections[' . var_export($m[2], true) . '] ?? ' . $default . '; ?>';
            },
            $source
        );

        // 7. @include('partials.x', ['a'=>1])
        $source = (string) preg_replace_callback(
            '/@include\(\s*([\'"])(.+?)\1\s*(?:,\s*(\[.*?\]))?\s*\)/',
            function (array $m): string {
                $data = isset($m[3]) && $m[3] !== '' ? $m[3] : '[]';
                return '<?php echo $view->render(' . var_export($m[2], true) . ', array_merge(get_defined_vars(), ' . $data . ')); ?>';
            },
            $source
        );

        // 8. @block('type', ['block' => $block]) — two-tier block rendering.
        $source = (string) preg_replace_callback(
            '/@block\(\s*([\'"])(.+?)\1\s*(?:,\s*(\[.*?\]))?\s*\)/',
            function (array $m): string {
                $data = isset($m[3]) && $m[3] !== '' ? $m[3] : '[]';
                return '<?php echo $view->block(' . var_export($m[2], true) . ', array_merge(get_defined_vars(), ' . $data . ')); ?>';
            },
            $source
        );

        // 9. Control structures.
        $source = (string) preg_replace('/@php\b/', '<?php', $source);
        $source = (string) preg_replace('/@endphp\b/', '?>', $source);
        $source = (string) preg_replace('/@if\s*\((.*?)\)/s', '<?php if ($1): ?>', $source);
        $source = (string) preg_replace('/@elseif\s*\((.*?)\)/s', '<?php elseif ($1): ?>', $source);
        $source = (string) preg_replace('/@else\b/', '<?php else: ?>', $source);
        $source = (string) preg_replace('/@endif\b/', '<?php endif; ?>', $source);
        $source = (string) preg_replace('/@foreach\s*\((.*?)\)/s', '<?php foreach ($1): ?>', $source);
        $source = (string) preg_replace('/@endforeach\b/', '<?php endforeach; ?>', $source);
        $source = (string) preg_replace('/@for\s*\((.*?)\)/s', '<?php for ($1): ?>', $source);
        $source = (string) preg_replace('/@endfor\b/', '<?php endfor; ?>', $source);
        $source = (string) preg_replace('/@while\s*\((.*?)\)/s', '<?php while ($1): ?>', $source);
        $source = (string) preg_replace('/@endwhile\b/', '<?php endwhile; ?>', $source);
        $source = (string) preg_replace('/@unless\s*\((.*?)\)/s', '<?php if (!($1)): ?>', $source);
        $source = (string) preg_replace('/@endunless\b/', '<?php endif; ?>', $source);

        // 10. Helpers available as directives.
        $source = (string) preg_replace('/@asset\s*\((.*?)\)/', '<?php echo asset($1); ?>', $source);
        $source = (string) preg_replace('/@lang\s*\((.*?)\)/', '<?php echo t($1); ?>', $source);
        $source = (string) preg_replace('/@csrf\b/', '', $source); // static site: no forms post

        return $source;
    }

    /** Human-readable resolution table for the admin template screen. */
    public function resolutionReport(string $template): array
    {
        $rows = [];
        foreach (['layouts.app', 'partials.header', 'partials.footer', 'pages.index'] as $name) {
            $rows[] = [
                'name' => $name,
                'template' => is_file($this->root . '/views/' . $template . '/' . str_replace('.', '/', $name) . '.php'),
                'shared' => is_file($this->root . '/views/_shared/' . str_replace('.', '/', $name) . '.php'),
            ];
        }
        return $rows;
    }
}
