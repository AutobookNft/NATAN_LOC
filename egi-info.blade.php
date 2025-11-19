{{--
/**
 * @package Resources\Views\Info
 * @author Padmin D. Curtis (AI Partner OS3.0)
 * @version 2.0.0 (FlorenceEGI - EGI Info Page - Localized)
 * @date 2025-09-29
 * @purpose Pagina informativa completa sugli EGI - localizzata e SEO ottimizzata
 * @brand-compliant Segue FlorenceEGI Brand Guidelines
 * @localization Supporta 6 lingue: IT/EN/ES/PT/FR/DE
 * @seo SEO completo con Schema.org, Open Graph, meta tags
 * @accessibility WCAG 2.1 AA compliant con ARIA labels
 * @silent-growth Ottimizzato per crescita organica internazionale
 */
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('info_egi.page_title') }}</title>
    <meta name="description" content="{{ __('info_egi.page_description') }}">
    <meta name="keywords" content="{{ __('info_egi.meta_keywords') }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <meta name="author" content="FlorenceEGI">
    <meta name="language" content="{{ app()->getLocale() }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph Protocol -->
    <meta property="og:title" content="{{ __('info_egi.page_title') }}">
    <meta property="og:description" content="{{ __('info_egi.page_description') }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="FlorenceEGI">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('info_egi.page_title') }}">
    <meta name="twitter:description" content="{{ __('info_egi.page_description') }}">
    <meta name="twitter:site" content="@FlorenceEGI">

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "{{ __('info_egi.page_title') }}",
        "description": "{{ __('info_egi.page_description') }}",
        "author": {
            "@type": "Organization",
            "@id": "https://florence-egi.com/#organization",
            "name": "FlorenceEGI"
        },
        "publisher": {
            "@type": "Organization",
            "name": "FlorenceEGI",
            "url": "https://florence-egi.com",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ asset('images/logo-florence-egi.png') }}"
            }
        },
        "about": {
            "@type": "Thing",
            "name": "Ecological Goods Invent",
            "description": "Certificati digitali blockchain che garantiscono sostenibilità ambientale"
        },
        "inLanguage": "{{ app()->getLocale() }}",
        "url": "{{ url()->current() }}",
        "datePublished": "2025-09-29",
        "dateModified": "{{ now()->toIso8601String() }}",
        "isPartOf": {
            "@type": "WebSite",
            "@id": "https://florence-egi.com/#website",
            "name": "FlorenceEGI"
        },
        "mainEntity": {
            "@type": "DefinedTerm",
            "name": "EGI - Ecological Goods Invent",
            "description": "Certificati digitali blockchain per sostenibilità ambientale",
            "inDefinedTermSet": "FlorenceEGI Ecosystem"
        }
    }
    </script>

    <!-- Google Fonts - Brand Guidelines -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Brand Colors e Configurazione -->
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

        /* Layout Rinascimentale - Sezione Aurea */
        .golden-ratio-container {
            max-width: 1618px;
            margin: 0 auto;
        }

        /* Animazioni eleganti */
        .elegant-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .elegant-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        /* CTA Oro Fiorentino */
        .cta-primary {
            background: linear-gradient(135deg, #D4A574 0%, #B8956A 100%);
            box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
        }

        .cta-primary:hover {
            box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
            transform: translateY(-1px);
        }

        /* Hero Background */
        .hero-background {
            background: linear-gradient(135deg, rgba(27, 54, 93, 0.95) 0%, rgba(45, 80, 22, 0.85) 100%),
                url('{{ asset('images/default/patron_banner_background_rinascimento_1.png') }}') no-repeat center center/cover;
            min-height: 70vh;
        }

        /* Cards eleganti */
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

        /* Componenti EGI - Cerchi colorati */
        .egi-component {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 0.9rem;
            position: relative;
            margin: 0 auto 1.5rem;
        }

        .egi-epp {
            background: linear-gradient(135deg, #8E44AD 0%, #9B59B6 100%);
        }

        .egi-goods {
            background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
        }

        .egi-creativita {
            background: linear-gradient(135deg, #E91E63 0%, #F06292 100%);
        }

        /* Sezione scura alternata */
        .section-dark {
            background: linear-gradient(135deg, #1B365D 0%, #2D5016 100%);
        }

        /* Schema code blocks */
        .schema-block {
            background: #f8f9fa;
            border-left: 4px solid #D4A574;
            padding: 1rem;
            border-radius: 0 8px 8px 0;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.9rem;
        }
    </style>
</head>

<body class="bg-gray-50 pt-20 text-grigio-pietra">

    <!-- Header con Navigazione - Fixed -->
    <header class="fixed left-0 right-0 top-0 z-50 bg-blu-algoritmo text-white shadow-lg">
        <div class="golden-ratio-container px-4 py-4 sm:px-6 sm:py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <i class="fas fa-leaf text-oro-fiorentino text-3xl sm:text-4xl"></i>
                    <div>
                        <h1 class="renaissance-title text-xl font-bold sm:text-2xl">EGI Info</h1>
                        <p class="font-body text-sm text-blue-200 sm:text-base">Ecological Goods Invent</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden space-x-3 md:flex" aria-label="{{ __('info_egi.aria_navigation') }}">
                    <a href="{{ route('home') }}"
                        class="hover:text-oro-fiorentino font-body text-sm transition lg:text-base">{{ __('info_egi.nav_home') }}</a>
                    <a href="{{ route('info.florence-egi') }}"
                        class="hover:text-oro-fiorentino font-body text-sm transition lg:text-base">FlorenceEGI</a>
                    <a href="#definizione"
                        class="hover:text-oro-fiorentino font-body text-sm transition lg:text-base">{{ __('info_egi.nav_egi_definition') }}</a>
                    <a href="#componenti"
                        class="hover:text-oro-fiorentino font-body text-sm transition lg:text-base">{{ __('info_egi.nav_components') }}</a>
                    <a href="#funzioni"
                        class="hover:text-oro-fiorentino font-body text-sm transition lg:text-base">{{ __('info_egi.nav_functions') }}</a>
                    <a href="#vantaggi"
                        class="hover:text-oro-fiorentino font-body text-sm transition lg:text-base">{{ __('info_egi.nav_advantages') }}</a>
                </nav>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button"
                    class="block rounded-md p-2 transition-colors hover:bg-blue-700 md:hidden"
                    aria-label="{{ __('info_egi.aria_mobile_menu') }}" aria-expanded="false">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobile-menu" class="mt-4 hidden border-t border-blue-600 pb-4 md:hidden" role="navigation"
                aria-label="{{ __('info_egi.aria_mobile_navigation') }}">
                <div class="space-y-3 pt-4">
                    <a href="{{ route('home') }}"
                        class="flex items-center rounded-md px-4 py-2 text-sm transition-colors hover:bg-blue-700">
                        <i class="fas fa-home text-oro-fiorentino mr-3 text-lg" aria-hidden="true"></i>
                        {{ __('info_egi.mobile_nav_home') }}
                    </a>
                    <a href="{{ route('info.florence-egi') }}"
                        class="flex items-center rounded-md px-4 py-2 text-sm transition-colors hover:bg-blue-700">
                        <i class="fas fa-infinity text-oro-fiorentino mr-3 text-lg" aria-hidden="true"></i>
                        FlorenceEGI
                    </a>
                    <a href="#definizione"
                        class="flex items-center rounded-md px-4 py-2 text-sm transition-colors hover:bg-blue-700">
                        <i class="fas fa-book text-oro-fiorentino mr-3 text-lg" aria-hidden="true"></i>
                        {{ __('info_egi.nav_egi_definition') }}
                    </a>
                    <a href="#componenti"
                        class="flex items-center rounded-md px-4 py-2 text-sm transition-colors hover:bg-blue-700">
                        <i class="fas fa-puzzle-piece text-oro-fiorentino mr-3 text-lg" aria-hidden="true"></i>
                        {{ __('info_egi.nav_components') }}
                    </a>
                    <a href="#funzioni"
                        class="flex items-center rounded-md px-4 py-2 text-sm transition-colors hover:bg-blue-700">
                        <i class="fas fa-cogs text-oro-fiorentino mr-3 text-lg" aria-hidden="true"></i>
                        {{ __('info_egi.nav_functions') }}
                    </a>
                    <a href="#vantaggi"
                        class="flex items-center rounded-md px-4 py-2 text-sm transition-colors hover:bg-blue-700">
                        <i class="fas fa-star text-oro-fiorentino mr-3 text-lg" aria-hidden="true"></i>
                        {{ __('info_egi.nav_advantages') }}
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-background" role="banner" aria-labelledby="hero-title">
        <div class="golden-ratio-container flex min-h-[70vh] items-center justify-center px-4">
            <div class="mx-auto max-w-5xl text-center text-white">
                <div class="mb-8">
                    <span class="bg-oro-fiorentino rounded-full px-4 py-2 text-sm font-semibold text-blu-algoritmo"
                        aria-label="{{ __('info_egi.hero_badge_aria') }}">
                        {{ __('info_egi.hero_badge') }}
                    </span>
                </div>
                <h1 id="hero-title" class="renaissance-title mb-6 text-4xl font-bold sm:text-5xl lg:text-7xl">
                    {{ __('info_egi.hero_title') }}
                </h1>
                <p class="mb-8 font-body text-xl leading-relaxed text-blue-100 sm:text-2xl">
                    {{ __('info_egi.hero_subtitle') }}
                </p>
                <p class="mb-10 font-body text-lg leading-relaxed text-blue-200">
                    {!! __('info_egi.hero_description') !!}
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-center" role="group"
                    aria-label="{{ __('info_egi.cta_group_aria') }}">
                    <a href="#definizione"
                        class="cta-primary elegant-hover inline-flex items-center rounded-lg px-8 py-4 text-lg font-semibold text-white transition-all duration-300"
                        aria-describedby="cta-discover-desc">
                        <i class="fas fa-book-open mr-3" aria-hidden="true"></i>
                        {{ __('info_egi.cta_discover') }}
                        <span id="cta-discover-desc" class="sr-only">{{ __('info_egi.cta_discover_aria') }}</span>
                    </a>
                    <a href="#componenti"
                        class="inline-flex items-center rounded-lg border-2 border-white px-8 py-4 text-lg font-semibold text-blue-100 transition-all duration-300 hover:bg-white hover:text-blu-algoritmo"
                        aria-describedby="cta-components-desc">
                        <i class="fas fa-puzzle-piece mr-3" aria-hidden="true"></i>
                        {{ __('info_egi.cta_components') }}
                        <span id="cta-components-desc"
                            class="sr-only">{{ __('info_egi.cta_components_aria') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Definizione EGI -->
    <section id="definizione" class="bg-white py-20" aria-labelledby="definition-title">
        <div class="golden-ratio-container px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <h2 id="definition-title"
                    class="renaissance-title mb-6 text-3xl font-bold text-blu-algoritmo sm:text-4xl">
                    {{ __('info_egi.definition_title') }}
                </h2>
                <p class="mb-12 font-body text-xl leading-relaxed text-grigio-pietra">
                    {!! __('info_egi.definition_intro') !!}
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4" role="group"
                aria-labelledby="definition-cards-title">
                <h3 id="definition-cards-title" class="sr-only">{{ __('info_egi.definition_cards_title') }}</h3>

                <article class="renaissance-card elegant-hover p-6" aria-labelledby="card1-title">
                    <div class="mb-4 text-center">
                        <i class="text-oro-fiorentino fas fa-palette text-4xl" aria-hidden="true"></i>
                    </div>
                    <h4 id="card1-title"
                        class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.card_digital_art_title') }}
                    </h4>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.card_digital_art_description') }}
                    </p>
                </article>

                <article class="renaissance-card elegant-hover p-6" aria-labelledby="card2-title">
                    <div class="mb-4 text-center">
                        <i class="fas fa-leaf text-4xl text-verde-rinascita" aria-hidden="true"></i>
                    </div>
                    <h4 id="card2-title"
                        class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.card_environmental_title') }}
                    </h4>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.card_environmental_description') }}
                    </p>
                </article>

                <article class="renaissance-card elegant-hover p-6" aria-labelledby="card3-title">
                    <div class="mb-4 text-center">
                        <i class="fas fa-cog text-4xl text-viola-innovazione" aria-hidden="true"></i>
                    </div>
                    <h4 id="card3-title"
                        class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.card_utilities_title') }}
                    </h4>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.card_utilities_description') }}
                    </p>
                </article>

                <article class="renaissance-card elegant-hover p-6" aria-labelledby="card4-title">
                    <div class="mb-4 text-center">
                        <i class="fas fa-shield-alt text-4xl text-blu-algoritmo" aria-hidden="true"></i>
                    </div>
                    <h4 id="card4-title"
                        class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.card_certification_title') }}
                    </h4>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.card_certification_description') }}
                    </p>
                </article>
            </div>
        </div>
    </section>

    <!-- Le Tre Componenti -->
    <section id="componenti" class="section-dark py-20" aria-labelledby="components-title">
        <div class="golden-ratio-container px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-16 max-w-4xl text-center">
                <h2 id="components-title" class="renaissance-title mb-6 text-3xl font-bold text-white sm:text-4xl">
                    {{ __('info_egi.components_title') }}
                </h2>
                <p class="font-body text-xl leading-relaxed text-blue-200">
                    {{ __('info_egi.components_intro') }}
                </p>
            </div>

            <!-- EPP - Environment Protection Programs -->
            <article class="mb-20" aria-labelledby="epp-title">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="egi-component egi-epp" aria-label="{{ __('info_egi.epp_component_aria') }}">
                            <div>
                                <div class="text-2xl font-bold">{{ __('info_egi.component_epp_title') }}</div>
                                <div class="text-xs">{{ __('info_egi.epp_acronym') }}</div>
                            </div>
                        </div>
                        <h3 id="epp-title" class="text-oro-fiorentino renaissance-title mb-4 text-2xl font-bold">
                            {{ __('info_egi.component_epp_full') }}
                        </h3>
                        <p class="mb-6 font-body text-lg leading-relaxed text-blue-100">
                            {{ __('info_egi.epp_description') }}
                        </p>
                    </div>

                    <div class="renaissance-card rounded-lg bg-white p-8">
                        <h4 class="renaissance-title mb-4 text-xl font-bold text-blu-algoritmo">
                            {{ __('info_egi.epp_active_programs') }}
                        </h4>
                        <div class="space-y-4" role="list"
                            aria-label="{{ __('info_egi.epp_programs_list_aria') }}">
                            <div class="border-l-4 border-viola-innovazione bg-purple-50 p-4" role="listitem">
                                <h5 class="font-semibold text-blu-algoritmo">{{ __('info_egi.epp_aquatic_title') }}
                                </h5>
                                <p class="text-sm text-grigio-pietra">
                                    {{ __('info_egi.epp_aquatic_description') }}
                                </p>
                            </div>
                            <div class="border-l-4 border-verde-rinascita bg-green-50 p-4" role="listitem">
                                <h5 class="font-semibold text-blu-algoritmo">{{ __('info_egi.epp_forestry_title') }}
                                </h5>
                                <p class="text-sm text-grigio-pietra">
                                    {{ __('info_egi.epp_forestry_description') }}
                                </p>
                            </div>
                            <div class="border-l-4 border-arancio-energia bg-orange-50 p-4" role="listitem">
                                <h5 class="font-semibold text-blu-algoritmo">{{ __('info_egi.epp_bee_title') }}</h5>
                                <p class="text-sm text-grigio-pietra">
                                    {{ __('info_egi.epp_bee_description') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- GOODS -->
            <article class="mb-20" aria-labelledby="goods-title">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div class="order-2 text-center lg:order-1 lg:text-left">
                        <div class="renaissance-card rounded-lg bg-white p-8">
                            <h4 class="renaissance-title mb-4 text-xl font-bold text-blu-algoritmo">
                                {{ __('info_egi.goods_flexibility_title') }}
                            </h4>
                            <p class="mb-4 font-body text-grigio-pietra">
                                {{ __('info_egi.goods_flexibility_intro') }}
                            </p>
                            <p class="mb-4 font-body text-grigio-pietra">
                                {{ __('info_egi.goods_flexibility_description') }}
                            </p>
                            <p class="font-body text-grigio-pietra">
                                {{ __('info_egi.goods_marketing_potential') }}
                            </p>
                        </div>
                    </div>

                    <div class="order-1 text-center lg:order-2 lg:text-right">
                        <div class="egi-component egi-goods" aria-label="{{ __('info_egi.goods_component_aria') }}">
                            <div>
                                <div class="text-2xl font-bold">{{ __('info_egi.component_goods_title') }}</div>
                                <div class="text-xs">{{ __('info_egi.goods_acronym') }}</div>
                            </div>
                        </div>
                        <h3 id="goods-title" class="text-oro-fiorentino renaissance-title mb-4 text-2xl font-bold">
                            {{ __('info_egi.component_goods_full') }}
                        </h3>
                        <p class="font-body text-lg leading-relaxed text-blue-100">
                            {{ __('info_egi.goods_description') }}
                        </p>
                    </div>
                </div>
            </article>

            <!-- CREATIVITÀ E INVENTIVA -->
            <article aria-labelledby="creativity-title">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div class="text-center lg:text-left">
                        <div class="egi-component egi-creativita"
                            aria-label="{{ __('info_egi.creativity_component_aria') }}">
                            <div>
                                <div class="text-xl font-bold">{{ __('info_egi.component_creativita_title') }}</div>
                                <div class="text-xs">{{ __('info_egi.creativity_acronym') }}</div>
                            </div>
                        </div>
                        <h3 id="creativity-title"
                            class="text-oro-fiorentino renaissance-title mb-4 text-2xl font-bold">
                            {{ __('info_egi.component_creativita_full') }}
                        </h3>
                        <p class="font-body text-lg leading-relaxed text-blue-100">
                            {{ __('info_egi.creativity_description') }}
                        </p>
                    </div>

                    <div class="renaissance-card rounded-lg bg-white p-8">
                        <h4 class="renaissance-title mb-4 text-xl font-bold text-blu-algoritmo">
                            {{ __('info_egi.creativity_beyond_art_title') }}
                        </h4>
                        <p class="mb-4 font-body text-grigio-pietra">
                            {!! __('info_egi.creativity_beyond_art_intro') !!}
                        </p>
                        <div class="border-oro-fiorentino mb-4 border-l-4 bg-blue-50 p-4" role="complementary"
                            aria-labelledby="egi-types-title">
                            <h5 id="egi-types-title" class="mb-2 font-semibold text-blu-algoritmo">
                                {{ __('info_egi.egi_types_title') }}</h5>
                            <ul class="space-y-1 text-sm text-grigio-pietra" role="list">
                                <li role="listitem">{!! __('info_egi.egi_type_artwork') !!}</li>
                                <li role="listitem">{!! __('info_egi.egi_type_document') !!}</li>
                                <li role="listitem">{!! __('info_egi.egi_type_utility') !!}</li>
                                <li role="listitem">{!! __('info_egi.egi_type_token') !!}</li>
                                <li role="listitem">{!! __('info_egi.egi_type_collectible') !!}</li>
                                <li role="listitem">{!! __('info_egi.egi_type_narrative') !!}</li>
                            </ul>
                        </div>
                        <p class="mb-4 font-body text-grigio-pietra">
                            {!! __('info_egi.creativity_goods_explanation') !!}
                        </p>
                    </div>
                </div>
        </div>
        </div>
    </section>

    <!-- Struttura Tecnica -->
    <section id="funzioni" class="bg-gray-100 py-20" aria-labelledby="functions-title">
        <div class="golden-ratio-container px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-16 max-w-4xl text-center">
                <h2 id="functions-title"
                    class="renaissance-title mb-6 text-3xl font-bold text-blu-algoritmo sm:text-4xl">
                    {{ __('info_egi.functions_title') }}
                </h2>
                <p class="font-body text-xl leading-relaxed text-grigio-pietra">
                    {{ __('info_egi.functions_intro') }}
                </p>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <!-- Struttura -->
                <article class="renaissance-card elegant-hover rounded-lg bg-white p-8"
                    aria-labelledby="structure-title">
                    <h3 id="structure-title" class="renaissance-title mb-6 text-2xl font-bold text-blu-algoritmo">
                        <i class="text-oro-fiorentino fas fa-layer-group mr-3" aria-hidden="true"></i>
                        {{ __('info_egi.structure_title') }}
                    </h3>
                    <div class="space-y-4" role="list"
                        aria-label="{{ __('info_egi.structure_components_aria') }}">
                        <div class="schema-block" role="listitem">
                            <strong>{{ __('info_egi.structure_invent') }}:</strong>
                            {{ __('info_egi.structure_invent_desc') }}
                        </div>
                        <div class="schema-block" role="listitem">
                            <strong>{{ __('info_egi.structure_good') }}:</strong>
                            {{ __('info_egi.structure_good_desc') }}
                        </div>
                        <div class="schema-block" role="listitem">
                            <strong>{{ __('info_egi.structure_epp') }}:</strong>
                            {{ __('info_egi.structure_epp_desc') }}
                        </div>
                        <div class="schema-block" role="listitem">
                            <strong>{{ __('info_egi.structure_metadata') }}:</strong>
                            {{ __('info_egi.structure_metadata_desc') }}
                        </div>
                        <div class="schema-block" role="listitem">
                            <strong>{{ __('info_egi.structure_coa') }}:</strong>
                            {{ __('info_egi.structure_coa_desc') }}
                        </div>
                        <div class="schema-block" role="listitem">
                            <strong>{{ __('info_egi.structure_smart_contract') }}:</strong>
                            {{ __('info_egi.structure_smart_contract_desc') }}
                        </div>
                    </div>
                </article>

                <!-- Funzioni -->
                <article class="renaissance-card elegant-hover rounded-lg bg-white p-8"
                    aria-labelledby="functions-advanced-title">
                    <h3 id="functions-advanced-title"
                        class="renaissance-title mb-6 text-2xl font-bold text-blu-algoritmo">
                        <i class="text-oro-fiorentino fas fa-cogs mr-3" aria-hidden="true"></i>
                        {{ __('info_egi.functions_advanced_title') }}
                    </h3>
                    <div class="space-y-6" role="list" aria-label="{{ __('info_egi.functions_list_aria') }}">
                        <div role="listitem">
                            <h4 class="mb-2 font-semibold text-verde-rinascita">
                                {{ __('info_egi.function_rebind_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">{{ __('info_egi.function_rebind_desc') }}</p>
                        </div>
                        <div role="listitem">
                            <h4 class="mb-2 font-semibold text-verde-rinascita">
                                {{ __('info_egi.function_tokenization_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">{{ __('info_egi.function_tokenization_desc') }}</p>
                        </div>
                        <div role="listitem">
                            <h4 class="mb-2 font-semibold text-verde-rinascita">
                                {{ __('info_egi.function_multirole_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">{{ __('info_egi.function_multirole_desc') }}</p>
                        </div>
                        <div role="listitem">
                            <h4 class="mb-2 font-semibold text-verde-rinascita">
                                {{ __('info_egi.function_interface_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">{{ __('info_egi.function_interface_desc') }}</p>
                        </div>
                        <div role="listitem">
                            <h4 class="mb-2 font-semibold text-verde-rinascita">
                                {{ __('info_egi.function_signature_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">{{ __('info_egi.function_signature_desc') }}</p>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Traits e Utilities: La Potenza Nascosta -->
    <section id="traits-utilities" class="bg-gradient-to-br from-blue-50 to-green-50 py-20">
        <div class="golden-ratio-container px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-16 max-w-4xl text-center">
                <h2 class="renaissance-title mb-6 text-3xl font-bold text-blu-algoritmo sm:text-4xl">
                    {{ __('info_egi.traits_utilities_title') }}
                </h2>
                <p class="font-body text-xl leading-relaxed text-grigio-pietra">
                    {{ __('info_egi.traits_utilities_subtitle') }}
                </p>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Due Tipologie di Traits -->
                <div class="renaissance-card elegant-hover rounded-lg bg-white p-8">
                    <div class="mb-6 text-center">
                        <i class="fas fa-tags text-oro-fiorentino text-4xl"></i>
                    </div>
                    <h3 class="renaissance-title mb-4 text-center text-xl font-bold text-blu-algoritmo">
                        {{ __('info_egi.traits_dual_system_title') }}
                    </h3>
                    <div class="space-y-4">
                        <div class="border-l-4 border-purple-400 bg-purple-50 p-4">
                            <h4 class="mb-2 font-semibold text-purple-700">{{ __('info_egi.traits_egi_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">
                                {{ __('info_egi.traits_egi_desc') }}
                            </p>
                        </div>
                        <div class="border-l-4 border-orange-400 bg-orange-50 p-4">
                            <h4 class="mb-2 font-semibold text-orange-700">{{ __('info_egi.traits_coa_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">
                                {{ __('info_egi.traits_coa_desc') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Utilities Complete -->
                <div class="renaissance-card elegant-hover rounded-lg bg-white p-8">
                    <div class="mb-6 text-center">
                        <i class="fas fa-tools text-oro-fiorentino text-4xl"></i>
                    </div>
                    <h3 class="renaissance-title mb-4 text-center text-xl font-bold text-blu-algoritmo">
                        {{ __('info_egi.utilities_advanced_title') }}
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <h4 class="mb-2 font-semibold text-verde-rinascita">
                                {{ __('info_egi.utilities_physical_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">
                                {{ __('info_egi.utilities_physical_desc') }}
                            </p>
                        </div>
                        <div>
                            <h4 class="mb-2 font-semibold text-verde-rinascita">
                                {{ __('info_egi.utilities_temporal_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">
                                {{ __('info_egi.utilities_temporal_desc') }}
                            </p>
                        </div>
                        <div>
                            <h4 class="mb-2 font-semibold text-verde-rinascita">
                                {{ __('info_egi.utilities_documentation_title') }}</h4>
                            <p class="text-sm text-grigio-pietra">
                                {{ __('info_egi.utilities_documentation_desc') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Beyond NFTs -->
                <div class="renaissance-card elegant-hover rounded-lg bg-white p-8">
                    <div class="mb-6 text-center">
                        <i class="fas fa-rocket text-oro-fiorentino text-4xl"></i>
                    </div>
                    <h3 class="renaissance-title mb-4 text-center text-xl font-bold text-blu-algoritmo">
                        {{ __('info_egi.beyond_nft_title') }}
                    </h3>
                    <div class="space-y-4 text-sm text-grigio-pietra">
                        <p>
                            {!! __('info_egi.beyond_nft_blockchain') !!}
                        </p>
                        <p>
                            {!! __('info_egi.beyond_nft_value') !!}
                        </p>
                        <p>
                            {!! __('info_egi.beyond_nft_impact') !!}
                        </p>
                        <p>
                            {!! __('info_egi.beyond_nft_management') !!}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="mx-auto mt-12 max-w-3xl text-center">
                <div
                    class="from-oro-fiorentino border-oro-fiorentino rounded-lg border-2 bg-gradient-to-r to-yellow-400 p-8">
                    <h3 class="renaissance-title mb-4 text-2xl font-bold text-white">
                        {{ __('info_egi.cta_philosophy_title') }}
                    </h3>
                    <p class="mb-6 font-body text-lg text-white">
                        {!! __('info_egi.cta_philosophy_desc') !!}
                    </p>
                    <div class="flex flex-wrap justify-center gap-4 text-sm">
                        <span
                            class="rounded-full bg-white bg-opacity-20 px-4 py-2">{{ __('info_egi.cta_tag_art') }}</span>
                        <span
                            class="rounded-full bg-white bg-opacity-20 px-4 py-2">{{ __('info_egi.cta_tag_documents') }}</span>
                        <span
                            class="rounded-full bg-white bg-opacity-20 px-4 py-2">{{ __('info_egi.cta_tag_utilities') }}</span>
                        <span
                            class="rounded-full bg-white bg-opacity-20 px-4 py-2">{{ __('info_egi.cta_tag_tokens') }}</span>
                        <span
                            class="rounded-full bg-white bg-opacity-20 px-4 py-2">{{ __('info_egi.cta_tag_impact') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Citazione Padmin D. Curtis -->
    <section class="bg-gradient-to-r from-blu-algoritmo via-verde-rinascita to-blu-algoritmo py-16">
        <div class="golden-ratio-container px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <div class="relative">
                    <!-- Quote Icon -->
                    <div class="text-oro-fiorentino absolute left-0 top-0 text-6xl opacity-20">
                        <i class="fas fa-quote-left"></i>
                    </div>

                    <!-- Citation Content -->
                    <div class="relative z-10 px-8 py-12">
                        <blockquote
                            class="renaissance-title mb-8 text-2xl font-medium leading-relaxed text-white sm:text-3xl lg:text-4xl">
                            {!! __('info_egi.curtis_quote') !!}
                        </blockquote>

                        <!-- Attribution -->
                        <footer class="font-body text-xl text-blue-200">
                            <span class="text-oro-fiorentino">—</span>
                            <cite
                                class="text-oro-fiorentino font-semibold not-italic">{{ __('info_egi.curtis_attribution') }}</cite>
                            <div class="mt-2 text-sm text-blue-300">
                                {{ __('info_egi.curtis_role') }}
                            </div>
                        </footer>
                    </div>

                    <!-- Decorative Element -->
                    <div class="text-oro-fiorentino absolute bottom-0 right-0 text-6xl opacity-20">
                        <i class="fas fa-quote-right"></i>
                    </div>
                </div>

                <!-- Subtle separator -->
                <div class="mt-8 flex items-center justify-center">
                    <div class="bg-oro-fiorentino h-px w-20"></div>
                    <div class="text-oro-fiorentino mx-4">
                        <i class="fas fa-infinity"></i>
                    </div>
                    <div class="bg-oro-fiorentino h-px w-20"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Finalità e Vantaggi -->
    <section id="vantaggi" class="bg-white py-20">
        <div class="golden-ratio-container px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-16 max-w-4xl text-center">
                <h2 class="renaissance-title mb-6 text-3xl font-bold text-blu-algoritmo sm:text-4xl">
                    {{ __('info_egi.advantages_title') }}
                </h2>
                <p class="font-body text-xl leading-relaxed text-grigio-pietra">
                    {{ __('info_egi.advantages_subtitle') }}
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div class="renaissance-card elegant-hover p-6">
                    <div class="mb-4 text-center">
                        <i class="fas fa-seedling text-4xl text-verde-rinascita"></i>
                    </div>
                    <h3 class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.advantage_nft_utility_title') }}
                    </h3>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.advantage_nft_utility_desc') }}
                    </p>
                </div>

                <div class="renaissance-card elegant-hover p-6">
                    <div class="mb-4 text-center">
                        <i class="text-oro-fiorentino fas fa-certificate text-4xl"></i>
                    </div>
                    <h3 class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.advantage_certification_title') }}
                    </h3>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.advantage_certification_desc') }}
                    </p>
                </div>

                <div class="renaissance-card elegant-hover p-6">
                    <div class="mb-4 text-center">
                        <i class="fas fa-tools text-4xl text-viola-innovazione"></i>
                    </div>
                    <h3 class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.advantage_operational_title') }}
                    </h3>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.advantage_operational_desc') }}
                    </p>
                </div>

                <div class="renaissance-card elegant-hover p-6">
                    <div class="mb-4 text-center">
                        <i class="fas fa-book-open text-4xl text-arancio-energia"></i>
                    </div>
                    <h3 class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.advantage_narrative_title') }}
                    </h3>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.advantage_narrative_desc') }}
                    </p>
                </div>

                <div class="renaissance-card elegant-hover p-6">
                    <div class="mb-4 text-center">
                        <i class="fas fa-shield-alt text-4xl text-rosso-urgenza"></i>
                    </div>
                    <h3 class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.advantage_standard_title') }}
                    </h3>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.advantage_standard_desc') }}
                    </p>
                </div>

                <div class="renaissance-card elegant-hover p-6">
                    <div class="mb-4 text-center">
                        <i class="fas fa-infinity text-4xl text-blu-algoritmo"></i>
                    </div>
                    <h3 class="renaissance-title mb-3 text-center text-lg font-bold text-blu-algoritmo">
                        {{ __('info_egi.advantage_traceable_title') }}
                    </h3>
                    <p class="text-center font-body text-grigio-pietra">
                        {{ __('info_egi.advantage_traceable_desc') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Finale -->
    <section class="section-dark py-20">
        <div class="golden-ratio-container px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <h2 class="text-oro-fiorentino renaissance-title mb-6 text-3xl font-bold sm:text-4xl">
                    {{ __('info_egi.final_cta_title') }}
                </h2>
                <p class="mb-8 font-body text-xl leading-relaxed text-blue-100">
                    {!! __('info_egi.final_cta_subtitle') !!}
                </p>
                <p class="renaissance-title mb-12 text-2xl font-bold text-white">
                    {!! __('info_egi.final_cta_standard') !!}
                </p>

                <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <a href="{{ route('home') }}"
                        class="cta-primary elegant-hover inline-flex items-center rounded-lg px-8 py-4 text-lg font-semibold text-blu-algoritmo transition-all duration-300">
                        <i class="fas fa-rocket mr-3"></i>
                        {{ __('info_egi.final_cta_start') }}
                    </a>
                    <a href="{{ route('info.florence-egi') }}"
                        class="border-oro-fiorentino text-oro-fiorentino hover:bg-oro-fiorentino inline-flex items-center rounded-lg border-2 px-8 py-4 text-lg font-semibold transition-all duration-300 hover:text-blu-algoritmo">
                        <i class="fas fa-info-circle mr-3"></i>
                        {{ __('info_egi.final_cta_discover') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    @include('components.info-footer')

    <!-- Mobile Menu Script -->
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

            // Close menu when clicking on a link
            const mobileMenuLinks = mobileMenu.querySelectorAll('a');
            mobileMenuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                    menuIcon.className = 'fas fa-bars text-2xl';
                });
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                    menuIcon.className = 'fas fa-bars text-2xl';
                }
            });

            // Smooth scrolling per i link interni
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
