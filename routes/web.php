<?php

use Gobozzz\VoiceAssistant\Http\Controllers\VoiceNavigationController;
use Illuminate\Support\Facades\Route;

Route::post('/voice-assistant-site', VoiceNavigationController::class);
