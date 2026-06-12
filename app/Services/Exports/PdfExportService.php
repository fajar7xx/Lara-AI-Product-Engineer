<?php

namespace App\Services\Exports;

use App\Models\GenerationOutput;
use App\Models\TranscriptSession;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

class PdfExportService
{
    /**
     * @return array{path: string, filename: string}
     */
    public function exportTranscriptOutput(TranscriptSession $transcriptSession, GenerationOutput $output): array
    {
        $htmlPath = tempnam(sys_get_temp_dir(), 'transcript-export-');
        $pdfPath = tempnam(sys_get_temp_dir(), 'transcript-export-');

        if ($htmlPath === false || $pdfPath === false) {
            throw new RuntimeException('Unable to create temporary export files.');
        }

        $htmlPathWithExtension = $htmlPath.'.html';
        $pdfPathWithExtension = $pdfPath.'.pdf';

        rename($htmlPath, $htmlPathWithExtension);
        rename($pdfPath, $pdfPathWithExtension);

        file_put_contents($htmlPathWithExtension, $this->renderHtmlDocument($transcriptSession, $output));

        $result = Process::run([
            '/usr/bin/chromium-browser',
            '--headless',
            '--disable-gpu',
            '--no-sandbox',
            '--print-to-pdf='.$pdfPathWithExtension,
            'file://'.$htmlPathWithExtension,
        ]);

        @unlink($htmlPathWithExtension);

        if ($result->failed()) {
            @unlink($pdfPathWithExtension);

            throw new RuntimeException($result->errorOutput() ?: 'PDF export failed.');
        }

        if (! is_file($pdfPathWithExtension)) {
            throw new RuntimeException('PDF export did not produce a file.');
        }

        return [
            'path' => $pdfPathWithExtension,
            'filename' => $this->filenameFor($transcriptSession, $output),
        ];
    }

    protected function renderHtmlDocument(TranscriptSession $transcriptSession, GenerationOutput $output): string
    {
        if ($output->type === GenerationOutput::TYPE_HTML_PAGE) {
            return $output->content ?? '';
        }

        return (string) view('exports.transcript-output-pdf', [
            'transcriptSession' => $transcriptSession,
            'output' => $output,
            'renderedMarkdown' => Str::markdown($output->content ?? '', [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
        ])->render();
    }

    protected function filenameFor(TranscriptSession $transcriptSession, GenerationOutput $output): string
    {
        $project = Str::of($transcriptSession->project_name ?: 'transcript-session')
            ->slug()
            ->limit(40, '');

        return sprintf('%s-%s.pdf', $project, str_replace('_', '-', $output->type));
    }
}
