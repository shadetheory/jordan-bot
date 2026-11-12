<?php

it('links the ask page to the shadetheory GitHub org', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('href="https://github.com/shadetheory"', false);
    $response->assertSee('github.com/shadetheory', false);
    $response->assertSee('ask.jordanpartridge.us', false);
    $response->assertDontSee('github.com/jordanpartridge', false);
});
