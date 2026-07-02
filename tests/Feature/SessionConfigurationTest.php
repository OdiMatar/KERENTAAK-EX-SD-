<?php

use Illuminate\Support\Facades\Schema;

it('creates the sessions table when database sessions are enabled', function () {
    expect(Schema::hasTable('sessions'))->toBeTrue();
});
