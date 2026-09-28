<?php

use Gabrielesbaiz\NovaCardRssNews\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function feed_fixture(string $name): string
{
    return (string) file_get_contents(__DIR__.'/Fixtures/'.$name);
}
