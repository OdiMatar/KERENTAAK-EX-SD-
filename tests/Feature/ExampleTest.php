<?php

test('the application returns a successful homepage response', function () {
    $response = $this->get('/');

    $response
        ->assertStatus(200)
        ->assertSee('Kniploket Tiko')
        ->assertSee('Online afspraken, producten en salonbeheer.');
});
