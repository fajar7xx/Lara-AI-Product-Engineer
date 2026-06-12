<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $content['title'] ?? 'Generated Landing Page' }}</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body class="{{ $tokens['page'] }}">

        {{-- Navigation --}}
        <nav class="flex items-center justify-between px-6 py-4 lg:px-12">
            <span class="text-lg font-bold tracking-tight">{{ $content['title'] ?? 'Product' }}</span>
            <div class="flex items-center gap-3">
                <span class="{{ $tokens['badge'] }}">beta</span>
                <button class="{{ $tokens['button'] }}" type="button">Get Started</button>
            </div>
        </nav>

        {{-- Hero Section --}}
        <section class="{{ $tokens['hero'] }}">
            <div class="mx-auto max-w-5xl px-6 py-24 text-center lg:py-32">
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                    {{ $content['title'] ?? 'Welcome' }}
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed opacity-80">
                    {{ $content['tagline'] ?? 'AI-generated content.' }}
                </p>
                <div class="mt-10 flex items-center justify-center gap-4">
                    <button class="{{ $tokens['button'] }}" type="button">Start Now</button>
                    <button class="{{ $tokens['button_secondary'] }}" type="button">Learn More</button>
                </div>
            </div>
        </section>

        {{-- Features Section --}}
        <section class="px-6 py-20 lg:px-12">
            <div class="mx-auto max-w-5xl">
                <div class="mb-12 text-center">
                    <span class="{{ $tokens['badge'] }}">Features</span>
                    <h2 class="mt-4 text-3xl font-bold tracking-tight">Everything you need</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg opacity-70">Built to help you ship faster and smarter.</p>
                </div>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach (($content['sections'] ?? []) as $index => $section)
                        <article class="{{ $tokens['card'] }} p-6">
                            <div class="{{ $tokens['feature_icon'] }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold">{{ $section['heading'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed opacity-70">{{ $section['body'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- CTA Section --}}
        <section class="px-6 py-20 lg:px-12">
            <div class="{{ $tokens['card'] }} mx-auto max-w-4xl p-12 text-center">
                <h2 class="text-2xl font-bold tracking-tight">Ready to get started?</h2>
                <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed opacity-70">
                    Join teams who are building better products faster with {{ $content['title'] ?? 'this solution' }}.
                </p>
                <div class="mt-8">
                    <button class="{{ $tokens['button'] }}" type="button">Get Started Free</button>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="{{ $tokens['footer'] }} px-6 py-8 lg:px-12">
            <div class="mx-auto max-w-5xl flex flex-col items-center justify-between gap-4 sm:flex-row">
                <span class="text-sm font-medium opacity-60">&copy; {{ date('Y') }} {{ $content['title'] ?? 'Product' }}. All rights reserved.</span>
                <div class="flex items-center gap-6 text-sm opacity-60">
                    <a href="#" class="hover:opacity-100 transition-opacity">Privacy</a>
                    <a href="#" class="hover:opacity-100 transition-opacity">Terms</a>
                    <a href="#" class="hover:opacity-100 transition-opacity">Contact</a>
                </div>
            </div>
        </footer>

    </body>
</html>
