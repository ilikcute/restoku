<?php

test('basic true assertion', function () {
    expect(true)->toBeTrue();
});

test('basic calculation', function () {
    expect(2 + 2)->toBe(4);
});

test('string contains substring', function () {
    $string = 'Laravel is awesome';
    expect($string)->toContain('awesome');
});
