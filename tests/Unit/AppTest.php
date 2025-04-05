<?php

namespace ApproTickets\Tests\Unit;

use ApproTickets\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

test('confirm environment is set to testing', function () {
    expect(config('app.env'))->toBe('testing');
});