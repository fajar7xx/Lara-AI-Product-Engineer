<?php

namespace App\Services\Generation;

use InvalidArgumentException;

class HtmlAssemblyService
{
    /**
     * @param  array{
     *     title?: string,
     *     tagline?: string,
     *     sections?: array<int, array{heading: string, body: string}>
     * }  $content
     */
    public function render(string $templateFamily, string $designSystem, array $content): string
    {
        $view = match ($templateFamily) {
            'landing' => 'components.generated-pages.landing',
            'app_shell' => 'components.generated-pages.app-shell',
            default => throw new InvalidArgumentException('Unsupported template family.'),
        };

        return (string) view($view, [
            'content' => $content,
            'tokens' => $this->tokens($designSystem),
        ])->render();
    }

    /**
     * @return array{page: string, card: string, button: string, badge: string}
     */
    public function tokens(string $designSystem): array
    {
        return match ($designSystem) {
            'minimal' => [
                'page' => 'bg-white text-zinc-900',
                'card' => 'rounded-2xl border border-zinc-200 bg-white shadow-sm',
                'button' => 'rounded-xl bg-zinc-900 px-4 py-2 text-white',
                'badge' => 'rounded-full bg-zinc-100 px-3 py-1 text-sm text-zinc-700',
            ],
            'modern' => [
                'page' => 'bg-slate-950 text-white',
                'card' => 'rounded-3xl border border-white/10 bg-white/5 shadow-xl',
                'button' => 'rounded-xl bg-cyan-400 px-4 py-2 font-medium text-slate-950',
                'badge' => 'rounded-full bg-cyan-400/15 px-3 py-1 text-sm text-cyan-200',
            ],
            'corporate' => [
                'page' => 'bg-slate-100 text-slate-900',
                'card' => 'rounded-2xl border border-slate-300 bg-white shadow-sm',
                'button' => 'rounded-lg bg-slate-800 px-4 py-2 text-white',
                'badge' => 'rounded-md bg-slate-200 px-3 py-1 text-sm text-slate-700',
            ],
            default => throw new InvalidArgumentException('Unsupported design system.'),
        };
    }
}
