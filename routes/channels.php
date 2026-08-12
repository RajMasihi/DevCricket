<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('match.{matchId}', fn () => true);
Broadcast::channel('live-matches', fn () => true);
