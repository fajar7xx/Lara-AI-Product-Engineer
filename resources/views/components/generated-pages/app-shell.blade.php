<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $content['title'] ?? 'Generated App Shell' }}</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body class="{{ $tokens['page'] }}">
        <div class="flex min-h-screen">

            {{-- Sidebar --}}
            <aside class="hidden w-64 shrink-0 flex-col border-r p-6 lg:flex"
                   style="border-color: var(--tw-border-opacity, 0.1)">
                <div class="flex items-center gap-3">
                    <div class="{{ $tokens['feature_icon'] }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold tracking-tight">{{ $content['title'] ?? 'App' }}</h1>
                        <p class="text-xs opacity-60">{{ $content['tagline'] ?? 'Dashboard' }}</p>
                    </div>
                </div>

                <nav class="mt-8 flex-1 space-y-1">
                    <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium bg-current/5 opacity-100">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium opacity-50 hover:opacity-100 transition-opacity">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                        </svg>
                        Projects
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium opacity-50 hover:opacity-100 transition-opacity">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Settings
                    </a>
                </nav>

                <div class="mt-auto pt-4 border-t" style="border-color: var(--tw-border-opacity, 0.1)">
                    <span class="{{ $tokens['badge'] }}">v1.0.0</span>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 overflow-auto">
                {{-- Top Header --}}
                <header class="border-b px-6 py-4 lg:px-8" style="border-color: var(--tw-border-opacity, 0.1)">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold tracking-tight">{{ $content['title'] ?? 'Workspace' }}</h2>
                            <p class="mt-1 text-sm opacity-60">{{ $content['tagline'] ?? 'Manage your projects and features.' }}</p>
                        </div>
                        <button class="{{ $tokens['button'] }}" type="button">New Project</button>
                    </div>
                </header>

                {{-- Content --}}
                <div class="p-6 lg:p-8">
                    {{-- Stats Row --}}
                    <div class="mb-8 grid gap-4 sm:grid-cols-3">
                        @foreach (array_slice($content['sections'] ?? [], 0, 3) as $section)
                            <div class="{{ $tokens['card'] }} p-5">
                                <p class="text-xs font-medium uppercase tracking-wider opacity-60">{{ $section['heading'] }}</p>
                                <p class="mt-2 text-sm leading-relaxed opacity-80">{{ Str::limit($section['body'], 80) }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Features Grid --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-bold tracking-tight">Modules</h3>
                        <p class="mt-1 text-sm opacity-60">Core capabilities of this application.</p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        @foreach ($content['sections'] ?? [] as $section)
                            <article class="{{ $tokens['card'] }} p-6">
                                <div class="flex items-start gap-4">
                                    <div class="{{ $tokens['feature_icon'] }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-base font-semibold">{{ $section['heading'] }}</h4>
                                        <p class="mt-2 text-sm leading-relaxed opacity-70">{{ $section['body'] }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
