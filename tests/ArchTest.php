<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->each->not->toBeUsed();

arch('data objects stay free of framework facades')
    ->expect('Gabrielesbaiz\NovaCardRssNews\Data')
    ->not->toUse(['Illuminate\Support\Facades\DB', 'Illuminate\Support\Facades\Http']);

arch('parsers implement the parser contract')
    ->expect('Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers')
    ->toImplement('Gabrielesbaiz\NovaCardRssNews\Contracts\FeedParser');

arch('presets implement the source provider contract')
    ->expect('Gabrielesbaiz\NovaCardRssNews\Presets')
    ->toImplement('Gabrielesbaiz\NovaCardRssNews\Contracts\SourceProvider');
