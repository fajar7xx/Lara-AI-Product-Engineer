<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('transcripts/create', 'pages::transcripts.create')->name('transcripts.create');
    Route::livewire('transcripts/{transcriptSession}/clarify', 'pages::transcripts.clarify')->name('transcripts.clarify');
    Route::livewire('transcripts/{transcriptSession}', 'pages::transcripts.show')->name('transcripts.show');
});

require __DIR__.'/settings.php';
