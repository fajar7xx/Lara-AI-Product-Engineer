<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $content['title'] ?? 'Generated Landing Page' }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="{{ $tokens['page'] }}">
        <main class="mx-auto flex min-h-screen max-w-6xl flex-col gap-10 px-6 py-16">
            <header class="{{ $tokens['card'] }} p-8">
                <span class="{{ $tokens['badge'] }}">landing</span>
                <h1 class="mt-4 text-4xl font-semibold">{{ $content['title'] ?? 'Generated Landing Page' }}</h1>
                <p class="mt-4 max-w-3xl text-lg">{{ $content['tagline'] ?? 'AI-generated landing page content.' }}</p>
                <div class="mt-6">
                    <button class="{{ $tokens['button'] }}" type="button">Primary Action</button>
                </div>
            </header>

            <section class="grid gap-6 md:grid-cols-2">
                @foreach (($content['sections'] ?? []) as $section)
                    <article class="{{ $tokens['card'] }} p-6">
                        <h2 class="text-xl font-semibold">{{ $section['heading'] }}</h2>
                        <p class="mt-3">{{ $section['body'] }}</p>
                    </article>
                @endforeach
            </section>
        </main>
    </body>
</html>
