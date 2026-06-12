<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>{{ $transcriptSession->project_name ?: 'Transcript Export' }}</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                color: #18181b;
                margin: 40px;
                line-height: 1.5;
            }

            header {
                margin-bottom: 32px;
                border-bottom: 1px solid #d4d4d8;
                padding-bottom: 16px;
            }

            h1, h2, h3 {
                color: #09090b;
            }

            .meta {
                color: #52525b;
                font-size: 14px;
            }

            .content {
                max-width: 900px;
            }

            pre {
                white-space: pre-wrap;
            }
        </style>
    </head>
    <body>
        <header>
            <h1>{{ $transcriptSession->project_name ?: 'Transcript Export' }}</h1>
            <p class="meta">{{ str($output->type)->replace('_', ' ')->headline() }}</p>
            <p class="meta">{{ $transcriptSession->project_summary }}</p>
        </header>

        <main class="content">
            {!! $renderedMarkdown !!}
        </main>
    </body>
</html>
