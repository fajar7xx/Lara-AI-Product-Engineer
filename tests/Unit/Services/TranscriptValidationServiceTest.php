<?php

use App\Services\Transcripts\TranscriptValidationService;

test('it rejects a too short transcript', function () {
    $result = app(TranscriptValidationService::class)->validate('short note about product');

    expect($result['valid'])->toBeFalse();
    expect($result['errors'])->not->toBeEmpty();
});

test('it rejects a transcript without enough product signals', function () {
    $transcript = 'This conversation is about scheduling lunch tomorrow and picking a cafe near the office because the team wants a different place for a casual meetup after work.';

    $result = app(TranscriptValidationService::class)->validate($transcript);

    expect($result['valid'])->toBeFalse();
    expect($result['errors'])->toContain('Transcript must describe a product, user, or feature direction clearly enough for generation.');
});

test('it accepts a transcript with product, user, and feature context', function () {
    $transcript = 'We are planning a product that helps startup founders and product managers turn meeting notes into a dashboard-ready specification. The app should extract goals, user problems, and key features so the team can generate a landing page and a documentation workflow faster.';

    $result = app(TranscriptValidationService::class)->validate($transcript);

    expect($result['valid'])->toBeTrue();
    expect($result['errors'])->toBeEmpty();
});
