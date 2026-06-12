<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $content['title'] ?? 'Generated App Shell' }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="{{ $tokens['page'] }}">
        <div class="grid min-h-screen lg:grid-cols-[280px_1fr]">
            <aside class="{{ $tokens['card'] }} m-4 p-6">
                <span class="{{ $tokens['badge'] }}">app_shell</span>
                <h1 class="mt-4 text-2xl font-semibold">{{ $content['title'] ?? 'Generated App Shell' }}</h1>
                <p class="mt-3">{{ $content['tagline'] ?? 'AI-generated application shell.' }}</p>
            </aside>

            <main class="space-y-6 p-4 lg:p-6">
                <header class="{{ $tokens['card'] }} p-6">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-semibold">Workspace Overview</h2>
                        <button class="{{ $tokens['button'] }}" type="button">Primary Action</button>
                    </div>
                </header>

                <section class="grid gap-6 xl:grid-cols-2">
                    @foreach (($content['sections'] ?? []) as $section)
                        <article class="{{ $tokens['card'] }} p-6">
                            <h3 class="text-lg font-semibold">{{ $section['heading'] }}</h3>
                            <p class="mt-3">{{ $section['body'] }}</p>
                        </article>
                    @endforeach
                </section>
            </main>
        </div>
    </body>
</html>
