<?php

test('api docs page is available', function () {
    $this->get('/docs')->assertOk();
});
