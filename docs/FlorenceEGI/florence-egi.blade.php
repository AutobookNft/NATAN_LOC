{{--
    File: florence-egi.blade.php
    Versione: 1.1 FlorenceEGI Istituzionale (Localized)
    Data: 28 Settembre 2025
    Descrizione: Pagina istituzionale localizzata con chiavi di traduzione. Connessa a /resources/lang/[locale]/info_florence_egi.php
    Caratteristiche: Brand Guidelines, CoA Integration, Vision tecnica completa, i18n Ready
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('info_florence_egi.meta_title') }}</title>
    <meta name="description" content="{{ __('info_florence_egi.meta_description') }}">
    <meta name="keywords" content="{{ __('info_florence_egi.meta_keywords') }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <meta name="author" content="{{ __('info_florence_egi.meta_author') }}">
    <meta name="language" content="{{ app()->getLocale() }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:title" content="{{ __('info_florence_egi.og_title') }}">
    <meta property="og:description" content="{{ __('info_florence_egi.og_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ __('info_florence_egi.og_site_name') }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta property="og:image" content="{{ asset('images/og/florence-egi-social.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ __('info_florence_egi.og_image_alt') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('info_florence_egi.twitter_title') }}">
    <meta name="twitter:description" content="{{ __('info_florence_egi.twitter_description') }}">
    <meta name="twitter:site" content="@FlorenceEGI">
    <meta name="twitter:creator" content="@FlorenceEGI">
    <meta name="twitter:image" content="{{ asset('images/twitter/florence-egi-twitter.jpg') }}">
    <meta name="twitter:image:alt" content="{{ __('info_florence_egi.twitter_image_alt') }}">

    <meta name="theme-color" content="#D4A574">
    <meta name="apple-mobile-web-app-title" content="{{ __('info_florence_egi.meta_app_title') }}">
    <meta name="application-name" content="{{ __('info_florence_egi.meta_app_name') }}">
    <meta name="msapplication-TileColor" content="#D4A574">

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'oro-fiorentino': '#D4A574',
                        'verde-rinascita': '#2D5016',
                        'blu-algoritmo': '#1B365D',
                        'grigio-pietra': '#6B6B6B',
                        'rosso-urgenza': '#C13120',
                        'arancio-energia': '#E67E22',
                        'viola-innovazione': '#8E44AD'
                    },
                    fontFamily: {
                        'renaissance': ['Playfair Display', 'serif'],
                        'body': ['Source Sans Pro', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            overflow-x: hidden;
        }

        .renaissance-title {
            font-family: 'Playfair Display', serif;
        }

        .golden-ratio-container {
            max-width: 1618px;
            margin: 0 auto;
        }

        .elegant-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .elegant-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .cta-primary {
            background: linear-gradient(135deg, #D4A574 0%, #B8956A 100%);
            box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
        }

        .cta-primary:hover {
            box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
            transform: translateY(-1px);
        }

        .hero-background {
            background: linear-gradient(135deg, rgba(27, 54, 93, 0.95) 0%, rgba(45, 80, 22, 0.85) 100%),
                url('{{ asset('images/default/patron_banner_background_rinascimento_1.png') }}') no-repeat center center/cover;
            min-height: 70vh;
        }

        .renaissance-card {
            background: linear-gradient(145deg, #ffffff 0%, #fafafa 100%);
            border: 1px solid rgba(212, 165, 116, 0.2);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .renaissance-card:hover {
            border-color: #D4A574;
            box-shadow: 0 8px 30px rgba(212, 165, 116, 0.15);
        }

        .section-dark {
            background: linear-gradient(135deg, #1B365D 0%, #2D5016 100%);
        }

        .code-block {
            background: #1a1a1a;
            border-left: 4px solid #D4A574;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.85rem;
        }

        .trilemma-point {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "@id": "{{ url('/') }}#organization",
                "name": "{{ __('info_florence_egi.schema.organization.name') }}",
                "url": "{{ url('/') }}",
                "logo": {
                    "@type": "ImageObject",
                    "url": "{{ asset('images/logo-florence-egi.png') }}",
                    "width": 200,
                    "height": 200
                },
                "description": "{{ __('info_florence_egi.schema.organization.description') }}",
                "foundingDate": "2024",
                "sameAs": [
                    "https://www.linkedin.com/company/florence-egi",
                    "https://twitter.com/florence_egi"
                ]
            },
            {
                "@type": "WebSite",
                "@id": "{{ url('/') }}#website",
                "url": "{{ url('/') }}",
                "name": "{{ __('info_florence_egi.schema.website.name') }}",
                "publisher": {
                    "@id": "{{ url('/') }}#organization"
                },
                "inLanguage": "{{ str_replace('_', '-', app()->getLocale()) }}"
            },
            {
                "@type": "TechArticle",
                "@id": "{{ url()->current() }}#webpage",
                "url": "{{ url()->current() }}",
                "name": "{{ __('info_florence_egi.schema.article.name') }}",
                "headline": "{{ __('info_florence_egi.schema.article.headline') }}",
                "description": "{{ __('info_florence_egi.schema.article.description') }}",
                "inLanguage": "{{ str_replace('_', '-', app()->getLocale()) }}",
                "isPartOf": {
                    "@id": "{{ url('/') }}#website"
                },
                "author": {
                    "@id": "{{ url('/') }}#organization"
                },
                "publisher": {
                    "@id": "{{ url('/') }}#organization"
                },
                "datePublished": "2024-09-29",
                "dateModified": "2025-09-29",
                "about": [
                    {
                        "@type": "Thing",
                        "name": "{{ __('info_florence_egi.schema.topics.blockchain_art') }}"
                    },
                    {
                        "@type": "Thing",
                        "name": "{{ __('info_florence_egi.schema.topics.nft_marketplace') }}"
                    },
                    {
                        "@type": "Thing",
                        "name": "{{ __('info_florence_egi.schema.topics.environmental_impact') }}"
                    },
                    {
                        "@type": "Thing",
                        "name": "{{ __('info_florence_egi.schema.topics.algorand_blockchain') }}"
                    }
                ]
            }
        ]
    }
    </script>
</head>

<body class="pt-20 bg-gray-50 text-grigio-pietra">

    <header class="fixed top-0 left-0 right-0 z-50 text-white shadow-lg bg-blu-algoritmo">
        <div class="px-4 py-4 golden-ratio-container sm:px-6 sm:py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <i class="text-3xl fas fa-infinity text-oro-fiorentino sm:text-4xl"></i>
                    <div>
                        <h1 class="text-xl font-bold renaissance-title sm:text-2xl">
                            {{ __('info_florence_egi.header_brand_name') }}</h1>
                        <p class="text-sm text-blue-200 font-body sm:text-base">
                            {{ __('info_florence_egi.header_brand_slogan') }}</p>
                    </div>
                </div>
                <nav class="hidden space-x-3 md:flex" aria-label="{{ __('info_florence_egi.nav_aria_label') }}">
                    <a href="{{ route('home') }}"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_home') }}</a>
                    <a href="#visione"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_vision') }}</a>
                    <a href="#problema"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_problem') }}</a>
                    <a href="#egi"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_egi') }}</a>
                    <a href="#ammk"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">AMMk</a>
                    <a href="#soluzione"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_solution') }}</a>
                    <a href="#tecnologia"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_technology') }}</a>
                    <a href="#gdpr"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_gdpr') }}</a>
                    <a href="#archetipi"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_archetypes') }}</a>
                    <a href="#valori"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_values') }}</a>
                    <a href="#equilibrium"
                        class="text-sm transition hover:text-oro-fiorentino font-body lg:text-base">{{ __('info_florence_egi.nav_equilibrium') }}</a>
                </nav>
                <button id="mobile-menu-button"
                    class="block p-2 transition-colors rounded-md hover:bg-blue-700 md:hidden"><i
                        class="text-2xl fas fa-bars"></i></button>
            </div>
            <div id="mobile-menu" class="hidden pb-4 mt-4 border-t border-blue-600 md:hidden">
                <div class="pt-4 space-y-3">
                    <a href="{{ route('home') }}"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-home text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_home') }}</a>
                    <a href="#visione"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-eye text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_vision') }}</a>
                    <a href="#problema"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-exclamation-triangle text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_problem') }}</a>
                    <a href="#egi"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-gem text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_egi') }}</a>
                    <a href="#ammk"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-cog text-oro-fiorentino"></i>
                        AMMk</a>
                    <a href="#soluzione"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-lightbulb text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_solution') }}</a>
                    <a href="#tecnologia"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-cogs text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_technology') }}</a>
                    <a href="#gdpr"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-shield-alt text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_gdpr') }}</a>
                    <a href="#archetipi"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-users text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_archetypes') }}</a>
                    <a href="#valori"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-heart text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_values') }}</a>
                    <a href="#equilibrium"
                        class="flex items-center px-4 py-2 text-sm transition-colors rounded-md hover:bg-blue-700"><i
                            class="mr-3 text-lg fas fa-atom text-oro-fiorentino"></i>
                        {{ __('info_florence_egi.nav_equilibrium') }}</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section id="visione" class="text-white hero-background"
            aria-label="{{ __('info_florence_egi.hero.aria_label') }}">
            <div class="px-4 py-16 golden-ratio-container sm:px-6 sm:py-24">
                <div class="max-w-5xl mx-auto text-center">
                    <h1 class="mb-6 text-4xl font-bold leading-tight renaissance-title sm:text-5xl md:text-6xl">
                        {!! __('info_florence_egi.hero.title_html') !!}</h1>
                    <p class="max-w-4xl mx-auto mb-8 text-xl text-green-100 font-body sm:text-2xl">
                        {!! __('info_florence_egi.hero.subtitle_html') !!}</p>
                    <div class="max-w-4xl mx-auto mb-8 text-lg font-body">
                        {{ __('info_florence_egi.hero.description') }}
                    </div>
                    <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                        <a href="#soluzione" aria-label="{{ __('info_florence_egi.hero.cta_primary_aria') }}"
                            class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white cta-primary elegant-hover rounded-xl"><i
                                class="mr-3 fas fa-rocket"></i> {{ __('info_florence_egi.hero.cta_primary') }}</a>
                        <a href="#tecnologia" aria-label="{{ __('info_florence_egi.hero.cta_secondary_aria') }}"
                            class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold transition-all border-2 border-oro-fiorentino text-oro-fiorentino hover:bg-oro-fiorentino elegant-hover rounded-xl hover:text-blu-algoritmo"><i
                                class="mr-3 fas fa-cogs"></i> {{ __('info_florence_egi.hero.cta_secondary') }}</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="problema" class="py-16 bg-white sm:py-24"
            aria-label="{{ __('info_florence_egi.problem.aria_label') }}">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title text-grigio-pietra sm:text-4xl md:text-5xl">
                        {!! __('info_florence_egi.problem.title_html') !!}</h2>
                    <p class="max-w-3xl mx-auto text-xl font-body text-grigio-pietra">
                        {{ __('info_florence_egi.problem.subtitle') }}</p>
                </div>
                <div class="relative max-w-4xl mx-auto mb-16">
                    <div class="flex items-center justify-center h-96">
                        <div class="relative">
                            <div class="absolute transform -translate-x-1/2 -top-16 left-1/2">
                                <div class="trilemma-point bg-viola-innovazione">
                                    <div><i class="mb-2 text-2xl fas fa-palette"></i><br>{!! __('info_florence_egi.problem.trilemma_quality') !!}</div>
                                </div>
                            </div>
                            <div class="absolute -left-16 top-32">
                                <div class="trilemma-point bg-blu-algoritmo">
                                    <div><i class="mb-2 text-2xl fas fa-chart-line"></i><br>{!! __('info_florence_egi.problem.trilemma_liquidity') !!}
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -right-16 top-32">
                                <div class="trilemma-point bg-verde-rinascita">
                                    <div><i class="mb-2 text-2xl fas fa-leaf"></i><br>{!! __('info_florence_egi.problem.trilemma_impact') !!}</div>
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-center w-32 h-32 font-bold text-center text-white rounded-full bg-rosso-urgenza">
                                <div><i class="mb-2 text-3xl fas fa-times"></i><br>{!! __('info_florence_egi.problem.trilemma_impossible') !!}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid gap-8 mb-16 md:grid-cols-3">
                    <div class="p-6 text-center renaissance-card">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full">
                            <i class="text-2xl fas fa-times text-rosso-urgenza"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.problem.competitor_opensea') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">{!! __('info_florence_egi.problem.competitor_opensea_desc') !!}</p>
                    </div>
                    <div class="p-6 text-center renaissance-card">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full">
                            <i class="text-2xl fas fa-times text-rosso-urgenza"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.problem.competitor_blur') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">{!! __('info_florence_egi.problem.competitor_blur_desc') !!}</p>
                    </div>
                    <div class="p-6 text-center renaissance-card">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full">
                            <i class="text-2xl fas fa-times text-rosso-urgenza"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.problem.competitor_foundation') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">{!! __('info_florence_egi.problem.competitor_foundation_desc') !!}</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="egi" class="py-16 bg-gray-50 sm:py-24"
            aria-label="{{ __('info_florence_egi.egi.aria_label') }}">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title text-grigio-pietra sm:text-4xl md:text-5xl">
                        {!! __('info_florence_egi.egi.title_html') !!}</h2>
                    <p class="max-w-3xl mx-auto text-xl font-body text-grigio-pietra">{!! __('info_florence_egi.egi.subtitle_html') !!}</p>
                </div>
                <div class="grid gap-8 mb-16 md:grid-cols-3">
                    <div class="p-8 renaissance-card elegant-hover">
                        <div class="flex items-center justify-center mb-6">
                            <div class="flex items-center justify-center w-20 h-20 rounded-full bg-verde-rinascita/20">
                                <i class="text-3xl fas fa-seedling text-verde-rinascita"></i>
                            </div>
                        </div>
                        <h3 class="mb-4 text-xl font-bold text-center renaissance-title">
                            {{ __('info_florence_egi.egi.card1_title') }}</h3>
                        <p class="text-center font-body text-grigio-pietra">
                            {{ __('info_florence_egi.egi.card1_desc') }}
                        </p>
                    </div>
                    <div class="p-8 renaissance-card elegant-hover">
                        <div class="flex items-center justify-center mb-6">
                            <div class="flex items-center justify-center w-20 h-20 rounded-full bg-oro-fiorentino/20">
                                <i class="text-3xl fas fa-box text-oro-fiorentino"></i>
                            </div>
                        </div>
                        <h3 class="mb-4 text-xl font-bold text-center renaissance-title">
                            {{ __('info_florence_egi.egi.card2_title') }}</h3>
                        <p class="text-center font-body text-grigio-pietra">
                            {{ __('info_florence_egi.egi.card2_desc') }}
                        </p>
                    </div>
                    <div class="p-8 renaissance-card elegant-hover">
                        <div class="flex items-center justify-center mb-6">
                            <div
                                class="flex items-center justify-center w-20 h-20 rounded-full bg-viola-innovazione/20">
                                <i class="text-3xl fas fa-lightbulb text-viola-innovazione"></i>
                            </div>
                        </div>
                        <h3 class="mb-4 text-xl font-bold text-center renaissance-title">
                            {{ __('info_florence_egi.egi.card3_title') }}</h3>
                        <p class="text-center font-body text-grigio-pietra">
                            {{ __('info_florence_egi.egi.card3_desc') }}
                        </p>
                    </div>
                </div>
                <div class="p-8 bg-white rounded-2xl md:p-12">
                    <h3 class="mb-6 text-2xl font-bold text-center renaissance-title text-grigio-pietra">
                        {!! __('info_florence_egi.egi.cocreation_title_html') !!}</h3>
                    <div class="grid items-center gap-8 md:grid-cols-2">
                        <div class="space-y-4 font-body">
                            <p class="text-lg">{!! __('info_florence_egi.egi.cocreation_p1') !!}</p>
                            <p>{{ __('info_florence_egi.egi.cocreation_p2') }}</p>
                            <div class="p-4 rounded-lg bg-oro-fiorentino/10">
                                <p class="font-semibold text-oro-fiorentino"><i class="mr-2 fas fa-star"></i>
                                    {{ __('info_florence_egi.egi.cocreation_highlight') }}</p>
                            </div>
                        </div>
                        <div class="text-center">
                            <div
                                class="inline-flex items-center justify-center w-32 h-32 mb-4 rounded-full bg-oro-fiorentino/20">
                                <i class="text-4xl fas fa-handshake text-oro-fiorentino"></i>
                            </div>
                            <p class="font-body text-grigio-pietra">{!! __('info_florence_egi.egi.cocreation_p3') !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Asset Market Maker Section --}}
        <section id="ammk" class="py-16 bg-white sm:py-24" aria-label="Asset Market Maker">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title text-grigio-pietra sm:text-4xl md:text-5xl">
                        Asset Market Maker (AMMk)</h2>
                    <p class="max-w-3xl mx-auto text-xl font-body text-grigio-pietra">
                        Il cuore pulsante di FlorenceEGI: un motore che origina, certifica, valuta e rende liquidi gli
                        EGI con prove on-chain e automazioni guidate da NATAN
                    </p>
                </div>

                {{-- AMMk Core Functions --}}
                <div class="grid gap-8 mb-16 md:grid-cols-2 lg:grid-cols-3">
                    <div class="p-6 renaissance-card elegant-hover">
                        <div class="flex items-center justify-center mb-4">
                            <div
                                class="flex items-center justify-center w-16 h-16 rounded-full bg-arancio-energia/20">
                                <i class="text-2xl fas fa-brain text-arancio-energia"></i>
                            </div>
                        </div>
                        <h3 class="mb-3 text-lg font-bold text-center renaissance-title">NATAN Market Engine</h3>
                        <p class="text-sm text-center font-body text-grigio-pietra">
                            L’intelligenza del tenant che rende FlorenceEGI un market maker: combina Valuation (prezzo,
                            floor e curva di crescita) e Activation (campagne, alert e suggerimenti on/off-chain).
                        </p>
                    </div>

                    <div class="p-6 renaissance-card elegant-hover">
                        <div class="flex items-center justify-center mb-4">
                            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-blu-algoritmo/20">
                                <i class="text-2xl fas fa-cubes text-blu-algoritmo"></i>
                            </div>
                        </div>
                        <h3 class="mb-3 text-lg font-bold text-center renaissance-title">Asset Engine</h3>
                        <p class="text-sm text-center font-body text-grigio-pietra">
                            Gestisce listing, aste, vendite secondarie e liquidità degli EGI con regole trasparenti e
                            marketplace integrato.
                        </p>
                    </div>

                    <div class="p-6 renaissance-card elegant-hover">
                        <div class="flex items-center justify-center mb-4">
                            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-verde-rinascita/20">
                                <i class="text-2xl fas fa-network-wired text-verde-rinascita"></i>
                            </div>
                        </div>
                        <h3 class="mb-3 text-lg font-bold text-center renaissance-title">Distribution Engine</h3>
                        <p class="text-sm text-center font-body text-grigio-pietra">
                            Automatizza royalty, fee piattaforma e contributi <span class="whitespace-nowrap">EPP</span>,
                            garantendo tracciabilità fiscale end-to-end.
                        </p>
                    </div>

                    <div class="p-6 renaissance-card elegant-hover">
                        <div class="flex items-center justify-center mb-4">
                            <div
                                class="flex items-center justify-center w-16 h-16 rounded-full bg-viola-innovazione/20">
                                <i class="text-2xl fas fa-hands-helping text-viola-innovazione"></i>
                            </div>
                        </div>
                        <h3 class="mb-3 text-lg font-bold text-center renaissance-title">Co-Creation Engine</h3>
                        <p class="text-sm text-center font-body text-grigio-pietra">
                            Orchestra il flusso Creator / Co-Creator / Collector: minting, notarizzazione, firme e
                            catena di custodia dell’EGI.
                        </p>
                    </div>

                    <div class="p-6 renaissance-card elegant-hover">
                        <div class="flex items-center justify-center mb-4">
                            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-oro-fiorentino/20">
                                <i class="text-2xl fas fa-shield-alt text-oro-fiorentino"></i>
                            </div>
                        </div>
                        <h3 class="mb-3 text-lg font-bold text-center renaissance-title">Compliance Engine</h3>
                        <p class="text-sm text-center font-body text-grigio-pietra">
                            GDPR by design, audit trail completo e tutela <span class="whitespace-nowrap">MiCA-safe</span>
                            integrata in ogni operazione.
                        </p>
                    </div>
                </div>

                {{-- Modalità di vendita --}}
                <div class="mb-16">
                    <h3 class="mb-8 text-2xl font-bold text-center renaissance-title text-grigio-pietra">
                        Modalità di Vendita Flessibili
                    </h3>
                    <div class="grid gap-6 md:grid-cols-3">
                        <div class="p-6 border-l-4 renaissance-card border-verde-rinascita">
                            <div class="flex items-center mb-4">
                                <div
                                    class="flex items-center justify-center w-12 h-12 mr-3 font-bold text-white rounded-full bg-verde-rinascita">
                                    1
                                </div>
                                <h4 class="text-lg font-bold renaissance-title">Acquisto Diretto</h4>
                            </div>
                            <p class="text-sm font-body text-grigio-pietra">
                                <strong>Mint immediato</strong> al prezzo fissato dal Creator. Transazione istantanea
                                con certificazione blockchain in tempo reale.
                            </p>
                        </div>

                        <div class="p-6 border-l-4 renaissance-card border-oro-fiorentino">
                            <div class="flex items-center mb-4">
                                <div
                                    class="flex items-center justify-center w-12 h-12 mr-3 font-bold text-white rounded-full bg-oro-fiorentino">
                                    2
                                </div>
                                <h4 class="text-lg font-bold renaissance-title">Asta a Tempo</h4>
                            </div>
                            <p class="text-sm font-body text-grigio-pietra">
                                <strong>Soglia minima (reserve)</strong> con durata definita. Il miglior offerente
                                riceve l'EGI al termine dell'asta.
                            </p>
                        </div>

                        <div class="p-6 border-l-4 renaissance-card border-viola-innovazione">
                            <div class="flex items-center mb-4">
                                <div
                                    class="flex items-center justify-center w-12 h-12 mr-3 font-bold text-white rounded-full bg-viola-innovazione">
                                    3
                                </div>
                                <h4 class="text-lg font-bold renaissance-title">Modalità Mista</h4>
                            </div>
                            <p class="text-sm font-body text-grigio-pietra">
                                <strong>Asta + buy-now</strong> combinati. Gli interessati possono scegliere se
                                partecipare all'asta o acquistare immediatamente.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- NATAN & Drops --}}
                <div class="p-8 rounded-2xl bg-gradient-to-br from-viola-innovazione/5 to-blu-algoritmo/5 md:p-12">
                    <div class="grid items-center gap-8 md:grid-cols-2">
                        <div>
                            <div class="flex items-center mb-4">
                                <div
                                    class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-arancio-energia/20">
                                    <i class="text-2xl fas fa-brain text-arancio-energia"></i>
                                </div>
                                <h3 class="text-2xl font-bold renaissance-title text-grigio-pietra">NATAN</h3>
                            </div>
                            <p class="mb-4 font-body text-grigio-pietra">
                                <strong>NATAN è il tenant funzionale di FlorenceEGI</strong>: un layer cognitivo che
                                serve enti pubblici e privati con automazioni AI, suggerimenti operativi e servizi RAG
                                a supporto di notarizzazione e gestione documentale.
                            </p>
                            <p class="font-body text-grigio-pietra">
                                Gli <strong>smart contract intelligenti</strong> emettono trigger che attivano NATAN in
                                tempo reale, coordinandosi con FlorenceEGI Core per garantire coerenza di policy e
                                compliance mentre ciascun tenant opera con autonomia verticale.
                            </p>
                        </div>
                        <div>
                            <div class="flex items-center mb-4">
                                <div
                                    class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-oro-fiorentino/20">
                                    <i class="text-2xl fas fa-star text-oro-fiorentino"></i>
                                </div>
                                <h3 class="text-2xl font-bold renaissance-title text-grigio-pietra">Drops Trimestrali
                                </h3>
                            </div>
                            <p class="mb-4 font-body text-grigio-pietra">
                                Quattro volte l'anno, le <strong>Drop</strong> selezionano opere eccellenti e culminano
                                in una
                                <strong>Serata Memorabile</strong> che concentra attenzione, incentivi e liquidità.
                            </p>
                            <p class="text-sm text-oro-fiorentino font-body">
                                <i class="mr-2 fas fa-trophy"></i>
                                Un evento che celebra i Creator e valorizza le opere più significative
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-6 mt-10 border-l-4 rounded-2xl bg-white/80 border-oro-fiorentino shadow-lg">
                    <h4 class="mb-4 text-xl font-bold text-grigio-pietra renaissance-title">Architettura AMMk FlorenceEGI</h4>
                    <p class="font-body text-grigio-pietra">
                        Il <strong><a href="{{ url('/info/white-paper-finanziario#glossary-florenceegi-core') }}"
                                class="text-oro-fiorentino underline decoration-2 decoration-oro-fiorentino/60 hover:text-blu-algoritmo">FlorenceEGI
                                Core</a></strong> coordina i cinque engine che definiscono un <a
                            href="{{ url('/info/white-paper-finanziario#glossary-ammk') }}"
                            class="text-oro-fiorentino underline decoration-2 decoration-oro-fiorentino/60 hover:text-blu-algoritmo">AMMk</a>:
                    </p>
                    <ul class="mt-4 space-y-3 text-grigio-pietra font-body list-disc list-inside">
                        <li>
                            <strong><a href="{{ url('/info/white-paper-finanziario#glossary-natan') }}"
                                    class="text-oro-fiorentino underline decoration-2 decoration-oro-fiorentino/60 hover:text-blu-algoritmo">NATAN
                                    Market Engine</a></strong> – l’intelligenza del tenant che rende la piattaforma un
                            vero market maker:
                            <ul class="mt-2 ml-5 space-y-1 list-disc list-inside text-grigio-pietra">
                                <li><strong>Valuation</strong> – definisce valore, floor price e traiettoria economica
                                    analizzando qualità, storico e domanda.</li>
                                <li><strong>Activation</strong> – suggerimenti e campagne attivati da trigger
                                    on/off-chain.</li>
                            </ul>
                        </li>
        ... (truncated)
                </div>
            </div>
        </section>

        <section id="soluzione" class="py-16 text-white section-dark sm:py-24"
            aria-label="{{ __('info_florence_egi.solution.aria_label') }}">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title sm:text-4xl md:text-5xl">
                        {!! __('info_florence_egi.solution.title_html') !!}</h2>
                    <p class="max-w-3xl mx-auto text-xl text-green-100 font-body">
                        {{ __('info_florence_egi.solution.subtitle') }}</p>
                </div>
                <div class="grid gap-8 mb-16 lg:grid-cols-2">
                    <div
                        class="p-8 renaissance-card bg-gradient-to-br from-viola-innovazione/10 to-viola-innovazione/5 text-grigio-pietra">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-viola-innovazione/20">
                                <i class="text-2xl fas fa-gem text-viola-innovazione"></i>
                            </div>
                            <h3 class="text-2xl font-bold renaissance-title">
                                {{ __('info_florence_egi.solution.card_physical_title') }}</h3>
                        </div>
                        <div class="space-y-4 font-body">
                            <p><strong>{{ __('info_florence_egi.solution.card_physical_l1_b') }}</strong>{{ __('info_florence_egi.solution.card_physical_l1') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.solution.card_physical_l2_b') }}</strong>{{ __('info_florence_egi.solution.card_physical_l2') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.solution.card_physical_l3_b') }}</strong>{{ __('info_florence_egi.solution.card_physical_l3') }}
                            </p>
                            <p class="font-semibold text-viola-innovazione">
                                {{ __('info_florence_egi.solution.card_physical_highlight') }}</p>
                        </div>
                    </div>
                    <div
                        class="p-8 renaissance-card bg-gradient-to-br from-blu-algoritmo/10 to-blu-algoritmo/5 text-grigio-pietra">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-blu-algoritmo/20">
                                <i class="text-2xl fas fa-bolt text-blu-algoritmo"></i>
                            </div>
                            <h3 class="text-2xl font-bold renaissance-title">
                                {{ __('info_florence_egi.solution.card_pt_title') }}</h3>
                        </div>
                        <div class="space-y-4 font-body">
                            <p><strong>{{ __('info_florence_egi.solution.card_pt_l1_b') }}</strong>{{ __('info_florence_egi.solution.card_pt_l1') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.solution.card_pt_l2_b') }}</strong>{{ __('info_florence_egi.solution.card_pt_l2') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.solution.card_pt_l3_b') }}</strong>{{ __('info_florence_egi.solution.card_pt_l3') }}
                            </p>
                            <p class="font-semibold text-blu-algoritmo">
                                {{ __('info_florence_egi.solution.card_pt_highlight') }}</p>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <div
                        class="max-w-4xl p-8 mx-auto renaissance-card bg-gradient-to-br from-verde-rinascita/10 to-verde-rinascita/5 text-grigio-pietra">
                        <div class="flex items-center justify-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-verde-rinascita/20">
                                <i class="text-2xl fas fa-infinity text-verde-rinascita"></i>
                            </div>
                            <h3 class="text-2xl font-bold renaissance-title">
                                {{ __('info_florence_egi.solution.infra_title') }}</h3>
                        </div>
                        <div class="grid gap-6 font-body md:grid-cols-3">
                            <div><i class="mb-2 text-3xl fas fa-leaf text-verde-rinascita"></i>
                                <p><strong>{{ __('info_florence_egi.solution.infra_f1_b') }}</strong><br>{{ __('info_florence_egi.solution.infra_f1') }}
                                </p>
                            </div>
                            <div><i class="mb-2 text-3xl fas fa-tachometer-alt text-verde-rinascita"></i>
                                <p><strong>{{ __('info_florence_egi.solution.infra_f2_b') }}</strong><br>{{ __('info_florence_egi.solution.infra_f2') }}
                                </p>
                            </div>
                            <div><i class="mb-2 text-3xl fas fa-shield-alt text-verde-rinascita"></i>
                                <p><strong>{{ __('info_florence_egi.solution.infra_f3_b') }}</strong><br>{{ __('info_florence_egi.solution.infra_f3') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="tecnologia" class="py-16 bg-white sm:py-24"
            aria-label="{{ __('info_florence_egi.tech.aria_label') }}">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title text-grigio-pietra sm:text-4xl md:text-5xl">
                        {!! __('info_florence_egi.tech.title_html') !!}</h2>
                    <p class="max-w-3xl mx-auto text-xl font-body text-grigio-pietra">
                        {{ __('info_florence_egi.tech.subtitle') }}</p>
                </div>
                <div class="max-w-4xl mx-auto space-y-8">
                    <div class="p-6 border-l-4 renaissance-card border-verde-rinascita">
                        <div class="flex items-center mb-4">
                            <div
                                class="flex items-center justify-center w-12 h-12 mr-4 font-bold text-white rounded-full bg-verde-rinascita">
                                1</div>
                            <h3 class="text-xl font-bold renaissance-title text-grigio-pietra">
                                {{ __('info_florence_egi.tech.layer1_title') }}</h3>
                        </div>
                        <p class="font-body text-grigio-pietra">
                            <strong>{{ __('info_florence_egi.tech.layer1_p1_b') }}</strong><br>{{ __('info_florence_egi.tech.layer1_p1') }}
                        </p>
                    </div>
                    <div class="p-6 border-l-4 renaissance-card border-oro-fiorentino">
                        <div class="flex items-center mb-4">
                            <div
                                class="flex items-center justify-center w-12 h-12 mr-4 font-bold text-white rounded-full bg-oro-fiorentino">
                                2</div>
                            <h3 class="text-xl font-bold renaissance-title text-grigio-pietra">
                                {{ __('info_florence_egi.tech.layer2_title') }}</h3>
                        </div>
                        <p class="font-body text-grigio-pietra">
                            <strong>{{ __('info_florence_egi.tech.layer2_p1_b') }}</strong><br>{{ __('info_florence_egi.tech.layer2_p1') }}
                        </p>
                    </div>
                    <div class="p-6 border-l-4 renaissance-card border-viola-innovazione">
                        <div class="flex items-center mb-4">
                            <div
                                class="flex items-center justify-center w-12 h-12 mr-4 font-bold text-white rounded-full bg-viola-innovazione">
                                3</div>
                            <h3 class="text-xl font-bold renaissance-title text-grigio-pietra">
                                {{ __('info_florence_egi.tech.layer3_title') }}</h3>
                        </div>
                        <p class="font-body text-grigio-pietra">
                            <strong>{{ __('info_florence_egi.tech.layer3_p1_b') }}</strong><br>{{ __('info_florence_egi.tech.layer3_p1') }}
                        </p>
                    </div>
                </div>
                <div class="mt-16">
                    <h3 class="mb-8 text-2xl font-bold text-center renaissance-title text-grigio-pietra">
                        {{ __('info_florence_egi.tech.sc_title') }}</h3>
                    <div class="grid gap-8 md:grid-cols-3">
                        <div class="p-6 text-center renaissance-card"><i
                                class="mb-4 text-3xl fas fa-chart-line text-oro-fiorentino"></i>
                            <h4 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                                {{ __('info_florence_egi.tech.sc1_title') }}</h4>
                            <p class="text-sm font-body text-grigio-pietra">
                                {{ __('info_florence_egi.tech.sc1_desc') }}
                            </p>
                        </div>
                        <div class="p-6 text-center renaissance-card"><i
                                class="mb-4 text-3xl fas fa-share-alt text-verde-rinascita"></i>
                            <h4 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                                {{ __('info_florence_egi.tech.sc2_title') }}</h4>
                            <p class="text-sm font-body text-grigio-pietra">
                                {{ __('info_florence_egi.tech.sc2_desc') }}
                            </p>
                        </div>
                        <div class="p-6 text-center renaissance-card"><i
                                class="mb-4 text-3xl fas fa-infinity text-viola-innovazione"></i>
                            <h4 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                                {{ __('info_florence_egi.tech.sc3_title') }}</h4>
                            <p class="text-sm font-body text-grigio-pietra">
                                {{ __('info_florence_egi.tech.sc3_desc') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="coa" class="py-16 text-white section-dark sm:py-24">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title sm:text-4xl md:text-5xl">
                        {!! __('info_florence_egi.coa.title_html') !!}</h2>
                    <p class="max-w-3xl mx-auto text-xl text-green-100 font-body">
                        {{ __('info_florence_egi.coa.subtitle') }}</p>
                </div>
                <div class="grid gap-8 mb-16 lg:grid-cols-2">
                    <div
                        class="p-8 renaissance-card from-oro-fiorentino/10 to-oro-fiorentino/5 bg-gradient-to-br text-grigio-pietra">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-oro-fiorentino/20">
                                <i class="text-2xl fas fa-certificate text-oro-fiorentino"></i>
                            </div>
                            <h3 class="text-2xl font-bold renaissance-title">
                                {{ __('info_florence_egi.coa.card1_title') }}</h3>
                        </div>
                        <div class="space-y-4 font-body">
                            <p><strong>{{ __('info_florence_egi.coa.card1_l1_b') }}</strong>{{ __('info_florence_egi.coa.card1_l1') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.coa.card1_l2_b') }}</strong>{{ __('info_florence_egi.coa.card1_l2') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.coa.card1_l3_b') }}</strong>{{ __('info_florence_egi.coa.card1_l3') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.coa.card1_l4_b') }}</strong>{{ __('info_florence_egi.coa.card1_l4') }}
                            </p>
                            <p class="font-semibold text-oro-fiorentino">
                                {{ __('info_florence_egi.coa.card1_highlight') }}</p>
                        </div>
                    </div>
                    <div
                        class="p-8 renaissance-card bg-gradient-to-br from-blu-algoritmo/10 to-blu-algoritmo/5 text-grigio-pietra">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-blu-algoritmo/20">
                                <i class="text-2xl fas fa-building text-blu-algoritmo"></i>
                            </div>
                            <h3 class="text-2xl font-bold renaissance-title">
                                {{ __('info_florence_egi.coa.card2_title') }}</h3>
                        </div>
                        <div class="space-y-4 font-body">
                            <p><strong>{{ __('info_florence_egi.coa.card2_l1_b') }}</strong>{{ __('info_florence_egi.coa.card2_l1') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.coa.card2_l2_b') }}</strong>{{ __('info_florence_egi.coa.card2_l2') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.coa.card2_l3_b') }}</strong>{{ __('info_florence_egi.coa.card2_l3') }}
                            </p>
                            <p><strong>{{ __('info_florence_egi.coa.card2_l4_b') }}</strong>{{ __('info_florence_egi.coa.card2_l4') }}
                            </p>
                            <p class="font-semibold text-blu-algoritmo">
                                {{ __('info_florence_egi.coa.card2_highlight') }}</p>
                        </div>
                    </div>
                </div>
                <div class="max-w-4xl mx-auto">
                    <h3 class="mb-8 text-2xl font-bold text-center renaissance-title">
                        {{ __('info_florence_egi.coa.workflow_title') }}</h3>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div
                                class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white rounded-full bg-oro-fiorentino">
                                1</div>
                            <div class="font-body">
                                <h4 class="text-lg font-semibold">{{ __('info_florence_egi.coa.workflow1_title') }}
                                </h4>
                                <p class="text-green-100">{{ __('info_florence_egi.coa.workflow1_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div
                                class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white rounded-full bg-oro-fiorentino">
                                2</div>
                            <div class="font-body">
                                <h4 class="text-lg font-semibold">{{ __('info_florence_egi.coa.workflow2_title') }}
                                </h4>
                                <p class="text-green-100">{{ __('info_florence_egi.coa.workflow2_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div
                                class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white rounded-full bg-oro-fiorentino">
                                3</div>
                            <div class="font-body">
                                <h4 class="text-lg font-semibold">{{ __('info_florence_egi.coa.workflow3_title') }}
                                </h4>
                                <p class="text-green-100">{{ __('info_florence_egi.coa.workflow3_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4">
                            <div
                                class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white rounded-full bg-oro-fiorentino">
                                4</div>
                            <div class="font-body">
                                <h4 class="text-lg font-semibold">{{ __('info_florence_egi.coa.workflow4_title') }}
                                </h4>
                                <p class="text-green-100">{{ __('info_florence_egi.coa.workflow4_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="gdpr" class="py-16 text-white section-dark sm:py-24">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title sm:text-4xl md:text-5xl">
                        {!! __('info_florence_egi.gdpr.title_html') !!}</h2>
                    <p class="max-w-4xl mx-auto text-xl text-green-100 font-body">
                        {{ __('info_florence_egi.gdpr.subtitle') }}</p>
                </div>
                <div class="grid gap-8 lg:grid-cols-2">
                    <div
                        class="p-8 renaissance-card elegant-hover bg-gradient-to-br from-blu-algoritmo/20 to-green-800/20">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-oro-fiorentino/20">
                                <i class="text-2xl fas fa-shield-alt text-oro-fiorentino"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.gdpr.card1_title') }}</h3>
                        </div>
                        <div class="space-y-4 text-green-100 font-body">
                            <div class="flex items-start"><i
                                    class="mt-1 mr-3 fas fa-check-circle text-verde-rinascita"></i>
                                <div>
                                    <h4 class="font-semibold text-white">
                                        {{ __('info_florence_egi.gdpr.card1_f1_title') }}</h4>
                                    <p>{{ __('info_florence_egi.gdpr.card1_f1_desc') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start"><i
                                    class="mt-1 mr-3 fas fa-check-circle text-verde-rinascita"></i>
                                <div>
                                    <h4 class="font-semibold text-white">
                                        {{ __('info_florence_egi.gdpr.card1_f2_title') }}</h4>
                                    <p>{{ __('info_florence_egi.gdpr.card1_f2_desc') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start"><i
                                    class="mt-1 mr-3 fas fa-check-circle text-verde-rinascita"></i>
                                <div>
                                    <h4 class="font-semibold text-white">
                                        {{ __('info_florence_egi.gdpr.card1_f3_title') }}</h4>
                                    <p>{{ __('info_florence_egi.gdpr.card1_f3_desc') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="p-8 renaissance-card elegant-hover bg-gradient-to-br from-green-800/20 to-blu-algoritmo/20">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-verde-rinascita/20">
                                <i class="text-2xl fas fa-cogs text-verde-rinascita"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.gdpr.card2_title') }}</h3>
                        </div>
                        <div class="space-y-4 text-green-100 font-body">
                            <div class="flex items-start"><i
                                    class="mt-1 mr-3 fas fa-check-circle text-oro-fiorentino"></i>
                                <div>
                                    <h4 class="font-semibold text-white">
                                        {{ __('info_florence_egi.gdpr.card2_f1_title') }}</h4>
                                    <p>{{ __('info_florence_egi.gdpr.card2_f1_desc') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start"><i
                                    class="mt-1 mr-3 fas fa-check-circle text-oro-fiorentino"></i>
                                <div>
                                    <h4 class="font-semibold text-white">
                                        {{ __('info_florence_egi.gdpr.card2_f2_title') }}</h4>
                                    <p>{{ __('info_florence_egi.gdpr.card2_f2_desc') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start"><i
                                    class="mt-1 mr-3 fas fa-check-circle text-oro-fiorentino"></i>
                                <div>
                                    <h4 class="font-semibold text-white">
                                        {{ __('info_florence_egi.gdpr.card2_f3_title') }}</h4>
                                    <p>{{ __('info_florence_egi.gdpr.card2_f3_desc') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-12">
                    <h3 class="mb-8 text-2xl font-bold text-center renaissance-title">{!! __('info_florence_egi.gdpr.rights_title_html') !!}</h3>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div
                            class="p-6 text-center renaissance-card from-oro-fiorentino/10 to-oro-fiorentino/5 bg-gradient-to-br">
                            <i class="mb-4 text-3xl fas fa-eye text-oro-fiorentino"></i>
                            <h4 class="mb-2 font-bold">{{ __('info_florence_egi.gdpr.right1_title') }}</h4>
                            <p class="text-sm text-green-100">{{ __('info_florence_egi.gdpr.right1_desc') }}</p>
                        </div>
                        <div
                            class="p-6 text-center renaissance-card bg-gradient-to-br from-verde-rinascita/10 to-verde-rinascita/5">
                            <i class="mb-4 text-3xl fas fa-download text-verde-rinascita"></i>
                            <h4 class="mb-2 font-bold">{{ __('info_florence_egi.gdpr.right2_title') }}</h4>
                            <p class="text-sm text-green-100">{{ __('info_florence_egi.gdpr.right2_desc') }}</p>
                        </div>
                        <div
                            class="p-6 text-center renaissance-card bg-gradient-to-br from-rosso-urgenza/10 to-rosso-urgenza/5">
                            <i class="mb-4 text-3xl fas fa-ban text-rosso-urgenza"></i>
                            <h4 class="mb-2 font-bold">{{ __('info_florence_egi.gdpr.right3_title') }}</h4>
                            <p class="text-sm text-green-100">{{ __('info_florence_egi.gdpr.right3_desc') }}</p>
                        </div>
                        <div
                            class="p-6 text-center renaissance-card bg-gradient-to-br from-viola-innovazione/10 to-viola-innovazione/5">
                            <i class="mb-4 text-3xl fas fa-trash-alt text-viola-innovazione"></i>
                            <h4 class="mb-2 font-bold">{{ __('info_florence_egi.gdpr.right4_title') }}</h4>
                            <p class="text-sm text-green-100">{{ __('info_florence_egi.gdpr.right4_desc') }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-12">
                    <div
                        class="max-w-4xl p-8 mx-auto renaissance-card bg-gradient-to-r from-blu-algoritmo/20 to-verde-rinascita/20">
                        <div class="text-center">
                            <h3 class="mb-6 text-2xl font-bold renaissance-title">{!! __('info_florence_egi.gdpr.ultra_title_html') !!}</h3>
                            <p class="mb-6 text-lg text-green-100 font-body">
                                {{ __('info_florence_egi.gdpr.ultra_desc') }}</p>
                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="text-center"><i
                                        class="mb-2 text-2xl fas fa-clipboard-list text-oro-fiorentino"></i>
                                    <h4 class="font-bold">{{ __('info_florence_egi.gdpr.ultra1_title') }}</h4>
                                    <p class="text-sm text-green-100">{{ __('info_florence_egi.gdpr.ultra1_desc') }}
                                    </p>
                                </div>
                                <div class="text-center"><i
                                        class="mb-2 text-2xl fas fa-exclamation-triangle text-verde-rinascita"></i>
                                    <h4 class="font-bold">{{ __('info_florence_egi.gdpr.ultra2_title') }}</h4>
                                    <p class="text-sm text-green-100">{{ __('info_florence_egi.gdpr.ultra2_desc') }}
                                    </p>
                                </div>
                                <div class="text-center"><i
                                        class="mb-2 text-2xl fas fa-chart-line text-rosso-urgenza"></i>
                                    <h4 class="font-bold">{{ __('info_florence_egi.gdpr.ultra3_title') }}</h4>
                                    <p class="text-sm text-green-100">{{ __('info_florence_egi.gdpr.ultra3_desc') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="archetipi" class="py-16 bg-white sm:py-24">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title text-grigio-pietra sm:text-4xl md:text-5xl">
                        {!! __('info_florence_egi.archetypes.title_html') !!}</h2>
                    <p class="max-w-3xl mx-auto text-xl font-body text-grigio-pietra">
                        {{ __('info_florence_egi.archetypes.subtitle') }}</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <a href="{{ route('info.creator') }}"
                        class="block p-6 text-center transition-transform renaissance-card elegant-hover hover:scale-105">
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-viola-innovazione/10">
                            <i class="text-2xl fas fa-brush text-viola-innovazione"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.creator_title') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.creator_desc') }}</p>
                    </a>
                    <a href="{{ route('archetypes.collector') }}"
                        class="block p-6 text-center transition-transform renaissance-card elegant-hover hover:scale-105">
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-oro-fiorentino/10">
                            <i class="text-2xl fas fa-gem text-oro-fiorentino"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.collector_title') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.collector_desc') }}</p>
                    </a>
                    <a href="{{ route('archetypes.patron') }}"
                        class="block p-6 text-center transition-transform renaissance-card elegant-hover hover:scale-105">
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-rosso-urgenza/10">
                            <i class="text-2xl fas fa-heart text-rosso-urgenza"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.patron_title') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.patron_desc') }}</p>
                    </a>
                    <a href="{{ route('info.epp') }}"
                        class="block p-6 text-center transition-transform renaissance-card elegant-hover hover:scale-105">
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-verde-rinascita/10">
                            <i class="text-2xl fas fa-seedling text-verde-rinascita"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.epp_title') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.epp_desc') }}</p>
                    </a>
                    <a href="{{ route('info.aziende') }}"
                        class="block p-6 text-center transition-transform renaissance-card elegant-hover hover:scale-105">
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-blu-algoritmo/10">
                            <i class="text-2xl fas fa-building text-blu-algoritmo"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.companies_title') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.companies_desc') }}</p>
                    </a>
                    <a href="{{ route('archetypes.pa-entity') }}"
                        class="block p-6 text-center transition-transform renaissance-card elegant-hover hover:scale-105">
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-arancio-energia/10">
                            <i class="text-2xl fas fa-university text-arancio-energia"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.pa_title') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.pa_desc') }}
                        </p>
                    </a>
                    <a href="{{ route('info.trader-pro') }}"
                        class="block p-6 text-center transition-transform renaissance-card elegant-hover hover:scale-105">
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-viola-innovazione/10">
                            <i class="text-2xl fas fa-chart-line text-viola-innovazione"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold renaissance-title text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.trader_title') }}</h3>
                        <p class="text-sm font-body text-grigio-pietra">
                            {{ __('info_florence_egi.archetypes.trader_desc') }}</p>
                    </a>
                </div>
            </div>
        </section>

        <section id="impatto" class="py-16 text-white section-dark sm:py-24">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title sm:text-4xl md:text-5xl">
                        {!! __('info_florence_egi.impact.title_html') !!}</h2>
                    <p class="max-w-3xl mx-auto text-xl text-green-100 font-body">
                        {{ __('info_florence_egi.impact.subtitle') }}</p>
                </div>
                <div class="mb-12 text-center">
                    <div
                        class="max-w-4xl p-8 mx-auto renaissance-card from-oro-fiorentino/10 to-oro-fiorentino/5 bg-gradient-to-br text-grigio-pietra">
                        <h3 class="mb-6 text-2xl font-bold renaissance-title">
                            {{ __('info_florence_egi.impact.card_title') }}</h3>
                        <div class="grid gap-6 font-body md:grid-cols-3">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-oro-fiorentino">
                                    {{ __('info_florence_egi.impact.stat1_title') }}</div>
                                <p class="text-sm">{{ __('info_florence_egi.impact.stat1_desc') }}</p>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-verde-rinascita">
                                    {{ __('info_florence_egi.impact.stat2_title') }}</div>
                                <p class="text-sm">{{ __('info_florence_egi.impact.stat2_desc') }}</p>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blu-algoritmo">
                                    {{ __('info_florence_egi.impact.stat3_title') }}</div>
                                <p class="text-sm">{{ __('info_florence_egi.impact.stat3_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid gap-8 mb-16 md:grid-cols-3">
                    <div
                        class="p-8 renaissance-card bg-gradient-to-br from-verde-rinascita/10 to-verde-rinascita/5 text-grigio-pietra">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-verde-rinascita/20">
                                <i class="text-2xl fas fa-tree text-verde-rinascita"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.impact.epp1_title') }}</h3>
                        </div>
                        <div class="space-y-2 font-body">
                            <p><strong>{{ __('info_florence_egi.impact.epp1_l1_b') }}</strong></p>
                            <p>{{ __('info_florence_egi.impact.epp1_l2') }}</p>
                            <p>{{ __('info_florence_egi.impact.epp1_l3') }}</p>
                            <p>{{ __('info_florence_egi.impact.epp1_l4') }}</p>
                            <p class="text-sm text-verde-rinascita"><i
                                    class="mr-1 fas fa-exclamation-triangle"></i>{{ __('info_florence_egi.impact.epp1_highlight') }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="p-8 renaissance-card bg-gradient-to-br from-blu-algoritmo/10 to-blu-algoritmo/5 text-grigio-pietra">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-blu-algoritmo/20">
                                <i class="text-2xl fas fa-water text-blu-algoritmo"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.impact.epp2_title') }}</h3>
                        </div>
                        <div class="space-y-2 font-body">
                            <p><strong>{{ __('info_florence_egi.impact.epp2_l1_b') }}</strong></p>
                            <p>{{ __('info_florence_egi.impact.epp2_l2') }}</p>
                            <p>{{ __('info_florence_egi.impact.epp2_l3') }}</p>
                            <p>{{ __('info_florence_egi.impact.epp2_l4') }}</p>
                            <p class="text-sm text-blu-algoritmo"><i
                                    class="mr-1 fas fa-exclamation-triangle"></i>{{ __('info_florence_egi.impact.epp2_highlight') }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="p-8 renaissance-card from-oro-fiorentino/10 to-oro-fiorentino/5 bg-gradient-to-br text-grigio-pietra">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-oro-fiorentino/20">
                                <i class="text-2xl fas fa-bee text-oro-fiorentino"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.impact.epp3_title') }}</h3>
                        </div>
                        <div class="space-y-2 font-body">
                            <p><strong>{{ __('info_florence_egi.impact.epp3_l1_b') }}</strong></p>
                            <p>{{ __('info_florence_egi.impact.epp3_l2') }}</p>
                            <p>{{ __('info_florence_egi.impact.epp3_l3') }}</p>
                            <p>{{ __('info_florence_egi.impact.epp3_l4') }}</p>
                            <p class="text-sm text-oro-fiorentino"><i
                                    class="mr-1 fas fa-check"></i>{{ __('info_florence_egi.impact.epp3_highlight') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <div
                        class="max-w-4xl p-8 mx-auto renaissance-card bg-gradient-to-br from-viola-innovazione/10 to-viola-innovazione/5 text-grigio-pietra">
                        <h3 class="mb-6 text-2xl font-bold renaissance-title">
                            {{ __('info_florence_egi.impact.growth_title') }}</h3>
                        <div class="grid gap-6 font-body md:grid-cols-2">
                            <div>
                                <h4 class="mb-2 font-bold text-viola-innovazione">
                                    {{ __('info_florence_egi.impact.growth1_title') }}</h4>
                                <p>{{ __('info_florence_egi.impact.growth1_desc') }}</p>
                            </div>
                            <div>
                                <h4 class="mb-2 font-bold text-viola-innovazione">
                                    {{ __('info_florence_egi.impact.growth2_title') }}</h4>
                                <p>{{ __('info_florence_egi.impact.growth2_desc') }}</p>
                            </div>
                        </div>
                        <div class="mt-6"><a href="{{ route('info.epp') }}"
                                class="inline-flex items-center px-6 py-3 text-white transition-all rounded-lg bg-viola-innovazione hover:bg-viola-innovazione/80"><i
                                    class="mr-2 fas fa-seedling"></i> {{ __('info_florence_egi.impact.cta') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="valori" class="py-16 bg-gray-50 sm:py-24">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="mb-12 text-center sm:mb-16">
                    <h2 class="mb-4 text-3xl font-bold renaissance-title text-grigio-pietra sm:text-4xl md:text-5xl">
                        {!! __('info_florence_egi.values.title_html') !!}</h2>
                    <p class="max-w-3xl mx-auto text-xl font-body text-grigio-pietra">
                        {{ __('info_florence_egi.values.subtitle') }}</p>
                </div>
                <div class="grid gap-8 mb-16 md:grid-cols-2 lg:grid-cols-3">
                    <div class="p-8 renaissance-card elegant-hover">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-verde-rinascita/20">
                                <i class="text-2xl fas fa-seedling text-verde-rinascita"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.values.value1_title') }}</h3>
                        </div>
                        <p class="font-body text-grigio-pietra">{{ __('info_florence_egi.values.value1_desc') }}</p>
                    </div>
                    <div class="p-8 renaissance-card elegant-hover">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-viola-innovazione/20">
                                <i class="text-2xl fas fa-palette text-viola-innovazione"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.values.value2_title') }}</h3>
                        </div>
                        <p class="font-body text-grigio-pietra">{{ __('info_florence_egi.values.value2_desc') }}</p>
                    </div>
                    <div class="p-8 renaissance-card elegant-hover">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-oro-fiorentino/20">
                                <i class="text-2xl fas fa-lightbulb text-oro-fiorentino"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.values.value3_title') }}</h3>
                        </div>
                        <p class="font-body text-grigio-pietra">{{ __('info_florence_egi.values.value3_desc') }}</p>
                    </div>
                    <div class="p-8 renaissance-card elegant-hover">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-blu-algoritmo/20">
                                <i class="text-2xl fas fa-shield-alt text-blu-algoritmo"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.values.value4_title') }}</h3>
                        </div>
                        <p class="font-body text-grigio-pietra">{{ __('info_florence_egi.values.value4_desc') }}</p>
                    </div>
                    <div class="p-8 renaissance-card elegant-hover">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-rosso-urgenza/20">
                                <i class="text-2xl fas fa-exchange-alt text-rosso-urgenza"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.values.value5_title') }}</h3>
                        </div>
                        <p class="font-body text-grigio-pietra">{{ __('info_florence_egi.values.value5_desc') }}</p>
                    </div>
                    <div class="p-8 renaissance-card elegant-hover">
                        <div class="flex items-center mb-6">
                            <div
                                class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-arancio-energia/20">
                                <i class="text-2xl fas fa-balance-scale text-arancio-energia"></i>
                            </div>
                            <h3 class="text-xl font-bold renaissance-title">
                                {{ __('info_florence_egi.values.value6_title') }}</h3>
                        </div>
                        <p class="font-body text-grigio-pietra">{{ __('info_florence_egi.values.value6_desc') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="equilibrium"
            class="py-16 from-oro-fiorentino/10 bg-gradient-to-br to-verde-rinascita/10 sm:py-24">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="max-w-6xl mx-auto">
                    <div class="mb-12 text-center">
                        <h2
                            class="mb-4 text-3xl font-bold renaissance-title text-grigio-pietra sm:text-4xl md:text-5xl">
                            {!! __('info_florence_egi.equilibrium.title_html') !!}</h2>
                        <p class="max-w-3xl mx-auto text-xl font-body text-grigio-pietra">
                            {{ __('info_florence_egi.equilibrium.subtitle') }}</p>
                    </div>
                    <div class="grid gap-8 lg:grid-cols-2">
                        <div class="p-8 renaissance-card elegant-hover">
                            <div class="flex items-center mb-6">
                                <div
                                    class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-oro-fiorentino/20">
                                    <i class="text-2xl fas fa-atom text-oro-fiorentino"></i>
                                </div>
                                <h3 class="text-xl font-bold renaissance-title">
                                    {{ __('info_florence_egi.equilibrium.card1_title') }}</h3>
                            </div>
                            <p class="mb-4 font-body text-grigio-pietra">
                                {{ __('info_florence_egi.equilibrium.card1_p1') }}</p>
                            <p class="font-body text-grigio-pietra">
                                {{ __('info_florence_egi.equilibrium.card1_p2') }}
                            </p>
                        </div>
                        <div class="p-8 renaissance-card elegant-hover">
                            <div class="flex items-center mb-6">
                                <div
                                    class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-verde-rinascita/20">
                                    <i class="text-2xl fas fa-cogs text-verde-rinascita"></i>
                                </div>
                                <h3 class="text-xl font-bold renaissance-title">
                                    {{ __('info_florence_egi.equilibrium.card2_title') }}</h3>
                            </div>
                            <p class="mb-4 font-body text-grigio-pietra">
                                {{ __('info_florence_egi.equilibrium.card2_p1') }}</p>
                            <p class="font-body text-grigio-pietra">
                                {{ __('info_florence_egi.equilibrium.card2_p2') }}
                            </p>
                        </div>
                        <div class="p-8 renaissance-card elegant-hover lg:col-span-2">
                            <div class="flex items-center justify-center mb-6">
                                <div
                                    class="flex items-center justify-center w-16 h-16 mr-4 rounded-full bg-blu-algoritmo/20">
                                    <i class="text-2xl fas fa-heart text-blu-algoritmo"></i>
                                </div>
                                <h3 class="text-xl font-bold renaissance-title">
                                    {{ __('info_florence_egi.equilibrium.card3_title') }}</h3>
                            </div>
                            <p class="max-w-4xl mx-auto text-center font-body text-grigio-pietra">
                                {!! __('info_florence_egi.equilibrium.card3_p1') !!}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white sm:py-24">
            <div class="px-4 golden-ratio-container sm:px-6">
                <div class="max-w-4xl mx-auto text-center">
                    <h2 class="mb-6 text-3xl font-bold renaissance-title text-grigio-pietra sm:text-4xl">
                        {!! __('info_florence_egi.cta.title_html') !!}</h2>
                    <p class="mb-8 text-xl font-body text-grigio-pietra">
                        <em>"{{ __('info_florence_egi.cta.quote') }}"</em>
                    </p>
                    <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                        <a href="{{ route('register') }}" aria-label="{{ __('info_florence_egi.cta.cta1_aria') }}"
                            class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white cta-primary elegant-hover rounded-xl"><i
                                class="mr-3 fas fa-rocket"></i> {{ __('info_florence_egi.cta.cta1') }}</a>
                        <a href="{{ route('archetypes.patron') }}"
                            aria-label="{{ __('info_florence_egi.cta.cta2_aria') }}"
                            class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold transition-all border-2 border-oro-fiorentino text-oro-fiorentino hover:bg-oro-fiorentino elegant-hover rounded-xl hover:text-white"><i
                                class="mr-3 fas fa-users"></i> {{ __('info_florence_egi.cta.cta2') }}</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('components.info-footer')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = mobileMenuButton.querySelector('i');
            mobileMenuButton.addEventListener('click', function() {
                if (mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.remove('hidden');
                    menuIcon.className = 'fas fa-times text-2xl';
                } else {
                    mobileMenu.classList.add('hidden');
                    menuIcon.className = 'fas fa-bars text-2xl';
                }
            });
            const mobileMenuLinks = mobileMenu.querySelectorAll('a');
            mobileMenuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                    menuIcon.className = 'fas fa-bars text-2xl';
                });
            });
            document.addEventListener('click', function(event) {
                if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                    menuIcon.className = 'fas fa-bars text-2xl';
                }
            });
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
