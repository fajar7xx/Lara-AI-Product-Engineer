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
     * @return array{
     *     page: string,
     *     hero: string,
     *     card: string,
     *     button: string,
     *     button_secondary: string,
     *     badge: string,
     *     feature_icon: string,
     *     footer: string,
     * }
     */
    public function tokens(string $designSystem): array
    {
        return match ($designSystem) {
            'minimal' => [
                'page' => 'bg-white text-zinc-900 font-sans',
                'hero' => 'bg-zinc-50 border-b border-zinc-100',
                'card' => 'rounded-2xl border border-zinc-200 bg-white shadow-sm hover:shadow-md transition-shadow',
                'button' => 'rounded-xl bg-zinc-900 px-6 py-3 text-sm font-semibold text-white hover:bg-zinc-800 transition-colors',
                'button_secondary' => 'rounded-xl border border-zinc-300 px-6 py-3 text-sm font-semibold text-zinc-700 hover:bg-zinc-100 transition-colors',
                'badge' => 'rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600',
                'feature_icon' => 'flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600',
                'footer' => 'bg-zinc-50 border-t border-zinc-100',
            ],
            'modern' => [
                'page' => 'bg-slate-950 text-white font-sans',
                'hero' => 'bg-gradient-to-br from-slate-900 via-slate-950 to-cyan-950',
                'card' => 'rounded-3xl border border-white/10 bg-white/5 backdrop-blur-sm shadow-2xl hover:bg-white/10 transition-all',
                'button' => 'rounded-2xl bg-gradient-to-r from-cyan-400 to-blue-500 px-6 py-3 text-sm font-semibold text-slate-950 hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/25',
                'button_secondary' => 'rounded-2xl border border-white/20 px-6 py-3 text-sm font-semibold text-white/80 hover:bg-white/10 transition-colors',
                'badge' => 'rounded-full bg-cyan-400/15 px-3 py-1 text-xs font-medium text-cyan-300',
                'feature_icon' => 'flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/15 text-cyan-400',
                'footer' => 'bg-slate-900/50 border-t border-white/5',
            ],
            'corporate' => [
                'page' => 'bg-slate-50 text-slate-900 font-sans',
                'hero' => 'bg-white border-b border-slate-200',
                'card' => 'rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow',
                'button' => 'rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm',
                'button_secondary' => 'rounded-xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors',
                'badge' => 'rounded-md bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700',
                'feature_icon' => 'flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600',
                'footer' => 'bg-slate-900 text-white',
            ],
            default => throw new InvalidArgumentException('Unsupported design system.'),
        };
    }
}
