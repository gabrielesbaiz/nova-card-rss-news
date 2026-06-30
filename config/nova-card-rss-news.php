<?php

/*
 * RSS feed sources, organised by category.
 *
 * Comment out any source you do not want exposed to the cards. The category
 * key (e.g. 'motori') and source key (e.g. 'motor1') are the identifiers
 * referenced from the dashboard:
 *
 *     (new NovaCardRssNews)->source('motor1')
 *     (new NovaCardRssNewsSelect)->defaultSource('motor1')
 */
return [
    'categories' => [
        'assicurazioni_specializzate' => [
            'label' => 'Assicurazioni — Testate specializzate',
            'sources' => [
                'assinews' => [
                    'title' => 'Assinews',
                    'url' => 'https://www.assinews.it/feed/',
                ],
                'insurance_up' => [
                    'title' => 'InsuranceUp',
                    'url' => 'https://www.insuranceup.it/feed/',
                ],
                'intermedia_channel' => [
                    'title' => 'Intermedia Channel',
                    'url' => 'https://www.intermediachannel.it/feed/',
                ],
            ],
        ],

        'assicurazioni_istituzioni' => [
            'label' => 'Assicurazioni — Autorità e istituzioni',
            'sources' => [
                'ania' => [
                    'title' => 'ANIA',
                    'url' => 'https://www.ania.it/feed/',
                ],
                'ivass' => [
                    'title' => 'IVASS',
                    'url' => 'https://www.ivass.it/util/index.rss.html?lingua=it',
                ],
            ],
        ],

        'assicurazioni_economiche' => [
            'label' => 'Assicurazioni — Sezioni da testate economiche',
            'sources' => [
                'firstonline_assicurazioni' => [
                    'title' => 'FIRSTonline — Assicurazioni',
                    'url' => 'https://www.firstonline.info/tag/assicurazioni/feed/',
                ],
                'il_sole_24_ore_finanza_assicurazioni' => [
                    'title' => 'Il Sole 24 ORE — Finanza',
                    'url' => 'https://www.ilsole24ore.com/rss/finanza.xml',
                ],
            ],
        ],

        'agenzie_stampa' => [
            'label' => 'Agenzie di stampa',
            'sources' => [
                'ansa_all' => [
                    'title' => 'ANSA',
                    'url' => 'https://www.ansa.it/sito/ansait_rss.xml',
                ],
                'ansa_cronaca' => [
                    'title' => 'ANSA Cronaca',
                    'url' => 'https://www.ansa.it/sito/notizie/cronaca/cronaca_rss.xml',
                ],
                'ansa_cultura' => [
                    'title' => 'ANSA Cultura',
                    'url' => 'https://www.ansa.it/sito/notizie/cultura/cultura_rss.xml',
                ],
                'ansa_economia' => [
                    'title' => 'ANSA Economia',
                    'url' => 'https://www.ansa.it/sito/notizie/economia/economia_rss.xml',
                ],
                'ansa_sport' => [
                    'title' => 'ANSA Sport',
                    'url' => 'https://www.ansa.it/sito/notizie/sport/sport_rss.xml',
                ],
                'ansa_top_news' => [
                    'title' => 'ANSA Top News',
                    'url' => 'https://www.ansa.it/sito/notizie/topnews/topnews_rss.xml',
                ],
            ],
        ],

        'economia' => [
            'label' => 'Economia / Finanza',
            'sources' => [
                'il_sole_24_ore_finanza' => [
                    'title' => 'Il Sole 24 ORE — Finanza',
                    'url' => 'https://www.ilsole24ore.com/rss/finanza.xml',
                ],
                'il_sole_24_ore_italia' => [
                    'title' => 'Il Sole 24 ORE — Italia',
                    'url' => 'https://www.ilsole24ore.com/rss/italia.xml',
                ],
                'il_sole_24_ore_norme_tributi' => [
                    'title' => 'Il Sole 24 ORE — Norme & Tributi',
                    'url' => 'https://www.ilsole24ore.com/rss/norme-e-tributi.xml',
                ],
                'il_sole_24_ore_risparmio' => [
                    'title' => 'Il Sole 24 ORE — Risparmio',
                    'url' => 'https://www.ilsole24ore.com/rss/risparmio.xml',
                ],
            ],
        ],

        'motori' => [
            'label' => 'Motori',
            'sources' => [
                'alvolante' => [
                    'title' => 'Alvolante.it',
                    'url' => 'https://www.alvolante.it/rss.xml',
                ],
                'ansa_motori' => [
                    'title' => 'ANSA Motori',
                    'url' => 'https://www.ansa.it/canale_motori/notizie/motori_rss.xml',
                ],
                'corriere_motori' => [
                    'title' => 'Corriere Motori',
                    'url' => 'http://xml2.corriereobjects.it/rss/motori.xml',
                ],
                'il_sole_24_ore_motori' => [
                    'title' => 'Il Sole 24 ORE Motori',
                    'url' => 'https://www.ilsole24ore.com/rss/motori.xml',
                ],
                'motor1' => [
                    'title' => 'Motor1.com',
                    'url' => 'https://it.motor1.com/rss/articles/all/',
                ],
                'motori_it' => [
                    'title' => 'Motori.it',
                    'url' => 'https://www.motori.it/feed',
                ],
                'quattroruote' => [
                    'title' => 'Quattroruote',
                    'url' => 'https://www.quattroruote.it/content/quattroruote/it/listino/feeds/newsRss/feed.xml',
                ],
            ],
        ],

        'quotidiani' => [
            'label' => 'Quotidiani nazionali',
            'sources' => [
                'corriere_home' => [
                    'title' => 'Corriere della Sera (homepage)',
                    'url' => 'https://xml2.corriereobjects.it/rss/homepage.xml',
                ],
                'il_fatto_quotidiano' => [
                    'title' => 'Il Fatto Quotidiano',
                    'url' => 'https://www.ilfattoquotidiano.it/feed/',
                ],
                'la_stampa' => [
                    'title' => 'La Stampa (copertina)',
                    'url' => 'https://www.lastampa.it/rss/copertina.xml',
                ],
                'panorama' => [
                    'title' => 'Panorama',
                    'url' => 'https://www.panorama.it/feeds/feed.rss',
                ],
                'repubblica_home' => [
                    'title' => 'Repubblica (homepage)',
                    'url' => 'https://www.repubblica.it/rss/homepage/rss2.0.xml',
                ],
                'repubblica_cronaca' => [
                    'title' => 'Repubblica Cronaca',
                    'url' => 'https://www.repubblica.it/rss/cronaca/rss2.0.xml',
                ],
                'repubblica_economia' => [
                    'title' => 'Repubblica Economia',
                    'url' => 'https://www.repubblica.it/rss/economia/rss2.0.xml',
                ],
                'repubblica_politica' => [
                    'title' => 'Repubblica Politica',
                    'url' => 'https://www.repubblica.it/rss/politica/rss2.0.xml',
                ],
            ],
        ],

        'sport' => [
            'label' => 'Sport',
            'sources' => [
                'gazzetta' => [
                    'title' => 'Gazzetta dello Sport',
                    'url' => 'https://www.gazzetta.it/rss/home.xml',
                ],
            ],
        ],

        'viaggi' => [
            'label' => 'Viaggi',
            'sources' => [
                'aerei' => [
                    'title' => 'Aerei',
                    'url' => 'https://www.masterviaggi.it/feed.php?idcat=16',
                ],
                'aeroporti' => [
                    'title' => 'Aeroporti',
                    'url' => 'https://www.masterviaggi.it/feed.php?idcat=17',
                ],
                'autonoleggio' => [
                    'title' => 'Autonoleggio',
                    'url' => 'https://www.masterviaggi.it/feed.php?idcat=21',
                ],
                'beb_agriturismi' => [
                    'title' => 'B&B, Agriturismi & Co.',
                    'url' => 'https://www.masterviaggi.it/feed.php?idcat=27',
                ],
                'crociere' => [
                    'title' => 'Crociere',
                    'url' => 'https://www.masterviaggi.it/feed.php?idcat=18',
                ],
                'ferrovie' => [
                    'title' => 'Ferrovie',
                    'url' => 'https://www.masterviaggi.it/feed.php?idcat=20',
                ],
                'hotel_catene' => [
                    'title' => 'Hotel e Catene Alberghiere',
                    'url' => 'https://www.masterviaggi.it/feed.php?idcat=28',
                ],
                'nonsoloturisti' => [
                    'title' => 'Nonsoloturisti',
                    'url' => 'https://nonsoloturisti.it/feed/',
                ],
                'si_viaggia' => [
                    'title' => 'SiViaggia',
                    'url' => 'https://siviaggia.it/feed/',
                ],
                'tour_operator' => [
                    'title' => 'Tour Operator',
                    'url' => 'https://www.masterviaggi.it/feed.php?idcat=26',
                ],
                'traghetti' => [
                    'title' => 'Traghetti',
                    'url' => 'https://www.masterviaggi.it/feed.php?idcat=19',
                ],
                'travel_quotidiano' => [
                    'title' => 'TravelQuotidiano',
                    'url' => 'https://www.travelquotidiano.com/feed/',
                ],
            ],
        ],

        'aggregatori' => [
            'label' => 'Aggregatori',
            'sources' => [
                'google_news_it' => [
                    'title' => 'Google News Italia',
                    'url' => 'https://news.google.com/rss?hl=it&gl=IT&ceid=IT:it',
                ],
            ],
        ],
    ],
];
