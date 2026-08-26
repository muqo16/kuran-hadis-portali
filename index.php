<?php
require_once __DIR__ . '/db.php';

$surahs = QuranDB::getSurahs();
$stats = QuranDB::getStats();

// URL parametreleri
$initialQuery = $_GET['q'] ?? '';
$initialSurah = isset($_GET['sure']) ? (int)$_GET['sure'] : 0;
$initialAyah = isset($_GET['ayet']) ? (int)$_GET['ayet'] : 0;
$initialView = $_GET['view'] ?? ($initialSurah > 0 ? 'reader' : 'search');
?>
<!DOCTYPE html>
<html lang="tr" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kur'an-ı Kerim Meali & Akıllı Arama Portalı</title>
    <meta name="description" content="Kur'an-ı Kerim mealleri, karşılaştırmalı mealler, Arapça orijinal metin, sesli dinleme, Sahih Hadisler ve Cuma & özel gün WhatsApp paylaşımları.">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        cream: {
                            50: '#fdfbf7',
                            100: '#faf6ee',
                            200: '#f4ede0',
                            300: '#ede2cf',
                            400: '#ded0b6',
                            500: '#cca97e',
                            800: '#604c38',
                            900: '#382a1e'
                        },
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22'
                        },
                        amber: {
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309'
                        }
                    },
                    fontFamily: {
                        quran: ['"Amiri Quran"', '"Scheherazade New"', 'Amiri', 'serif'],
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri+Quran&family=Amiri:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600;700;800&family=Cinzel:wght@600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .arabic-text {
            font-family: 'Amiri Quran', 'Amiri', serif;
            line-height: 2.4;
            direction: rtl;
            text-align: right;
            word-spacing: 4px;
        }
        .highlight {
            background-color: #fef08a;
            color: #854d0e;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            border-bottom: 2px solid #eab308;
        }
        .dark .highlight {
            background-color: rgba(245, 158, 11, 0.35);
            color: #fef08a;
            border-bottom: 2px solid #f59e0b;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.04);
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .dark ::-webkit-scrollbar-track {
            background: rgba(17, 24, 39, 0.4);
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* =========================================================================
           FERAHLATICI VE HUZUR VERİCİ KREM TEMA (GÖZÜ YORMAYAN PREMIUM AÇIK TEMA)
           ========================================================================= */
        html.light body {
            background-color: #fbf8f1 !important;
            color: #27272a !important;
        }
        html.light header {
            background-color: #ffffff !important;
            border-color: #eaddc7 !important;
            box-shadow: 0 4px 20px -2px rgba(80, 55, 20, 0.05) !important;
        }
        html.light header .border-b {
            border-color: #f2ebd9 !important;
        }
        html.light header h1, html.light h2, html.light h3, html.light h4 {
            color: #18181b !important;
        }
        html.light header p {
            color: #52525b !important;
        }
        
        /* Navigasyon Butonları */
        html.light .nav-btn {
            background-color: #f4eee2 !important;
            border: 1px solid #e5d9c3 !important;
            color: #443729 !important;
        }
        html.light .nav-btn:hover {
            background-color: #ede3d0 !important;
            color: #047857 !important;
            border-color: #a7f3d0 !important;
        }
        html.light .nav-btn.bg-emerald-600,
        html.light .nav-btn.active {
            background-color: #047857 !important;
            border-color: #047857 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(4, 120, 87, 0.25) !important;
        }
        html.light .nav-btn span.font-mono,
        html.light .nav-btn span.font-bold {
            background-color: #e8ddc7 !important;
            color: #382a1d !important;
        }
        html.light .nav-btn.bg-emerald-600 span.font-mono,
        html.light .nav-btn.bg-emerald-600 span.font-bold {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }

        /* Kartlar (Ayet, Hadis, Sure, Tebrik, Arama Kutuları) */
        html.light .ayah-card,
        html.light .hadith-card,
        html.light .surah-card,
        html.light .greetings-card,
        html.light #view-search > div:first-child,
        html.light #view-hadiths > div:first-child,
        html.light #view-surahs > div:first-child,
        html.light #view-juz > div:first-child,
        html.light #view-pages > div:first-child,
        html.light #view-favorites > div:first-child,
        html.light #view-sajdah > div:first-child,
        html.light #view-topics > div:first-child,
        html.light #view-greetings > div:first-child,
        html.light #reader-surah-header,
        html.light #search-header,
        html.light #search-empty {
            background-color: #ffffff !important;
            border-color: #ebdcc4 !important;
            color: #27272a !important;
            box-shadow: 0 4px 20px -2px rgba(80, 55, 20, 0.05), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
        }
        html.light .hadith-card:hover,
        html.light .ayah-card:hover,
        html.light .surah-card:hover,
        html.light .greetings-card:hover {
            border-color: #10b981 !important;
            box-shadow: 0 8px 30px -4px rgba(4, 120, 87, 0.12) !important;
        }

        /* Tüm Koyu Arka Plan Sınıflarının Açık Temadaki Zarif Karşılıkları */
        html.light [class*="bg-gray-900"],
        html.light [class*="bg-gray-950"],
        html.light [class*="bg-gray-850"] {
            background-color: #fdfbf7 !important;
            border-color: #ebdcc4 !important;
            color: #27272a !important;
        }
        html.light [class*="bg-gray-800"] {
            background-color: #f5efe3 !important;
            border-color: #e5d9c3 !important;
            color: #3f3f46 !important;
        }
        html.light [class*="border-gray-800"],
        html.light [class*="border-gray-700"] {
            border-color: #ebdcc4 !important;
        }
        html.light [class*="text-gray-100"],
        html.light [class*="text-gray-200"],
        html.light [class*="text-gray-300"],
        html.light [class*="text-white"] {
            color: #18181b !important;
        }
        html.light [class*="text-gray-400"],
        html.light [class*="text-gray-500"] {
            color: #64748b !important;
        }

        /* Yazı Tipleri ve Metin Kutuları */
        html.light .arabic-text {
            color: #111827 !important;
            font-weight: 500;
        }
        html.light .meal-content {
            color: #27272a !important;
            font-weight: 400;
        }
        html.light .transliteration-container {
            background-color: #f6faf7 !important;
            border-color: #c7ebd7 !important;
            color: #065f46 !important;
        }
        html.light [id^="compare-box-"] {
            background-color: #fdfaf4 !important;
            border-color: #ebdcc4 !important;
        }
        html.light [id^="note-box-"] {
            background-color: #f0fdf4 !important;
            border-color: #bbf7d0 !important;
        }

        /* İnput, Select ve Arama Alanları */
        html.light input,
        html.light select,
        html.light textarea {
            background-color: #ffffff !important;
            border-color: #d8cbbb !important;
            color: #18181b !important;
        }
        html.light input::placeholder {
            color: #94a3b8 !important;
        }
        html.light input:focus,
        html.light select:focus,
        html.light textarea:focus {
            border-color: #059669 !important;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12) !important;
        }

        /* Rozetler ve Vurgu Renkleri */
        html.light .bg-emerald-950,
        html.light .bg-emerald-900\/40,
        html.light .bg-emerald-950\/80,
        html.light .bg-emerald-950\/60,
        html.light .bg-emerald-950\/30 {
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            border-color: #a7f3d0 !important;
        }
        html.light .text-emerald-400,
        html.light .text-emerald-300 {
            color: #047857 !important;
        }
        html.light .bg-amber-950,
        html.light .bg-amber-950\/60,
        html.light .bg-amber-950\/30,
        html.light .bg-amber-950\/20 {
            background-color: #fffbeb !important;
            color: #b45309 !important;
            border-color: #fde68a !important;
        }
        html.light .text-amber-400,
        html.light .text-amber-300,
        html.light .text-amber-200\/90 {
            color: #b45309 !important;
        }
        html.light .bg-blue-950,
        html.light .bg-blue-950\/60 {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            border-color: #bfdbfe !important;
        }
        html.light .text-blue-400 {
            color: #1d4ed8 !important;
        }
        html.light .bg-purple-950 {
            background-color: #faf5ff !important;
            color: #7e22ce !important;
            border-color: #e9d5ff !important;
        }
        html.light .text-purple-300 {
            color: #7e22ce !important;
        }

        /* Hadis Kategori Butonları */
        html.light .hadith-cat-btn {
            background-color: #f4eee2 !important;
            border: 1px solid #e5d9c3 !important;
            color: #443729 !important;
        }
        html.light .hadith-cat-btn:hover {
            background-color: #ede3d0 !important;
            color: #047857 !important;
            border-color: #a7f3d0 !important;
        }
        html.light .hadith-cat-btn.bg-emerald-600 {
            background-color: #047857 !important;
            border-color: #047857 !important;
            color: #ffffff !important;
        }

        /* Sabit Ses Oynatıcı Barı */
        html.light #global-audio-player {
            background-color: #ffffff !important;
            border-color: #e4d7c0 !important;
            color: #18181b !important;
            box-shadow: 0 -6px 25px rgba(80, 55, 20, 0.08) !important;
        }
        html.light #global-audio-player select,
        html.light #global-audio-player button:not(#global-play-btn) {
            background-color: #f7f2e7 !important;
            border-color: #dfd2b9 !important;
            color: #27272a !important;
        }

        .audio-wave {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            height: 14px;
        }
        .audio-wave span {
            width: 3px;
            background-color: #10b981;
            border-radius: 2px;
            animation: wave 1s infinite ease-in-out;
        }
        .audio-wave span:nth-child(2) { animation-delay: 0.2s; }
        .audio-wave span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes wave {
            0%, 100% { height: 4px; }
            50% { height: 14px; }
        }
        @media print {
            header, nav, #global-audio-player, #search-form, .action-buttons, .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
            .ayah-card, .hadith-card {
                border: 1px solid #ccc !important;
                page-break-inside: avoid;
                margin-bottom: 20px;
                color: black !important;
            }
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen font-sans antialiased selection:bg-emerald-600 selection:text-white transition-colors duration-200">

    <!-- Üst Başlık & Navigasyon (Kusursuz Hiza, Sıfır Taşma & Ferah Tasarım) -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 shadow-sm transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- 1. Kademe: Marka / Logo ve Hızlı Ayarlar -->
            <div class="flex items-center justify-between h-16 border-b border-gray-100 dark:border-gray-800/60">
                <!-- Logo & Başlık -->
                <div class="flex items-center gap-3 cursor-pointer group" onclick="switchView('search');">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 flex items-center justify-center shadow-md shadow-emerald-900/20 group-hover:scale-105 transition-transform duration-200">
                        <i class="fa-solid fa-book-quran text-white text-lg sm:text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-base sm:text-lg font-bold tracking-tight text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                            Kur'an-ı Kerim
                        </h1>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 hidden sm:block">Arapça Orijinal Metin &bull; Türkçe Mealler &bull; Sesli Dinleme &bull; Sahih Hadisler</p>
                    </div>
                </div>

                <!-- Sağ Araçlar (Okunuş, Font Boyutu & Tema Değiştirici) -->
                <div class="flex items-center gap-2">
                    <!-- Okunuş Göster/Gizle Butonu -->
                    <button onclick="toggleTransliteration()" id="transliteration-toggle" title="Türkçe Okunuşu Göster/Gizle" class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-700 dark:text-gray-300 transition">
                        <i class="fa-solid fa-language text-emerald-600 dark:text-emerald-400"></i>
                        <span class="hidden sm:inline font-medium">Okunuş</span>
                    </button>

                    <!-- Arapça Font Boyutu Kontrolü -->
                    <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-xl p-0.5 border border-gray-200 dark:border-gray-700 text-xs text-gray-700 dark:text-gray-300">
                        <button onclick="changeFontSize(-2)" title="Arapça Yazıyı Küçült" class="px-2 py-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition font-bold">A-</button>
                        <span id="font-size-label" class="px-1.5 font-mono text-[11px] font-bold text-emerald-700 dark:text-emerald-400">28px</span>
                        <button onclick="changeFontSize(2)" title="Arapça Yazıyı Büyüt" class="px-2 py-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition font-bold">A+</button>
                    </div>

                    <!-- Tema Değiştirici (Ferah Krem & Koyu Mod) -->
                    <button onclick="toggleTheme()" id="theme-btn" title="Koyu / Ferah Krem Tema Değiştir" class="p-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 transition flex items-center justify-center">
                        <i class="fa-solid fa-sun text-amber-500 text-sm" id="theme-icon"></i>
                    </button>
                </div>
            </div>

            <!-- 2. Kademe: Birleşik Responsive Navigasyon Sekmeleri (Asla Taşmaz, Her Ekrana Tam Oturur) -->
            <div class="py-2.5">
                <nav class="flex flex-wrap items-center justify-start gap-1.5 sm:gap-2 w-full">
                    <button onclick="switchView('search')" id="nav-search" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-emerald-600 text-white shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i> Arama
                    </button>
                    <button onclick="switchView('surahs')" id="nav-surahs" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-700 dark:hover:text-white transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-list-ol text-xs"></i> Sureler
                    </button>
                    <button onclick="switchView('juz')" id="nav-juz" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-700 dark:hover:text-white transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-bookmark text-xs"></i> Cüzler
                    </button>
                    <button onclick="switchView('pages')" id="nav-pages" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-700 dark:hover:text-white transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-file-lines text-xs"></i> Sayfalar
                    </button>
                    <button onclick="switchView('hadiths')" id="nav-hadiths" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-700 dark:hover:text-white transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-scroll text-amber-500 text-xs"></i> Sahih Hadisler
                    </button>
                    <button onclick="switchView('greetings')" id="nav-greetings" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-700 dark:hover:text-white transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-brands fa-whatsapp text-emerald-600 dark:text-emerald-400 text-sm"></i> Cuma & Tebrik
                    </button>
                    <button onclick="switchView('favorites')" id="nav-favorites" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-700 dark:hover:text-white transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-star text-amber-500 text-xs"></i> Favorilerim <span id="favorites-count-badge" class="text-[10px] px-1.5 py-0.2 bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 rounded-full font-bold">0</span>
                    </button>
                    <button onclick="switchView('sajdah')" id="nav-sajdah" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-700 dark:hover:text-white transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-hands-praying text-xs text-purple-500"></i> Secde Ayetleri
                    </button>
                    <button onclick="switchView('topics')" id="nav-topics" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-700 dark:hover:text-white transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-tags text-xs text-teal-500"></i> Konu Dizini
                    </button>
                    <button onclick="switchView('history')" id="nav-history" class="nav-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-emerald-700 dark:hover:text-white transition-all flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fa-solid fa-landmark-dome text-xs text-amber-500"></i> İslam Tarihi
                    </button>
                </nav>
            </div>
        </div>
    </header>

    <!-- Son Kaldığım Yer Bildirim Çubuğu (Varsa) -->
    <div id="last-read-banner" class="hidden bg-emerald-950/80 border-b border-emerald-800/80 px-4 py-2.5 text-xs text-emerald-200">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-bookmark text-emerald-400"></i>
                <span>En son kaldığınız yer: <b id="last-read-text" class="text-white"></b></span>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="goToLastRead()" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded font-medium transition">Kaldığım Yere Git</button>
                <button onclick="dismissLastRead()" class="text-gray-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
    </div>

    <!-- Ana İçerik Alanı -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- 1. GÖRÜNÜM: ARAMA MOTORU (SEARCH VIEW) -->
        <section id="view-search" class="space-y-6">
            <!-- Arama Kutusu ve Filtreler Kartı -->
            <div class="bg-gray-900/90 border border-gray-800 rounded-2xl p-4 sm:p-7 shadow-2xl relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-72 h-72 bg-emerald-600/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-teal-600/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="max-w-3xl mx-auto space-y-4">
                    <div class="text-center space-y-1">
                        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                            Kur'an-ı Kerim'de Kelime ve Ayet Arayın
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-400">
                            Türkçe meallerde, Arapça orijinal lafızlarda veya sure adı / ayet referanslarında anında arayın
                        </p>
                    </div>

                    <!-- Ana Arama Formu -->
                    <form id="search-form" onsubmit="handleSearch(event)" class="relative">
                        <div class="relative flex items-center">
                            <div class="absolute left-4 text-gray-400">
                                <i class="fa-solid fa-magnifying-glass text-lg"></i>
                            </div>
                            <input 
                                type="text" 
                                id="search-input" 
                                value="<?= htmlspecialchars($initialQuery) ?>"
                                placeholder="Örn: helal, healal, 'helal AND temiz', 'cennet OR cehennem', 2:255, Bakara 168 veya حلال..."
                                class="w-full pl-12 pr-28 sm:pr-32 py-3.5 sm:py-4 bg-gray-950/90 border-2 border-gray-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 rounded-xl text-white placeholder-gray-500 text-sm sm:text-base outline-none transition-all shadow-inner"
                                autocomplete="off"
                            >
                            <div class="absolute right-2 flex items-center gap-1">
                                <button 
                                    type="button" 
                                    onclick="clearSearch()" 
                                    id="clear-btn" 
                                    class="hidden p-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800 transition"
                                    title="Temizle"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <button 
                                    type="submit" 
                                    class="px-4 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-lg text-sm font-semibold shadow-md shadow-emerald-900/30 transition-all flex items-center gap-1.5"
                                >
                                    <span>Ara</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Filtreler -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 text-xs text-gray-400">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Sure Filtresi -->
                            <div class="flex items-center gap-1.5 bg-gray-950/80 border border-gray-800 rounded-lg px-2.5 py-1.5">
                                <i class="fa-solid fa-filter text-emerald-400 text-[10px]"></i>
                                <span class="text-gray-400">Sure:</span>
                                <select id="filter-surah" onchange="triggerSearch()" class="bg-transparent text-gray-200 outline-none cursor-pointer">
                                    <option value="" class="bg-gray-900 text-white">Tüm Sureler (114)</option>
                                    <?php foreach ($surahs as $s): ?>
                                        <option value="<?= $s['id'] ?>" class="bg-gray-900 text-white" <?= $initialSurah == $s['id'] ? 'selected' : '' ?>>
                                            <?= $s['id'] ?>. <?= htmlspecialchars($s['name_tr']) ?> (<?= $s['ayahs_count'] ?> Ayet)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- İniş Yeri Filtresi -->
                            <div class="flex items-center gap-1.5 bg-gray-950/80 border border-gray-800 rounded-lg px-2.5 py-1.5">
                                <span class="text-gray-400">Dönem:</span>
                                <select id="filter-revelation" onchange="triggerSearch()" class="bg-transparent text-gray-200 outline-none cursor-pointer">
                                    <option value="all" class="bg-gray-900 text-white">Tümü (Mekke & Medine)</option>
                                    <option value="Mekke" class="bg-gray-900 text-white">Mekke Dönemi (<?= $stats['meccan_surahs'] ?> Sure)</option>
                                    <option value="Medine" class="bg-gray-900 text-white">Medine Dönemi (<?= $stats['medinan_surahs'] ?> Sure)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Hızlı İstatistik -->
                        <div class="hidden sm:flex items-center gap-2 text-gray-500 font-mono text-[11px]">
                            <span>6,236 Ayet</span>
                            <span>&bull;</span>
                            <span>114 Sure</span>
                        </div>
                    </div>

                    <!-- Popüler Hızlı Arama Etiketleri -->
                    <div class="pt-2">
                        <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-2">
                            <i class="fa-solid fa-fire text-amber-500 text-xs"></i>
                            <span class="font-medium text-gray-300">Örnek Aramalar & Mantıksal İpuçları:</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <?php 
                            $sampleTags = ['helal', 'healal (yazım toleransı)', 'helal AND temiz', 'cennet OR cehennem', 'haram', 'adalet', 'akıl', 'namaz', 'infak', 'sabır', 'şükür', '2:255', 'Bakara 168', 'حلال'];
                            foreach ($sampleTags as $tag):
                                $val = explode(' (', $tag)[0];
                            ?>
                                <button 
                                    type="button" 
                                    onclick="setSearchTag('<?= htmlspecialchars($val) ?>')" 
                                    class="text-xs bg-gray-800/70 hover:bg-emerald-900/40 hover:text-emerald-300 hover:border-emerald-700/60 border border-gray-700/60 text-gray-300 px-2.5 py-1 rounded-md transition duration-150"
                                >
                                    <?= htmlspecialchars($tag) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Arama Sonuçları Başlığı, Sayaç ve Dışa Aktarma Butonları -->
            <div id="search-header" class="hidden flex flex-wrap items-center justify-between gap-3 bg-gray-900/60 border border-gray-800/80 px-5 py-3.5 rounded-xl">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-950 text-emerald-400 border border-emerald-800 flex items-center justify-center">
                        <i class="fa-solid fa-list-check text-sm"></i>
                    </div>
                    <div>
                        <h3 id="search-summary-text" class="text-sm font-semibold text-white"></h3>
                        <p class="text-xs text-gray-400" id="search-sub-summary">Arapça orijinal metin ve Türkçe meal eşleşmeleri</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span id="results-count-badge" class="px-3 py-1 bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 rounded-full text-xs font-semibold"></span>
                    
                    <!-- Dışa Aktarma Butonları -->
                    <button onclick="exportResultsAsText()" title="Metin Dosyası Olarak İndir (.txt)" class="px-3 py-1 bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 rounded-lg text-xs transition flex items-center gap-1">
                        <i class="fa-solid fa-file-arrow-down text-emerald-400"></i> Metin (.txt)
                    </button>
                    <button onclick="window.print()" title="PDF Olarak Kaydet / Yazdır" class="px-3 py-1 bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 rounded-lg text-xs transition flex items-center gap-1">
                        <i class="fa-solid fa-print text-amber-400"></i> PDF / Yazdır
                    </button>
                </div>
            </div>

            <!-- Yükleniyor / Spinner -->
            <div id="search-loading" class="hidden py-16 text-center space-y-3">
                <div class="inline-block w-10 h-10 border-4 border-emerald-500/30 border-t-emerald-500 rounded-full animate-spin"></div>
                <p class="text-sm text-gray-400">Ayetler taranıyor, lütfen bekleyin...</p>
            </div>

            <!-- Sonuç Yok Kartı -->
            <div id="search-empty" class="hidden py-16 text-center bg-gray-900/40 border border-gray-800 rounded-2xl p-8 space-y-3">
                <div class="w-16 h-16 mx-auto rounded-full bg-gray-800 flex items-center justify-center text-gray-500 text-2xl">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <h3 class="text-lg font-semibold text-white">Sonuç Bulunamadı</h3>
                <p class="text-sm text-gray-400 max-w-md mx-auto">
                    Aradığınız kelimeye veya kritere uygun ayet bulunamadı. Lütfen kelimeyi kontrol edip tekrar deneyin.
                </p>
            </div>

            <!-- Arama Sonuçları Listesi -->
            <div id="search-results" class="space-y-4"></div>

            <!-- Sayfalama (Pagination) -->
            <div id="search-pagination" class="hidden flex items-center justify-center gap-2 pt-6"></div>
        </section>

        <!-- 2. GÖRÜNÜM: SURELER LİSTESİ (SURAHS VIEW) -->
        <section id="view-surahs" class="hidden space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 bg-gray-900/80 border border-gray-800 p-5 rounded-2xl">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-list-ol text-emerald-400"></i> Kur'an-ı Kerim Sureleri (1-114)
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-400">Okumak veya incelemek istediğiniz sureyi seçin</p>
                </div>
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-filter absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <input 
                        type="text" 
                        id="surah-filter-input" 
                        oninput="filterSurahsList(this.value)" 
                        placeholder="Sure adına göre filtrele..." 
                        class="w-full pl-8 pr-3 py-2 bg-gray-950 border border-gray-700 rounded-lg text-xs text-white placeholder-gray-500 outline-none focus:border-emerald-500"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="surahs-grid">
                <?php foreach ($surahs as $s): ?>
                    <div 
                        onclick="openSurah(<?= $s['id'] ?>)" 
                        class="surah-card bg-gray-900/70 hover:bg-gray-850 hover:border-emerald-600/70 border border-gray-800 p-4 rounded-xl cursor-pointer transition-all duration-200 group relative overflow-hidden shadow-sm hover:shadow-emerald-950/20"
                        data-name="<?= htmlspecialchars(mb_strtolower($s['name_tr'] . ' ' . $s['name_en'] . ' ' . $s['id'])) ?>"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gray-800 group-hover:bg-emerald-600 group-hover:text-white text-emerald-400 font-bold text-xs flex items-center justify-center border border-gray-700 transition">
                                    <?= $s['id'] ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-white text-sm group-hover:text-emerald-300 transition">
                                        <?= htmlspecialchars($s['name_tr']) ?>
                                    </h4>
                                    <p class="text-[11px] text-gray-400"><?= htmlspecialchars($s['name_en']) ?></p>
                                </div>
                            </div>
                            <span class="text-base font-quran text-gray-400 group-hover:text-emerald-400 transition" dir="rtl">
                                <?= htmlspecialchars($s['name_ar']) ?>
                            </span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-800/80 flex items-center justify-between text-[11px] text-gray-400">
                            <span class="flex items-center gap-1">
                                <i class="fa-regular fa-file-lines text-emerald-500"></i> <?= $s['ayahs_count'] ?> Ayet
                            </span>
                            <span class="px-2 py-0.5 rounded-full <?= $s['revelation_type'] === 'Mekke' ? 'bg-amber-950/60 text-amber-400 border border-amber-800/50' : 'bg-blue-950/60 text-blue-400 border border-blue-800/50' ?>">
                                <?= $s['revelation_type'] ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 3. GÖRÜNÜM: CÜZLER (JUZ VIEW) -->
        <section id="view-juz" class="hidden space-y-6">
            <div class="bg-gray-900/80 border border-gray-800 p-5 rounded-2xl">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-bookmark text-emerald-400"></i> 30 Cüz Listesi
                </h2>
                <p class="text-xs sm:text-sm text-gray-400">Cüz bazlı okuma ve hatim takibi</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 gap-3">
                <?php for ($i = 1; $i <= 30; $i++): ?>
                    <button 
                        onclick="openJuz(<?= $i ?>)" 
                        class="bg-gray-900/70 hover:bg-emerald-950 hover:border-emerald-600 border border-gray-800 p-4 rounded-xl text-center transition group space-y-1"
                    >
                        <div class="w-10 h-10 mx-auto rounded-full bg-gray-800 group-hover:bg-emerald-600 text-emerald-400 group-hover:text-white font-bold text-sm flex items-center justify-center border border-gray-700 transition">
                            <?= $i ?>
                        </div>
                        <h4 class="font-bold text-sm text-white group-hover:text-emerald-300">
                            <?= $i ?>. Cüz
                        </h4>
                        <p class="text-[11px] text-gray-400">Ayetleri Listele</p>
                    </button>
                <?php endfor; ?>
            </div>
        </section>

        <!-- 4. GÖRÜNÜM: MUSHAF SAYFALARI (PAGES VIEW 1-604) -->
        <section id="view-pages" class="hidden space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 bg-gray-900/80 border border-gray-800 p-5 rounded-2xl">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-file-lines text-emerald-400"></i> Standart Mushaf Sayfaları (1 - 604)
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-400">Orijinal Mushaf sayfa yapısına göre okuyun</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="changePageNumber(-1)" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-xs transition">
                        <i class="fa-solid fa-chevron-left"></i> Önceki Sayfa
                    </button>
                    <select id="page-selector" onchange="openPage(parseInt(this.value))" class="bg-gray-950 border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white outline-none">
                        <?php for ($p = 1; $p <= 604; $p++): ?>
                            <option value="<?= $p ?>">Sayfa <?= $p ?></option>
                        <?php endfor; ?>
                    </select>
                    <button onclick="changePageNumber(1)" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-xs transition">
                        Sonraki Sayfa <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div id="page-ayahs-list" class="space-y-4"></div>
        </section>

        <!-- 5. GÖRÜNÜM: FAVORİLERİM (FAVORITES VIEW) -->
        <section id="view-favorites" class="hidden space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 bg-gray-900/80 border border-gray-800 p-5 rounded-2xl">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-star text-amber-400"></i> Yıldızlı Ayetlerim (Favoriler)
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-400">Kaydettiğiniz ve üzerinde çalıştığınız ayetler</p>
                </div>
                <button onclick="clearAllFavorites()" class="px-3 py-1.5 bg-red-950/60 hover:bg-red-900 border border-red-800 text-red-300 rounded-lg text-xs transition">
                    <i class="fa-solid fa-trash-can"></i> Tümünü Temizle
                </button>
            </div>
            <div id="favorites-results" class="space-y-4"></div>
        </section>

        <!-- 6. GÖRÜNÜM: SECDE AYETLERİ (SAJDAH VIEW) -->
        <section id="view-sajdah" class="hidden space-y-6">
            <div class="bg-gray-900/80 border border-gray-800 p-5 rounded-2xl">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-hands-praying text-emerald-400"></i> Kur'an-ı Kerim'deki Tilavet Secdesi Ayetleri
                </h2>
                <p class="text-xs sm:text-sm text-gray-400">Kur'an-ı Kerim'deki tilavet secdesi ayetleri, orijinal Arapça metinleri ve Türkçe mealleri</p>
            </div>
            <div id="sajdah-results" class="space-y-4"></div>
        </section>

        <!-- 7. GÖRÜNÜM: KONU VE KAVRAM DİZİNİ (TOPICS VIEW) -->
        <section id="view-topics" class="hidden space-y-6">
            <div class="bg-gray-900/80 border border-gray-800 p-5 rounded-2xl">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-tags text-emerald-400"></i> Kur'an Kavramları ve Konu Dizini
                </h2>
                <p class="text-xs sm:text-sm text-gray-400">Temel İslami ve Kur'ani kavramları tek tıkla arayın</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php 
                $topicCategories = [
                    'Helal & Haram ve Hükümler' => ['helal', 'haram', 'faiz', 'riba', 'miras', 'vasiyet', 'nikah', 'talak', 'yemin', 'kurban'],
                    'İbadetler & Ameller' => ['namaz', 'salat', 'oruç', 'hac', 'zekat', 'infak', 'sadaka', 'dua', 'zikir', 'tesbih'],
                    'Ahlak & Erdemler' => ['adalet', 'akıl', 'ilim', 'sabır', 'şükür', 'af', 'ihsan', 'dürüstlük', 'emanet', 'ahde vefa'],
                    'İman Esasları' => ['allah', 'tevhid', 'peygamber', 'resul', 'ahiret', 'cennet', 'cehennem', 'melek', 'vahiy', 'kıyamet'],
                    'Sosyal Hayat & İnsan' => ['anne baba', 'akraba', 'yetim', 'yoksul', 'komşu', 'barış', 'kardeşlik', 'istişare', 'zulüm', 'haksızlık'],
                    'Tefekkür & Yaratılış' => ['gökler', 'yeryüzü', 'güneş', 'ay', 'yıldızlar', 'yağmur', 'deniz', 'insanın yaratılışı', 'ibret', 'delil']
                ];
                foreach ($topicCategories as $catName => $topics):
                ?>
                    <div class="bg-gray-900/70 border border-gray-800 rounded-xl p-5 space-y-3">
                        <h3 class="font-bold text-emerald-400 text-sm border-b border-gray-800 pb-2 flex items-center gap-2">
                            <i class="fa-solid fa-bookmark text-xs"></i> <?= $catName ?>
                        </h3>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($topics as $top): ?>
                                <button 
                                    onclick="setSearchTag('<?= $top ?>')" 
                                    class="px-2.5 py-1.5 rounded-lg bg-gray-800 hover:bg-emerald-600 hover:text-white text-gray-300 text-xs transition flex items-center gap-1"
                                >
                                    <i class="fa-solid fa-search text-[10px] opacity-60"></i> <?= ucfirst($top) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 8. GÖRÜNÜM: ÖZEL GÜNLER, CUMA & KANDİL TEBRİKLERİ (GREETINGS VIEW) -->
        <section id="view-greetings" class="hidden space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 bg-gray-900/80 border border-gray-800 p-5 rounded-2xl">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-emerald-500"></i> Özel Günler, Cuma & Kandil Tebrik Merkezi
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-400">Kur'an-ı Kerim ayetleri ve Türkçe meallerle sevdiklerinize WhatsApp mesajı gönderin veya şık görsel kart oluşturun</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs px-3 py-1 bg-emerald-950 text-emerald-300 border border-emerald-800/80 rounded-full font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-400"></i> 1080x1080 HD Kart & WhatsApp
                    </span>
                </div>
            </div>

            <!-- Kategori Filtre Butonları -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
                <button onclick="filterGreetings('all')" class="greeting-filter-btn active px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white transition whitespace-nowrap" data-cat="all">
                    Tümü
                </button>
                <button onclick="filterGreetings('cuma')" class="greeting-filter-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="cuma">
                    🕌 Hayırlı Cumalar
                </button>
                <button onclick="filterGreetings('ramazan')" class="greeting-filter-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="ramazan">
                    🌙 Ramazan & İftar
                </button>
                <button onclick="filterGreetings('kadir')" class="greeting-filter-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="kadir">
                    🌟 Kadir Gecesi
                </button>
                <button onclick="filterGreetings('bayram')" class="greeting-filter-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="bayram">
                    🕋 Ramazan & Kurban Bayramı
                </button>
                <button onclick="filterGreetings('kandil')" class="greeting-filter-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="kandil">
                    ✨ Kandiller (Regaip, Miraç, Berat, Mevlid)
                </button>
                <button onclick="filterGreetings('dua')" class="greeting-filter-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="dua">
                    🤲 Şifa & Sabır & Şükür
                </button>
                <button onclick="filterGreetings('ozturk')" class="greeting-filter-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="ozturk">
                    📖 Tefekkür & Hikmet
                </button>
            </div>

            <!-- Tebrik Kartları Grid -->
            <div id="greetings-cards-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- JavaScript tarafından dinamik oluşturulacak -->
            </div>
        </section>

        <!-- 9. GÖRÜNÜM: SAHİH HADİSLER KÜLLİYATI (KÜTÜB-İ SİTTE & RİYAZÜ'S-SALİHİN) -->
        <section id="view-hadiths" class="hidden space-y-6">
            <!-- Başlık ve Sıhhat Güvencesi Kartı -->
            <div class="bg-gray-900/80 border border-gray-800 p-5 sm:p-6 rounded-2xl space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-scroll text-amber-400"></i> Sahih Hadisler Külliyatı
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-400">
                            Peygamber Efendimiz'in (s.a.v.) muteber hadis-i şerifleri (Sahîh-i Buhârî, Sahîh-i Müslim, Tirmizî, Ebû Dâvûd, Nesâî, İbn Mâce). Tam isnad ve kaynaklıdır.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1.5 rounded-full bg-emerald-950 text-emerald-300 border border-emerald-800/80 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-shield-halved text-emerald-400"></i> %100 Sahih İsnad Güvencesi
                        </span>
                    </div>
                </div>

                <!-- Arama Kutusu -->
                <div class="pt-2">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input 
                            type="text" 
                            id="hadith-search-input" 
                            oninput="onHadithSearchInput()" 
                            placeholder="Hadislerde arayın (Örn: niyet, komşu, ilim, merhamet, işçi, ana-baba, emanet, tevekkül)..." 
                            class="w-full pl-10 pr-10 py-2.5 bg-gray-950/80 border border-gray-700/80 rounded-xl text-xs sm:text-sm text-white placeholder-gray-500 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                        >
                        <button onclick="clearHadithSearch()" id="hadith-clear-btn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white p-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kategori Filtre Butonları -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar" id="hadith-category-pills">
                <button onclick="filterHadithsByCategory('all')" class="hadith-cat-btn active px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white transition whitespace-nowrap" data-cat="all">
                    Tüm Sahih Hadisler
                </button>
                <button onclick="filterHadithsByCategory('niyet_ihlas')" class="hadith-cat-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="niyet_ihlas">
                    Niyet & İhlâs
                </button>
                <button onclick="filterHadithsByCategory('guzel_ahlak')" class="hadith-cat-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="guzel_ahlak">
                    Güzel Ahlâk
                </button>
                <button onclick="filterHadithsByCategory('adalet_merhamet')" class="hadith-cat-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="adalet_merhamet">
                    Adalet & Merhamet
                </button>
                <button onclick="filterHadithsByCategory('anne_baba')" class="hadith-cat-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="anne_baba">
                    Anne-Baba & Aile
                </button>
                <button onclick="filterHadithsByCategory('ilim_amel')" class="hadith-cat-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="ilim_amel">
                    İlim & Hikmet
                </button>
                <button onclick="filterHadithsByCategory('helal_haram')" class="hadith-cat-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="helal_haram">
                    Helal Kazanç
                </button>
                <button onclick="filterHadithsByCategory('dua_zikir')" class="hadith-cat-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="dua_zikir">
                    Dua & İbadet
                </button>
                <button onclick="filterHadithsByCategory('sabir_sukur')" class="hadith-cat-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="sabir_sukur">
                    Sabır & Tevekkül
                </button>
                <button onclick="filterHadithsByCategory('komsu_kardeslik')" class="hadith-cat-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-cat="komsu_kardeslik">
                    Komşuluk & Kardeşlik
                </button>
            </div>

            <!-- Hadis Yükleniyor / Boş Durum -->
            <div id="hadith-loading" class="hidden py-16 text-center">
                <div class="inline-block w-8 h-8 border-4 border-emerald-500/30 border-t-emerald-500 rounded-full animate-spin"></div>
                <p class="text-xs text-gray-400 mt-2">Sahih Hadisler yükleniyor...</p>
            </div>

            <!-- Hadis Kartları Listesi -->
            <div id="hadiths-list-container" class="space-y-4"></div>
        </section>

                <!-- 10. GÖRÜNÜM: İSLAM TARİHİ FİHRİSTİ & KRONOLOJİSİ -->
        <section id="view-history" class="hidden space-y-6">
            <!-- Başlık ve Filtreleme Kartı -->
            <div class="bg-gray-900/80 border border-gray-800 p-5 sm:p-6 rounded-2xl space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-landmark-dome text-amber-400"></i> İslam Tarihi Fihristi & Kronolojisi
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-400">
                            Peygamberler Tarihi, Kur'an Kıssaları, Nüzul Dönemi Olayları, Gazveler, Dört Halife ve Mushaf Tarihi. İlgili Kur'an ayetleriyle bağlantılıdır.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="history-total-badge" class="px-3 py-1.5 rounded-full bg-amber-950 text-amber-300 border border-amber-800/80 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-clock-rotate-left text-amber-400"></i> <span id="history-count-text">34 Tarihi Hadise</span>
                        </span>
                    </div>
                </div>

                <!-- Arama Kutusu -->
                <div class="pt-1">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input 
                            type="text" 
                            id="history-search-input" 
                            oninput="onHistorySearchInput()" 
                            placeholder="İslam tarihinde arayın (Örn: Bedir, Uhud, Hicret, Miraç, Hz. Yusuf, Hudeybiye, Mushaf, Veda Haccı)..." 
                            class="w-full pl-10 pr-10 py-2.5 bg-gray-950/80 border border-gray-700/80 rounded-xl text-xs sm:text-sm text-white placeholder-gray-500 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition"
                        >
                        <button onclick="clearHistorySearch()" id="history-clear-btn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white p-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Dönem Filtre Butonları -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar" id="history-period-pills">
                    <button onclick="filterHistoryByPeriod('all')" class="history-period-btn active px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white transition whitespace-nowrap" data-period="all">
                        Tüm Hadiseler
                    </button>
                    <button onclick="filterHistoryByPeriod('peygamberler')" class="history-period-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-period="peygamberler">
                        Peygamberler Tarihi & Kıssalar
                    </button>
                    <button onclick="filterHistoryByPeriod('mekke')" class="history-period-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-period="mekke">
                        Mekke Dönemi (610-622)
                    </button>
                    <button onclick="filterHistoryByPeriod('medine')" class="history-period-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-period="medine">
                        Medine Dönemi & Gazveler (622-632)
                    </button>
                    <button onclick="filterHistoryByPeriod('halifeler')" class="history-period-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-period="halifeler">
                        Dört Halife Dönemi (632-661)
                    </button>
                    <button onclick="filterHistoryByPeriod('mushaf')" class="history-period-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap" data-period="mushaf">
                        Kur'an & Mushaf Tarihi
                    </button>
                </div>
            </div>

            <!-- Tarih Kartları Listesi -->
            <div id="history-list-container" class="space-y-4"></div>
        </section>

        <!-- 10. GÖRÜNÜM: SURE OKUYUCU (FULL SURAH READER) -->
        <section id="view-reader" class="hidden space-y-6">
            <!-- Sure Başlığı ve Kontrolleri -->
            <div id="reader-surah-header" class="bg-gray-900/90 border border-gray-800 p-6 rounded-2xl shadow-xl text-center relative overflow-hidden space-y-4">
                <div class="flex items-center justify-between">
                    <button onclick="switchView('surahs')" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-xs flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-arrow-left"></i> Sure Listesi
                    </button>
                    <div class="flex items-center gap-2">
                        <button id="reader-prev-surah" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-xs transition">
                            <i class="fa-solid fa-chevron-left"></i> Önceki Sure
                        </button>
                        <button id="reader-next-surah" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-xs transition">
                            Sonraki Sure <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <span id="reader-surah-arabic" class="text-3xl sm:text-4xl font-quran text-emerald-400 block" dir="rtl"></span>
                    <h2 id="reader-surah-title" class="text-2xl font-bold text-white"></h2>
                    <div class="flex flex-wrap items-center justify-center gap-3 text-xs text-gray-400">
                        <span id="reader-surah-info"></span>
                        <span>&bull;</span>
                        <span id="reader-surah-revelation" class="px-2 py-0.5 rounded-full"></span>
                    </div>
                </div>

                <!-- Besmele (Tevbe suresi hariç) -->
                <div id="reader-bismillah" class="pt-4 border-t border-gray-800/80">
                    <div class="text-2xl sm:text-3xl font-quran text-emerald-300/90" dir="rtl">
                        بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Rahman ve Rahîm Allah'ın adıyla</p>
                </div>
            </div>

            <!-- Ayetler Listesi -->
            <div id="reader-ayahs-list" class="space-y-4"></div>
        </section>

    </main>

    <!-- Sabit Alt Ses Oynatıcı (Audio Player Bar & Hatim Modu) -->
    <div id="global-audio-player" class="fixed bottom-0 left-0 right-0 z-50 bg-gray-900/95 backdrop-blur-lg border-t border-gray-800 p-3 sm:p-4 shadow-2xl transition-transform duration-300 translate-y-full">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">
            <!-- Sol: Play / Pause ve Başlık -->
            <div class="flex items-center gap-3">
                <button onclick="toggleGlobalAudio()" id="global-play-btn" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center shadow-lg transition">
                    <i class="fa-solid fa-play text-base sm:text-lg"></i>
                </button>
                <div>
                    <h4 id="audio-title" class="text-xs sm:text-sm font-bold text-white flex items-center gap-2">
                        <span>Ayet Dinleniyor</span>
                        <div class="audio-wave"><span></span><span></span><span></span></div>
                    </h4>
                    <div class="flex items-center gap-2 text-[11px] sm:text-xs text-gray-400">
                        <span id="audio-subtitle">Okuyan: Mishary Rashid Alafasy</span>
                    </div>
                </div>
            </div>

            <!-- Orta / Sağ: Kârî Seçimi, Otomatik Çalma & Hız -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Okuyucu (Reciter) Seçici -->
                <div class="flex items-center gap-1 bg-gray-950 border border-gray-800 rounded-lg px-2 py-1 text-xs">
                    <i class="fa-solid fa-microphone text-emerald-400 text-[10px]"></i>
                    <select id="audio-reciter-select" onchange="changeReciter(this.value)" class="bg-transparent text-gray-200 outline-none text-xs cursor-pointer">
                        <option value="ar.alafasy" class="bg-gray-900 text-white">Mishary Rashid Alafasy</option>
                        <option value="ar.abdulbasit" class="bg-gray-900 text-white">Abdulbasit Abdussamed</option>
                        <option value="ar.mahermuaiqly" class="bg-gray-900 text-white">Maher Al-Muaiqly</option>
                        <option value="ar.saadalghamidi" class="bg-gray-900 text-white">Saad Al-Ghamdi</option>
                        <option value="ar.shaatree" class="bg-gray-900 text-white">Abu Bakr Ash-Shatri</option>
                    </select>
                </div>

                <!-- Otomatik Sırayla Çalma (Autoplay Hatim Toggle) -->
                <button onclick="toggleAutoplay()" id="autoplay-toggle-btn" class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs border border-gray-700 bg-gray-800 text-gray-300 hover:text-white transition" title="Ayet bitince otomatik bir sonrakine geç">
                    <i class="fa-solid fa-forward-step text-emerald-400"></i>
                    <span>Otomatik Sıradaki: <b id="autoplay-status-label" class="text-emerald-400">Açık</b></span>
                </button>

                <!-- Hız Seçici -->
                <select id="audio-speed-select" onchange="changeAudioSpeed(this.value)" class="bg-gray-950 border border-gray-800 rounded-lg px-2 py-1 text-xs text-gray-300 outline-none cursor-pointer">
                    <option value="0.75">0.75x</option>
                    <option value="1.0" selected>1.0x (Normal)</option>
                    <option value="1.25">1.25x</option>
                    <option value="1.5">1.5x</option>
                </select>

                <audio id="audio-element" onended="onAudioEnded()" ontimeupdate="onAudioTimeUpdate()"></audio>
                <div class="text-xs font-mono text-gray-400" id="audio-timer">00:00</div>
                <button onclick="closeAudioPlayer()" class="p-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Görsel Ayet & Tebrik Kartı Oluşturma Modalı (Social Media Image Modal) -->
    <div id="image-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="modal-content-card bg-gray-900 border border-gray-800 rounded-2xl max-w-2xl w-full p-5 sm:p-6 space-y-4 shadow-2xl relative max-h-[95vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-emerald-400"></i> Görsel Tebrik & Ayet Kartı Oluşturucu
                </h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Kart Arka Plan Teması Seçici -->
            <div class="space-y-1.5">
                <label class="text-xs font-medium text-gray-400">Arka Plan Stili & Teması:</label>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                    <button type="button" onclick="setCardTheme('cream')" id="theme-btn-cream" class="card-theme-selector px-2 py-1.5 rounded-lg border text-xs font-medium flex items-center justify-center gap-1.5 bg-[#f5efe3] text-[#382b1d] border-amber-600 shadow-sm ring-2 ring-emerald-500">
                        <span class="w-3 h-3 rounded-full bg-[#cca97e]"></span> Sıcak Krem
                    </button>
                    <button type="button" onclick="setCardTheme('emerald')" id="theme-btn-emerald" class="card-theme-selector px-2 py-1.5 rounded-lg border text-xs font-medium flex items-center justify-center gap-1.5 bg-[#064e3b] text-white border-emerald-500">
                        <span class="w-3 h-3 rounded-full bg-emerald-400"></span> Zümrüt & Altın
                    </button>
                    <button type="button" onclick="setCardTheme('night')" id="theme-btn-night" class="card-theme-selector px-2 py-1.5 rounded-lg border text-xs font-medium flex items-center justify-center gap-1.5 bg-[#0f172a] text-white border-gray-700">
                        <span class="w-3 h-3 rounded-full bg-indigo-400"></span> Gece & Hilal
                    </button>
                    <button type="button" onclick="setCardTheme('rose')" id="theme-btn-rose" class="card-theme-selector px-2 py-1.5 rounded-lg border text-xs font-medium flex items-center justify-center gap-1.5 bg-[#4c0519] text-white border-rose-800">
                        <span class="w-3 h-3 rounded-full bg-rose-400"></span> Gül & Bordo
                    </button>
                    <button type="button" onclick="setCardTheme('turquoise')" id="theme-btn-turquoise" class="card-theme-selector px-2 py-1.5 rounded-lg border text-xs font-medium flex items-center justify-center gap-1.5 bg-[#083344] text-white border-cyan-800">
                        <span class="w-3 h-3 rounded-full bg-cyan-400"></span> Turkuaz Hat
                    </button>
                </div>
            </div>

            <!-- Gönderen / İmza Alanı -->
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        id="card-sender-input" 
                        oninput="onCardCustomizerChange()" 
                        placeholder="İmzanız / Gönderen (Örn: Ahmet Yılmaz & Ailesi)" 
                        class="w-full pl-3 pr-3 py-2 bg-gray-950 border border-gray-700 rounded-lg text-xs text-white placeholder-gray-500 outline-none focus:border-emerald-500"
                    >
                </div>
            </div>

            <!-- Canvas Önizleme -->
            <div class="flex justify-center bg-gray-950 p-2 rounded-xl border border-gray-800 overflow-hidden">
                <canvas id="card-canvas" width="1080" height="1080" class="max-h-[300px] sm:max-h-[360px] w-auto rounded-lg shadow-lg"></canvas>
            </div>

            <!-- Aksiyon Butonları -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                <p class="text-[11px] text-gray-400">1080x1080 HD (Instagram, WhatsApp Durum & Mesaj için hazır).</p>
                <div class="flex items-center gap-2">
                    <button onclick="shareCurrentCardOnWhatsApp()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow">
                        <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp'ta Gönder
                    </button>
                    <button onclick="downloadGeneratedCard()" class="px-3.5 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow border border-gray-700">
                        <i class="fa-solid fa-download"></i> Resmi İndir (PNG)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bildirim / Toast -->
    <div id="toast" class="fixed bottom-20 right-5 z-50 transform translate-y-10 opacity-0 transition-all duration-300 pointer-events-none bg-emerald-600 text-white px-4 py-2.5 rounded-xl shadow-xl flex items-center gap-2 text-sm font-medium">
        <i class="fa-solid fa-circle-check"></i>
        <span id="toast-message">Kopyalandı!</span>
    </div>

    <!-- JavaScript Uygulama Mantığı -->
    <script>
        // Durum Değişkenleri
        let currentArabicFontSize = 28;
        let showTransliteration = true;
        let isDarkMode = false;
        let isAutoplay = true;
        let currentReciter = 'tr.vakfi-audio'; // Varsayılan Tok Sesli Türkçe Meal
        let currentPlaybackSpeed = 1.0;
        let currentAudioMode = 'tr'; // 'tr' veya 'ar'
        
        let activeAudioAyahId = null;
        let currentPlaylist = [];
        let currentPlaylistIndex = -1;
        let currentSearchData = null;
        let activeReaderSurahId = 1;
        let activePageNumber = 1;

        // Hadis Veri Durumu
        let activeHadithCategory = 'all';
        let currentHadithsData = [];
        let hadithSearchTimer = null;

        // LocalStorage Anahtarları
        const STORAGE_FAVORITES = 'quran_favorites_v1';
        const STORAGE_NOTES = 'quran_notes_v1';
        const STORAGE_LAST_READ = 'quran_last_read_v1';
        const STORAGE_THEME = 'quran_theme_v1';

        // Sayfa Başlatma
        document.addEventListener('DOMContentLoaded', () => {
            loadTheme();
            updateFavoritesBadge();
            checkLastRead();

            const initialQuery = "<?= addslashes($initialQuery) ?>";
            const initialSurah = <?= (int)$initialSurah ?>;
            const initialView = "<?= addslashes($initialView) ?>";

            if (initialSurah > 0 && initialView === 'reader') {
                openSurah(initialSurah);
            } else if (initialQuery.trim() !== '') {
                document.getElementById('search-input').value = initialQuery;
                triggerSearch();
            } else {
                // Varsayılan arama
                document.getElementById('search-input').value = 'helal';
                triggerSearch();
            }
        });

        // Tema Yönetimi (Ferahlatıcı Sıcak Krem Açık Tema & Koyu Mod)
        function loadTheme() {
            const savedTheme = localStorage.getItem(STORAGE_THEME);
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                isDarkMode = true;
                const icon = document.getElementById('theme-icon');
                if (icon) icon.className = 'fa-solid fa-moon text-amber-400';
            } else {
                // Varsayılan ferah krem tema
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                isDarkMode = false;
                const icon = document.getElementById('theme-icon');
                if (icon) icon.className = 'fa-solid fa-sun text-amber-500';
            }
        }

        function toggleTheme() {
            isDarkMode = !isDarkMode;
            if (isDarkMode) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                localStorage.setItem(STORAGE_THEME, 'dark');
                const icon = document.getElementById('theme-icon');
                if (icon) icon.className = 'fa-solid fa-moon text-amber-400';
                showToast('Koyu tema aktif');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                localStorage.setItem(STORAGE_THEME, 'light');
                const icon = document.getElementById('theme-icon');
                if (icon) icon.className = 'fa-solid fa-sun text-amber-500';
                showToast('Ferah krem açık tema aktif');
            }
        }

        // Görünüm Değiştirici
        function switchView(viewName) {
            const views = ['search', 'surahs', 'juz', 'pages', 'favorites', 'sajdah', 'topics', 'greetings', 'hadiths', 'history', 'reader'];
            views.forEach(v => {
                const el = document.getElementById(`view-${v}`);
                if (el) el.classList.add('hidden');
                
                const navBtn = document.getElementById(`nav-${v}`);
                if (navBtn) {
                    navBtn.classList.remove('bg-emerald-600', 'text-white', 'shadow-sm', 'active');
                }
            });

            const activeEl = document.getElementById(`view-${viewName}`);
            if (activeEl) activeEl.classList.remove('hidden');

            const activeNavBtn = document.getElementById(`nav-${viewName}`);
            if (activeNavBtn) {
                activeNavBtn.classList.add('bg-emerald-600', 'text-white', 'shadow-sm', 'active');
            }

            if (viewName === 'sajdah') loadSajdahAyahs();
            if (viewName === 'favorites') loadFavorites();
            if (viewName === 'pages') openPage(activePageNumber);
            if (viewName === 'greetings') initGreetingsView();
            if (viewName === 'hadiths') loadHadiths();
            if (viewName === 'history') initHistoryView();

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Arama Etiketi Tıklaması
        function setSearchTag(tag) {
            document.getElementById('search-input').value = tag;
            document.getElementById('filter-surah').value = '';
            document.getElementById('filter-revelation').value = 'all';
            switchView('search');
            triggerSearch();
        }

        // Arama Temizleme
        function clearSearch() {
            document.getElementById('search-input').value = '';
            document.getElementById('clear-btn').classList.add('hidden');
            document.getElementById('search-input').focus();
        }

        // Arama Tetikleyici
        function handleSearch(e) {
            if (e) e.preventDefault();
            triggerSearch(1);
        }

        async function triggerSearch(page = 1) {
            const q = document.getElementById('search-input').value.trim();
            const surah = document.getElementById('filter-surah').value;
            const revelation = document.getElementById('filter-revelation').value;
            const clearBtn = document.getElementById('clear-btn');

            if (q !== '') {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }

            const loadingEl = document.getElementById('search-loading');
            const resultsEl = document.getElementById('search-results');
            const emptyEl = document.getElementById('search-empty');
            const headerEl = document.getElementById('search-header');
            const paginationEl = document.getElementById('search-pagination');

            loadingEl.classList.remove('hidden');
            resultsEl.innerHTML = '';
            emptyEl.classList.add('hidden');
            headerEl.classList.add('hidden');
            paginationEl.classList.add('hidden');

            try {
                const params = new URLSearchParams({
                    action: 'search',
                    q: q,
                    surah: surah,
                    revelation: revelation,
                    page: page,
                    limit: 30
                });

                const response = await fetch(`api.php?${params.toString()}`);
                const data = await response.json();

                loadingEl.classList.add('hidden');

                if (data.status === 'success') {
                    currentSearchData = data.data;
                    currentPlaylist = data.data.results;
                    renderSearchResults(data.data, q);
                } else {
                    showToast(data.message || 'Bir hata oluştu', 'error');
                }
            } catch (err) {
                loadingEl.classList.add('hidden');
                console.error(err);
                showToast('Arama sırasında bağlantı hatası oluştu', 'error');
            }
        }

        // Arama Sonuçlarını Ekrana Basma
        function renderSearchResults(data, query) {
            const resultsEl = document.getElementById('search-results');
            const emptyEl = document.getElementById('search-empty');
            const headerEl = document.getElementById('search-header');
            const summaryText = document.getElementById('search-summary-text');
            const countBadge = document.getElementById('results-count-badge');
            const paginationEl = document.getElementById('search-pagination');

            if (data.total === 0) {
                emptyEl.classList.remove('hidden');
                return;
            }

            headerEl.classList.remove('hidden');
            summaryText.innerHTML = `"${escapeHtml(query)}" için arama sonuçları`;
            countBadge.innerText = `${data.total} Ayet Bulundu`;

            let html = '';
            data.results.forEach((ayah, index) => {
                html += generateAyahCard(ayah, data.search_terms || [query], index);
            });

            resultsEl.innerHTML = html;

            if (data.total_pages > 1) {
                renderPagination(data.page, data.total_pages);
            }
        }

        // Ayet Kartı Şablonu (Hem Arapça hem Tok Sesli Türkçe Meal Dinleme)
        function generateAyahCard(ayah, highlightTerms = [], playlistIndex = -1) {
            const highlightedMeal = highlightText(ayah.text_tr_ozturk, highlightTerms);
            const isPlaying = activeAudioAyahId === ayah.id;
            const isFav = isFavorite(ayah.id);
            const userNote = getAyahNote(ayah.id);

            return `
                <div class="ayah-card bg-gray-900/80 border border-gray-800 hover:border-gray-700 rounded-2xl p-5 sm:p-6 transition-all duration-200 shadow-md space-y-4 relative" id="ayah-card-${ayah.id}" data-surah="${escapeHtml(ayah.surah_name_tr)}" data-num="${ayah.ayah_number}" data-arabic="${encodeURIComponent(ayah.text_ar_uthmani)}" data-meal="${encodeURIComponent(ayah.text_tr_ozturk)}">
                    <!-- Üst Bilgi Barı -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-800/80 text-xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-950 text-emerald-400 font-bold border border-emerald-800/60">
                                ${escapeHtml(ayah.surah_name_tr)} ${ayah.ayah_number}. Ayet
                            </span>
                            <span class="text-gray-400">${escapeHtml(ayah.surah_name_ar)}</span>
                            <span class="text-gray-500">&bull;</span>
                            <span class="text-gray-400 font-mono">Cüz ${ayah.juz}, Sayfa ${ayah.page}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] ${ayah.revelation_type === 'Mekke' ? 'bg-amber-950/60 text-amber-400 border border-amber-800/40' : 'bg-blue-950/60 text-blue-400 border border-blue-800/40'}">
                                ${ayah.revelation_type}
                            </span>
                            ${ayah.sajdah ? `<span class="px-2 py-0.5 rounded-full bg-purple-950 text-purple-300 border border-purple-800 text-[10px]">Tilavet Secdesi</span>` : ''}
                        </div>

                        <!-- Aksiyon Butonları -->
                        <div class="flex items-center gap-1 text-gray-400 action-buttons flex-wrap">
                            <!-- Türkçe Meal Ses Oynat (Tok & Vakur Ses) -->
                            <button 
                                onclick="playAyahAudio(this, ${ayah.id}, ${playlistIndex}, 'tr')" 
                                data-surah="${escapeHtml(ayah.surah_name_tr)}"
                                data-num="${ayah.ayah_number}"
                                class="ayah-play-btn px-2 py-1.5 bg-amber-950/30 hover:bg-amber-950/60 text-amber-400 hover:text-amber-300 border border-amber-800/50 rounded-lg transition text-xs font-semibold flex items-center gap-1" 
                                title="Türkçe Meali Dinle (Tok & Vakur Ses - Hayri Küçükdeniz)"
                                id="play-tr-btn-${ayah.id}"
                            >
                                <i class="fa-solid fa-volume-high text-[11px]"></i> <span>Meal (Tok Ses)</span>
                            </button>

                            <!-- Arapça Orijinal Tilavet Oynat -->
                            <button 
                                onclick="playAyahAudio(this, ${ayah.id}, ${playlistIndex}, 'ar')" 
                                data-surah="${escapeHtml(ayah.surah_name_tr)}"
                                data-num="${ayah.ayah_number}"
                                class="ayah-play-btn px-2 py-1.5 bg-emerald-950/30 hover:bg-emerald-950/60 text-emerald-400 hover:text-emerald-300 border border-emerald-800/50 rounded-lg transition text-xs font-semibold flex items-center gap-1" 
                                title="Arapça Orijinal Tilavet Dinle"
                                id="play-ar-btn-${ayah.id}"
                            >
                                <i class="fa-solid fa-play text-[10px]"></i> <span>Arapça</span>
                            </button>

                            <!-- Favori / Yıldız -->
                            <button 
                                onclick="toggleFavorite(${ayah.id})" 
                                id="fav-btn-${ayah.id}"
                                class="p-2 hover:bg-gray-800 ${isFav ? 'text-amber-400' : 'hover:text-amber-400'} rounded-lg transition" 
                                title="Favorilere Ekle / Çıkar"
                            >
                                <i class="${isFav ? 'fa-solid' : 'fa-regular'} fa-star"></i>
                            </button>

                            <!-- WhatsApp Paylaş -->
                            <button 
                                onclick="shareAyahOnWhatsApp(${ayah.id})" 
                                class="p-2 hover:bg-emerald-900/30 text-emerald-500 hover:text-emerald-400 rounded-lg transition" 
                                title="Bu Ayeti WhatsApp'ta Paylaş"
                            >
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                            </button>

                            <!-- Not Ekle -->
                            <button 
                                onclick="toggleNoteBox(${ayah.id})" 
                                class="p-2 hover:bg-gray-800 hover:text-teal-400 rounded-lg transition" 
                                title="Bu Ayete Not Ekle"
                            >
                                <i class="fa-regular fa-note-sticky"></i>
                            </button>

                            <!-- Görsel Kart Oluştur -->
                            <button 
                                onclick="openImageModal(${ayah.id})" 
                                class="p-2 hover:bg-gray-800 hover:text-indigo-400 rounded-lg transition" 
                                title="Resim Olarak Paylaş / İndir"
                            >
                                <i class="fa-regular fa-image"></i>
                            </button>

                            <!-- Kopyala -->
                            <button 
                                onclick="copyAyah(${ayah.id})" 
                                class="p-2 hover:bg-gray-800 hover:text-emerald-400 rounded-lg transition" 
                                title="Arapça ve Meali Kopyala"
                            >
                                <i class="fa-regular fa-copy"></i>
                            </button>

                            <!-- Sureye Git -->
                            <button 
                                onclick="openSurah(${ayah.surah_id})" 
                                class="p-2 hover:bg-gray-800 hover:text-emerald-400 rounded-lg transition" 
                                title="Bu Sureyi Baştan Sona Oku"
                            >
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Arapça Orijinal Metin -->
                    <div class="arabic-text text-gray-100 font-quran select-text tracking-wide" style="font-size: ${currentArabicFontSize}px;" dir="rtl">
                        ${escapeHtml(ayah.text_ar_uthmani)}
                        <span class="inline-flex items-center justify-center w-7 h-7 mx-1 text-xs text-emerald-400 font-sans border border-emerald-800/80 rounded-full bg-emerald-950/60 font-semibold align-middle">
                            ${ayah.ayah_number}
                        </span>
                    </div>

                    <!-- Türkçe Okunuş (Transliteration) -->
                    <div class="transliteration-container ${showTransliteration ? '' : 'hidden'} bg-gray-950/60 border border-gray-800/70 p-3 rounded-xl text-xs text-emerald-300/80 italic font-mono leading-relaxed">
                        <span class="text-gray-500 font-sans not-italic font-bold mr-1">Okunuş:</span> ${escapeHtml(ayah.text_tr_transliteration || '')}
                    </div>

                    <!-- Türkçe Meali (Ana Meal) -->
                    <div class="text-sm sm:text-base text-gray-200 leading-relaxed space-y-1.5">
                        <div class="text-xs text-emerald-400/90 font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-book-open"></i> Türkçe Meali:
                        </div>
                        <p class="meal-content select-text font-normal">${highlightedMeal}</p>
                    </div>

                    <!-- Karşılaştırmalı Mealler Paneli (Accordion) -->
                    <div class="pt-2 border-t border-gray-800/60">
                        <button 
                            onclick="toggleComparison(${ayah.id})" 
                            class="text-xs text-gray-400 hover:text-emerald-400 transition flex items-center gap-1.5 font-medium"
                        >
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span>Diğer Meallerle Karşılaştır (Elmalılı, Süleyman Ateş, Diyanet)</span>
                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" id="compare-icon-${ayah.id}"></i>
                        </button>

                        <div id="compare-box-${ayah.id}" class="hidden mt-3 space-y-2.5 bg-gray-950/80 border border-gray-800/80 p-4 rounded-xl text-xs text-gray-300">
                            <div class="space-y-1">
                                <span class="font-bold text-amber-400 block">Elmalılı Hamdi Yazır:</span>
                                <p class="text-gray-300 leading-relaxed">${escapeHtml(ayah.text_tr_yazir || 'Meal bulunamadı')}</p>
                            </div>
                            <div class="space-y-1 pt-2 border-t border-gray-800/60">
                                <span class="font-bold text-cyan-400 block">Süleyman Ateş:</span>
                                <p class="text-gray-300 leading-relaxed">${escapeHtml(ayah.text_tr_ates || 'Meal bulunamadı')}</p>
                            </div>
                            <div class="space-y-1 pt-2 border-t border-gray-800/60">
                                <span class="font-bold text-teal-400 block">Diyanet İşleri:</span>
                                <p class="text-gray-300 leading-relaxed">${escapeHtml(ayah.text_tr_diyanet || 'Meal bulunamadı')}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Kişisel Not Alanı -->
                    <div id="note-box-${ayah.id}" class="${userNote ? '' : 'hidden'} bg-gray-950 border border-teal-900/50 p-3 rounded-xl text-xs space-y-2">
                        <div class="flex items-center justify-between text-teal-400 font-bold">
                            <span class="flex items-center gap-1"><i class="fa-regular fa-note-sticky"></i> Kişisel Notunuz:</span>
                            <button onclick="deleteNote(${ayah.id})" class="text-gray-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                        </div>
                        <textarea 
                            id="note-input-${ayah.id}" 
                            rows="2" 
                            placeholder="Bu ayetle ilgili çalışma notunuzu yazın..."
                            class="w-full bg-gray-900 border border-gray-800 rounded-lg p-2 text-white outline-none focus:border-teal-500 text-xs"
                        >${escapeHtml(userNote)}</textarea>
                        <div class="flex justify-end">
                            <button onclick="saveNote(${ayah.id})" class="px-3 py-1 bg-teal-700 hover:bg-teal-600 text-white rounded text-[11px] font-semibold transition">Notu Kaydet</button>
                        </div>
                    </div>

                    <!-- Gizli kopyalama verisi -->
                    <div id="copy-data-${ayah.id}" class="hidden">
${ayah.text_ar_uthmani}

Türkçe Meali:
"${ayah.text_tr_ozturk}"

[${ayah.surah_name_tr} Suresi, ${ayah.ayah_number}. Ayet]
                    </div>
                </div>
            `;
        }

        // Karşılaştırma Accordion Toggle
        function toggleComparison(id) {
            const box = document.getElementById(`compare-box-${id}`);
            const icon = document.getElementById(`compare-icon-${id}`);
            if (box.classList.contains('hidden')) {
                box.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                box.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        // Not Alanı Toggle
        function toggleNoteBox(id) {
            const box = document.getElementById(`note-box-${id}`);
            box.classList.toggle('hidden');
            if (!box.classList.contains('hidden')) {
                const input = document.getElementById(`note-input-${id}`);
                if (input) input.focus();
            }
        }

        function saveNote(id) {
            const val = document.getElementById(`note-input-${id}`).value.trim();
            const notes = JSON.parse(localStorage.getItem(STORAGE_NOTES) || '{}');
            if (val === '') {
                delete notes[id];
                document.getElementById(`note-box-${id}`).classList.add('hidden');
            } else {
                notes[id] = val;
            }
            localStorage.setItem(STORAGE_NOTES, JSON.stringify(notes));
            showToast('Notunuz kaydedildi!');
        }

        function deleteNote(id) {
            const notes = JSON.parse(localStorage.getItem(STORAGE_NOTES) || '{}');
            delete notes[id];
            localStorage.setItem(STORAGE_NOTES, JSON.stringify(notes));
            document.getElementById(`note-input-${id}`).value = '';
            document.getElementById(`note-box-${id}`).classList.add('hidden');
            showToast('Not silindi.');
        }

        function getAyahNote(id) {
            const notes = JSON.parse(localStorage.getItem(STORAGE_NOTES) || '{}');
            return notes[id] || '';
        }

        // Favoriler (Yer İmleri) Yönetimi
        function isFavorite(id) {
            const favs = JSON.parse(localStorage.getItem(STORAGE_FAVORITES) || '[]');
            return favs.includes(id);
        }

        function toggleFavorite(id) {
            let favs = JSON.parse(localStorage.getItem(STORAGE_FAVORITES) || '[]');
            const btn = document.getElementById(`fav-btn-${id}`);
            if (favs.includes(id)) {
                favs = favs.filter(x => x !== id);
                if (btn) {
                    btn.className = 'p-2 hover:bg-gray-800 hover:text-amber-400 rounded-lg transition';
                    btn.querySelector('i').className = 'fa-regular fa-star';
                }
                showToast('Ayet favorilerden çıkarıldı.');
            } else {
                favs.push(id);
                if (btn) {
                    btn.className = 'p-2 hover:bg-gray-800 text-amber-400 rounded-lg transition';
                    btn.querySelector('i').className = 'fa-solid fa-star';
                }
                showToast('Ayet favorilere eklendi!');
            }
            localStorage.setItem(STORAGE_FAVORITES, JSON.stringify(favs));
            updateFavoritesBadge();
        }

        function updateFavoritesBadge() {
            const favs = JSON.parse(localStorage.getItem(STORAGE_FAVORITES) || '[]');
            const badge = document.getElementById('favorites-count-badge');
            if (badge) badge.innerText = favs.length;
        }

        async function loadFavorites() {
            const favs = JSON.parse(localStorage.getItem(STORAGE_FAVORITES) || '[]');
            const container = document.getElementById('favorites-results');
            
            if (favs.length === 0) {
                container.innerHTML = `
                    <div class="py-16 text-center bg-gray-900/40 border border-gray-800 rounded-2xl p-8 space-y-3">
                        <i class="fa-regular fa-star text-amber-400 text-3xl"></i>
                        <h3 class="text-lg font-semibold text-white">Henüz Favori Ayetiniz Yok</h3>
                        <p class="text-xs text-gray-400">Ayetlerin sağ üstündeki yıldız ikonuna tıklayarak favorilerinize ekleyebilirsiniz.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="py-12 text-center">
                    <div class="inline-block w-8 h-8 border-4 border-emerald-500/30 border-t-emerald-500 rounded-full animate-spin"></div>
                    <p class="text-xs text-gray-400 mt-2">Favori ayetleriniz yükleniyor...</p>
                </div>
            `;

            try {
                const res = await fetch(`api.php?action=favorites&ids=${favs.join(',')}`);
                const data = await res.json();
                if (data.status === 'success') {
                    currentPlaylist = data.ayahs;
                    let html = '';
                    data.ayahs.forEach((ayah, idx) => {
                        html += generateAyahCard(ayah, [], idx);
                    });
                    container.innerHTML = html;
                }
            } catch(e) {
                container.innerHTML = `<p class="text-red-400 text-center py-8">Yükleme hatası oluştu.</p>`;
            }
        }

        function clearAllFavorites() {
            if (confirm('Tüm favori ayetlerinizi silmek istediğinize emin misiniz?')) {
                localStorage.setItem(STORAGE_FAVORITES, '[]');
                updateFavoritesBadge();
                loadFavorites();
                showToast('Tüm favoriler temizlendi.');
            }
        }

        // Son Kaldığım Yer Takibi
        function saveLastRead(surahName, ayahNum, surahId) {
            const data = { surahName, ayahNum, surahId, time: Date.now() };
            localStorage.setItem(STORAGE_LAST_READ, JSON.stringify(data));
            checkLastRead();
        }

        function checkLastRead() {
            const data = JSON.parse(localStorage.getItem(STORAGE_LAST_READ) || 'null');
            const banner = document.getElementById('last-read-banner');
            const textEl = document.getElementById('last-read-text');
            if (data && banner && textEl) {
                textEl.innerText = `${data.surahName} Suresi, ${data.ayahNum}. Ayet`;
                banner.classList.remove('hidden');
            }
        }

        function goToLastRead() {
            const data = JSON.parse(localStorage.getItem(STORAGE_LAST_READ) || 'null');
            if (data && data.surahId) {
                openSurah(data.surahId);
            }
        }

        function dismissLastRead() {
            document.getElementById('last-read-banner').classList.add('hidden');
        }

        // Metin Vurgulama (Highlight)
        function highlightText(text, terms) {
            if (!terms || terms.length === 0) return escapeHtml(text);
            let safeText = escapeHtml(text);

            terms.forEach(term => {
                if (!term || term.trim().length < 2) return;
                const cleanTerm = term.trim().replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                try {
                    const regex = new RegExp(`(${cleanTerm})`, 'gi');
                    safeText = safeText.replace(regex, '<mark class="highlight">$1</mark>');
                } catch(e) {}
            });

            return safeText;
        }

        // Sayfalama Çizimi
        function renderPagination(currentPage, totalPages) {
            const paginationEl = document.getElementById('search-pagination');
            paginationEl.classList.remove('hidden');

            let html = '';
            if (currentPage > 1) {
                html += `<button onclick="triggerSearch(${currentPage - 1})" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 rounded-lg text-xs text-gray-300 transition"><i class="fa-solid fa-chevron-left"></i> Önceki</button>`;
            }

            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            for (let i = startPage; i <= endPage; i++) {
                if (i === currentPage) {
                    html += `<button class="w-8 h-8 rounded-lg bg-emerald-600 text-white font-bold text-xs shadow">${i}</button>`;
                } else {
                    html += `<button onclick="triggerSearch(${i})" class="w-8 h-8 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs transition">${i}</button>`;
                }
            }

            if (currentPage < totalPages) {
                html += `<button onclick="triggerSearch(${currentPage + 1})" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 rounded-lg text-xs text-gray-300 transition">Sonraki <i class="fa-solid fa-chevron-right"></i></button>`;
            }

            paginationEl.innerHTML = html;
        }

        // Sure Okuma Modunu Aç
        async function openSurah(surahId) {
            activeReaderSurahId = surahId;
            switchView('reader');

            const headerTitle = document.getElementById('reader-surah-title');
            const headerArabic = document.getElementById('reader-surah-arabic');
            const headerInfo = document.getElementById('reader-surah-info');
            const headerRev = document.getElementById('reader-surah-revelation');
            const bismillahEl = document.getElementById('reader-bismillah');
            const listEl = document.getElementById('reader-ayahs-list');
            const prevBtn = document.getElementById('reader-prev-surah');
            const nextBtn = document.getElementById('reader-next-surah');

            if (surahId === 9) {
                bismillahEl.classList.add('hidden');
            } else {
                bismillahEl.classList.remove('hidden');
            }

            if (surahId > 1) {
                prevBtn.classList.remove('hidden');
                prevBtn.onclick = () => openSurah(surahId - 1);
            } else {
                prevBtn.classList.add('hidden');
            }

            if (surahId < 114) {
                nextBtn.classList.remove('hidden');
                nextBtn.onclick = () => openSurah(surahId + 1);
            } else {
                nextBtn.classList.add('hidden');
            }

            listEl.innerHTML = `
                <div class="py-16 text-center">
                    <div class="inline-block w-8 h-8 border-4 border-emerald-500/30 border-t-emerald-500 rounded-full animate-spin"></div>
                    <p class="text-xs text-gray-400 mt-2">Sure ayetleri yükleniyor...</p>
                </div>
            `;

            try {
                const res = await fetch(`api.php?action=surah&id=${surahId}&limit=500`);
                const data = await res.json();

                if (data.status === 'success') {
                    const s = data.surah;
                    currentPlaylist = data.ayahs;
                    headerTitle.innerText = `${s.id}. ${s.name_tr} Suresi (${s.name_en})`;
                    headerArabic.innerText = s.name_ar;
                    headerInfo.innerText = `${s.ayahs_count} Ayet`;
                    headerRev.innerText = `${s.revelation_type} Dönemi`;
                    headerRev.className = `px-2.5 py-0.5 rounded-full text-xs ${s.revelation_type === 'Mekke' ? 'bg-amber-950 text-amber-400 border border-amber-800' : 'bg-blue-950 text-blue-400 border border-blue-800'}`;

                    saveLastRead(s.name_tr, 1, s.id);

                    let html = '';
                    data.ayahs.forEach((ayah, idx) => {
                        html += generateAyahCard(ayah, [], idx);
                    });
                    listEl.innerHTML = html;
                }
            } catch(e) {
                listEl.innerHTML = `<p class="text-red-400 text-center py-8">Sure yüklenirken hata oluştu.</p>`;
            }
        }

        // Cüz Açma
        async function openJuz(juzId) {
            switchView('search');
            document.getElementById('search-input').value = '';
            
            const loadingEl = document.getElementById('search-loading');
            const resultsEl = document.getElementById('search-results');
            const emptyEl = document.getElementById('search-empty');
            const headerEl = document.getElementById('search-header');
            const summaryText = document.getElementById('search-summary-text');
            const countBadge = document.getElementById('results-count-badge');

            loadingEl.classList.remove('hidden');
            resultsEl.innerHTML = '';
            emptyEl.classList.add('hidden');

            try {
                const res = await fetch(`api.php?action=juz&id=${juzId}`);
                const data = await res.json();
                loadingEl.classList.add('hidden');

                if (data.status === 'success') {
                    currentPlaylist = data.ayahs;
                    headerEl.classList.remove('hidden');
                    summaryText.innerText = `${juzId}. Cüz Ayetleri`;
                    countBadge.innerText = `${data.ayahs.length} Ayet`;

                    let html = '';
                    data.ayahs.forEach((ayah, idx) => {
                        html += generateAyahCard(ayah, [], idx);
                    });
                    resultsEl.innerHTML = html;
                }
            } catch(e) {
                loadingEl.classList.add('hidden');
                showToast('Cüz yüklenirken hata oluştu', 'error');
            }
        }

        // Mushaf Sayfası Açma (1-604)
        async function openPage(pageNum) {
            activePageNumber = Math.max(1, Math.min(604, pageNum));
            document.getElementById('page-selector').value = activePageNumber;
            const container = document.getElementById('page-ayahs-list');
            
            container.innerHTML = `
                <div class="py-12 text-center">
                    <div class="inline-block w-8 h-8 border-4 border-emerald-500/30 border-t-emerald-500 rounded-full animate-spin"></div>
                    <p class="text-xs text-gray-400 mt-2">${activePageNumber}. Sayfa yükleniyor...</p>
                </div>
            `;

            try {
                const res = await fetch(`api.php?action=page&id=${activePageNumber}`);
                const data = await res.json();
                if (data.status === 'success') {
                    currentPlaylist = data.ayahs;
                    let html = '';
                    data.ayahs.forEach((ayah, idx) => {
                        html += generateAyahCard(ayah, [], idx);
                    });
                    container.innerHTML = html;
                }
            } catch(e) {
                container.innerHTML = `<p class="text-red-400 text-center py-8">Sayfa yükleme hatası</p>`;
            }
        }

        function changePageNumber(delta) {
            openPage(activePageNumber + delta);
        }

        // Secde Ayetlerini Yükle
        async function loadSajdahAyahs() {
            const container = document.getElementById('sajdah-results');
            container.innerHTML = `
                <div class="py-12 text-center">
                    <div class="inline-block w-8 h-8 border-4 border-emerald-500/30 border-t-emerald-500 rounded-full animate-spin"></div>
                    <p class="text-xs text-gray-400 mt-2">Secde ayetleri yükleniyor...</p>
                </div>
            `;

            try {
                const res = await fetch('api.php?action=sajdah');
                const data = await res.json();
                if (data.status === 'success') {
                    currentPlaylist = data.ayahs;
                    let html = '';
                    data.ayahs.forEach((ayah, idx) => {
                        html += generateAyahCard(ayah, [], idx);
                    });
                    container.innerHTML = html;
                }
            } catch(e) {
                container.innerHTML = `<p class="text-red-400 text-center py-8">Yükleme hatası</p>`;
            }
        }

        // Sure Listesi Filtresi
        function filterSurahsList(query) {
            const cleanQuery = query.toLowerCase().trim();
            const cards = document.querySelectorAll('.surah-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                if (name.includes(cleanQuery)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        // =========================================================================
        // SES OYNATICI (HEM TOK SESLİ TÜRKÇE MEAL HEM DE ARAPÇA TİLAVET)
        // =========================================================================
        const RECITER_NAMES = {
            'tr.vakfi-audio': 'Hayri Küçükdeniz (Diyanet Vakfı Türkçe Meal - Tok & Vakur Ses)',
            'ar.alafasy': 'Mishary Rashid Alafasy (Arapça Tilavet)',
            'ar.abdulbasit': 'Abdulbasit Abdussamed (Arapça Mücevved)',
            'ar.mahermuaiqly': 'Maher Al-Muaiqly (Kabe İmamı)',
            'ar.saadalghamidi': 'Saad Al-Ghamdi (Arapça)',
            'ar.shaatree': 'Abu Bakr Ash-Shatri (Arapça)'
        };

        function changeReciter(reciterId) {
            currentReciter = reciterId;
            document.getElementById('audio-subtitle').innerText = `Okuyan: ${RECITER_NAMES[reciterId] || reciterId}`;
            
            if (activeAudioAyahId) {
                const newUrl = getReciterAudioUrl(activeAudioAyahId, currentAudioMode);
                const audioEl = document.getElementById('audio-element');
                const isPlaying = !audioEl.paused;
                audioEl.src = newUrl;
                if (isPlaying) audioEl.play();
            }
            showToast(`Seslendirici seçildi: ${RECITER_NAMES[reciterId] || reciterId}`);
        }

        function getReciterAudioUrl(ayahId, mode = null) {
            const activeMode = mode || currentAudioMode;
            if (activeMode === 'tr') {
                return `https://cdn.islamic.network/quran/audio/128/tr.vakfi-audio/${ayahId}.mp3`;
            }
            const arReciter = currentReciter.startsWith('tr.') ? 'ar.alafasy' : currentReciter;
            return `https://cdn.islamic.network/quran/audio/128/${arReciter}/${ayahId}.mp3`;
        }

        function changeAudioSpeed(speed) {
            currentPlaybackSpeed = parseFloat(speed);
            const audioEl = document.getElementById('audio-element');
            if (audioEl) audioEl.playbackRate = currentPlaybackSpeed;
        }

        function toggleAutoplay() {
            isAutoplay = !isAutoplay;
            const lbl = document.getElementById('autoplay-status-label');
            if (lbl) {
                lbl.innerText = isAutoplay ? 'Açık' : 'Kapalı';
                lbl.className = isAutoplay ? 'text-emerald-400' : 'text-gray-500';
            }
            showToast(isAutoplay ? 'Otomatik sonraki ayet açıldı' : 'Otomatik sonraki ayet kapatıldı');
        }

        // Ses Oynatma Fonksiyonu
        function playAyahAudio(btnEl, ayahId, playlistIndex = -1, mode = 'tr') {
            const player = document.getElementById('global-audio-player');
            const audioEl = document.getElementById('audio-element');
            const titleEl = document.getElementById('audio-title');
            const subtitleEl = document.getElementById('audio-subtitle');
            const playBtn = document.getElementById('global-play-btn');

            currentAudioMode = mode;

            // Diğer ses çalma ikonlarını temizle
            document.querySelectorAll('.ayah-play-btn i, .ayah-play-tr-btn i').forEach(icon => {
                if (icon.parentElement.id.includes('-tr-')) {
                    icon.className = 'fa-solid fa-volume-high text-[11px]';
                } else {
                    icon.className = 'fa-solid fa-play text-[10px]';
                }
            });

            // Eğer aynı ayet ve aynı mod çalıyorsa durdur
            if (activeAudioAyahId === ayahId && !audioEl.paused) {
                audioEl.pause();
                playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
                return;
            }

            activeAudioAyahId = ayahId;
            currentPlaylistIndex = playlistIndex >= 0 ? playlistIndex : currentPlaylist.findIndex(a => a.id === ayahId);

            const url = getReciterAudioUrl(ayahId, mode);
            const surahName = (btnEl && btnEl.getAttribute('data-surah')) || '';
            const ayahNum = (btnEl && btnEl.getAttribute('data-num')) || '';

            audioEl.src = url;
            audioEl.playbackRate = currentPlaybackSpeed;
            audioEl.play().catch(e => {
                console.error('Audio playback error:', e);
                showToast('Ses dosyası yüklenirken hata oluştu', 'error');
            });

            player.classList.remove('translate-y-full');
            
            if (mode === 'tr') {
                titleEl.innerHTML = `<span>${surahName} ${ayahNum}. Ayet (Türkçe Meal)</span> <div class="audio-wave"><span></span><span></span><span></span></div>`;
                subtitleEl.innerText = `🎙️ Tok & Vakur Ses: Hayri Küçükdeniz (Diyanet Vakfı Meali)`;
            } else {
                titleEl.innerHTML = `<span>${surahName} ${ayahNum}. Ayet (Arapça Tilavet)</span> <div class="audio-wave"><span></span><span></span><span></span></div>`;
                const arReciter = currentReciter.startsWith('tr.') ? 'ar.alafasy' : currentReciter;
                subtitleEl.innerText = `🕌 Okuyan: ${RECITER_NAMES[arReciter] || 'Mishary Rashid Alafasy'}`;
            }

            playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';

            // Kartı odakla
            const cardEl = document.getElementById(`ayah-card-${ayahId}`);
            if (cardEl) {
                cardEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function toggleGlobalAudio() {
            const audioEl = document.getElementById('audio-element');
            const playBtn = document.getElementById('global-play-btn');

            if (audioEl.paused) {
                audioEl.play().catch(e => console.error(e));
                playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
            } else {
                audioEl.pause();
                playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            }
        }

        function closeAudioPlayer() {
            const player = document.getElementById('global-audio-player');
            const audioEl = document.getElementById('audio-element');
            audioEl.pause();
            activeAudioAyahId = null;
            player.classList.add('translate-y-full');
        }

        function onAudioEnded() {
            const playBtn = document.getElementById('global-play-btn');
            playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';

            // Otomatik sonraki ayete geç (Hatim modu)
            if (isAutoplay && currentPlaylistIndex >= 0 && currentPlaylistIndex < currentPlaylist.length - 1) {
                const nextIndex = currentPlaylistIndex + 1;
                const nextAyah = currentPlaylist[nextIndex];
                if (nextAyah) {
                    const btnEl = document.getElementById(`play-tr-btn-${nextAyah.id}`) || document.getElementById(`play-ar-btn-${nextAyah.id}`);
                    playAyahAudio(btnEl, nextAyah.id, nextIndex, currentAudioMode);
                }
            }
        }

        function onAudioTimeUpdate() {
            const audioEl = document.getElementById('audio-element');
            const timer = document.getElementById('audio-timer');
            const cur = Math.floor(audioEl.currentTime);
            const dur = Math.floor(audioEl.duration) || 0;
            const formatTime = s => `${Math.floor(s/60).toString().padStart(2,'0')}:${(s%60).toString().padStart(2,'0')}`;
            timer.innerText = `${formatTime(cur)} / ${formatTime(dur)}`;
        }

        // =========================================================================
        // SAHİH HADİSLER KÜLLİYATI MANTIĞI & ARAMA MOTORU
        // =========================================================================
        async function loadHadiths(category = null, query = null) {
            const targetCat = category !== null ? category : activeHadithCategory;
            const targetQ = query !== null ? query : (document.getElementById('hadith-search-input') ? document.getElementById('hadith-search-input').value.trim() : '');

            const loadingEl = document.getElementById('hadith-loading');
            const container = document.getElementById('hadiths-list-container');
            if (!container) return;

            loadingEl.classList.remove('hidden');
            container.innerHTML = '';

            try {
                const params = new URLSearchParams({
                    action: 'hadiths',
                    category: targetCat,
                    q: targetQ,
                    limit: 50
                });

                const res = await fetch(`api.php?${params.toString()}`);
                const data = await res.json();
                loadingEl.classList.add('hidden');

                if (data.status === 'success') {
                    currentHadithsData = data.data.results;
                    renderHadithCards(data.data.results, targetQ);
                } else {
                    showToast('Hadisler yüklenemedi', 'error');
                }
            } catch(e) {
                loadingEl.classList.add('hidden');
                container.innerHTML = `<p class="text-red-400 text-center py-8">Bağlantı hatası oluştu.</p>`;
            }
        }

        function filterHadithsByCategory(cat) {
            activeHadithCategory = cat;
            document.querySelectorAll('.hadith-cat-btn').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.classList.remove('bg-gray-800', 'text-gray-300');
                    btn.classList.add('bg-emerald-600', 'text-white');
                } else {
                    btn.classList.remove('bg-emerald-600', 'text-white');
                    btn.classList.add('bg-gray-800', 'text-gray-300');
                }
            });
            loadHadiths(cat);
        }

        function onHadithSearchInput() {
            const val = document.getElementById('hadith-search-input').value;
            const clearBtn = document.getElementById('hadith-clear-btn');
            if (val.trim() !== '') {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }

            clearTimeout(hadithSearchTimer);
            hadithSearchTimer = setTimeout(() => {
                loadHadiths(activeHadithCategory, val.trim());
            }, 300);
        }

        function clearHadithSearch() {
            document.getElementById('hadith-search-input').value = '';
            document.getElementById('hadith-clear-btn').classList.add('hidden');
            loadHadiths(activeHadithCategory, '');
        }

        function renderHadithCards(hadiths, query = '') {
            const container = document.getElementById('hadiths-list-container');
            if (!container) return;

            if (!hadiths || hadiths.length === 0) {
                container.innerHTML = `
                    <div class="py-16 text-center bg-gray-900/40 border border-gray-800 rounded-2xl p-8 space-y-3">
                        <i class="fa-solid fa-scroll text-amber-400 text-3xl"></i>
                        <h3 class="text-lg font-semibold text-white">Aramanıza Uygun Sahih Hadis Bulunamadı</h3>
                        <p class="text-xs text-gray-400">Farklı bir kelime veya kategori seçerek tekrar arayabilirsiniz.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            hadiths.forEach((h, index) => {
                html += `
                    <div class="hadith-card bg-gray-900/80 border border-gray-800 hover:border-emerald-700/60 rounded-2xl p-5 sm:p-6 transition-all duration-200 shadow-md space-y-4 relative" id="hadith-card-${h.id}">
                        <!-- Üst Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-800/80 pb-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-950 text-emerald-400 font-bold text-xs border border-emerald-800/60 flex items-center gap-1">
                                    <i class="fa-solid fa-tag text-[10px]"></i> ${escapeHtml(h.category_name)}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[11px] bg-emerald-900/40 text-emerald-300 border border-emerald-700/60 font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-emerald-400 text-[10px]"></i> ${escapeHtml(h.grade)}
                                </span>
                                <span class="text-xs text-amber-300/90 font-medium flex items-center gap-1">
                                    <i class="fa-regular fa-user text-[11px]"></i> Ravi: <b>${escapeHtml(h.narrator)}</b>
                                </span>
                            </div>

                            <!-- Aksiyon Butonları -->
                            <div class="flex items-center gap-1.5 text-gray-400">
                                <!-- Tok Sesle Dinle (Sesli Okuma) -->
                                <button 
                                    onclick="playHadithVoice(${h.id})" 
                                    class="px-2.5 py-1 bg-amber-950/30 hover:bg-amber-950/60 text-amber-400 hover:text-amber-300 border border-amber-800/50 rounded-lg transition text-xs font-semibold flex items-center gap-1"
                                    title="Hadis-i Şerifi Tok Sesle Dinle"
                                >
                                    <i class="fa-solid fa-volume-high text-xs"></i> <span>Dinle</span>
                                </button>

                                <!-- WhatsApp Paylaş -->
                                <button 
                                    onclick="shareHadithOnWhatsApp(${h.id})" 
                                    class="p-2 hover:bg-emerald-900/30 text-emerald-500 hover:text-emerald-400 rounded-lg transition" 
                                    title="WhatsApp'ta Paylaş"
                                >
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </button>

                                <!-- Görsel Kart Yap -->
                                <button 
                                    onclick="openHadithImageModal(${h.id})" 
                                    class="p-2 hover:bg-gray-800 hover:text-indigo-400 rounded-lg transition" 
                                    title="Sosyal Medya Görsel Kartı Oluştur"
                                >
                                    <i class="fa-regular fa-image"></i>
                                </button>

                                <!-- Kopyala -->
                                <button 
                                    onclick="copyHadith(${h.id})" 
                                    class="p-2 hover:bg-gray-800 hover:text-emerald-400 rounded-lg transition" 
                                    title="Metni Kopyala"
                                >
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Hadis Başlığı -->
                        <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-feather text-emerald-400 text-sm"></i> ${escapeHtml(h.title)}
                        </h3>

                        <!-- Orijinal Harekeli Arapça Hadis Metni -->
                        <div class="arabic-text text-gray-100 font-quran text-lg sm:text-xl py-1 text-right select-text leading-loose" dir="rtl">
                            ${escapeHtml(h.text_ar)}
                        </div>

                        <!-- Türkçe Tercüme -->
                        <div class="bg-gray-950/70 border border-gray-800/80 p-4 rounded-xl space-y-2">
                            <div class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-book-open"></i> Türkçe Tercüme & İzah:
                            </div>
                            <p class="text-sm sm:text-base text-gray-200 leading-relaxed font-normal select-text">
                                "${escapeHtml(h.text_tr)}"
                            </p>
                        </div>

                        <!-- Fıkhi & Ahlaki Hikmet Açıklaması -->
                        ${h.explanation ? `
                            <div class="text-xs text-amber-200/90 leading-relaxed bg-amber-950/20 border border-amber-900/30 p-3 rounded-lg flex items-start gap-2">
                                <i class="fa-solid fa-lightbulb text-amber-400 mt-0.5"></i>
                                <div>
                                    <b class="text-amber-300">Hikmet & Ahlaki Öğüt:</b> ${escapeHtml(h.explanation)}
                                </div>
                            </div>
                        ` : ''}

                        <!-- Muteber Kaynak ve İsnad Bilgisi -->
                        <div class="pt-2 border-t border-gray-800/60 flex items-center justify-between text-xs text-gray-400">
                            <span class="font-mono text-emerald-400/90 flex items-center gap-1">
                                <i class="fa-solid fa-book-bookmark"></i> <b>Kaynak:</b> ${escapeHtml(h.source)}
                            </span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // WhatsApp & Metin Temizleyici (Soru İşaretlerini ve Bozuk Karakterleri Önler)
        function cleanArabicForUniversal(text) {
            if (!text) return '';
            let s = text;
            // 1. Wasla Alif (ٱ) -> Standart Elif (ا)
            s = s.replace(/[\u0671]/g, '\u0627');
            // 2. Dagger Alif (ٰ) -> Standart Elif (ا)
            s = s.replace(/[\u0670]/g, '\u0627');
            // 3. Mushaf Küçük Cezm/Sukun (ۡ) -> Standart Sukun (ْ)
            s = s.replace(/[\u06E1]/g, '\u0652');
            // 4. Küçük Vav / Ye (ۥ ۦ) -> Standart Vav / Ye
            s = s.replace(/[\u06E5]/g, '\u0648').replace(/[\u06E6]/g, '\u064A');
            // 5. Windows ve WhatsApp URL kodlamasında bozulup ? (soru işareti) olan tüm mushaf durak işaretleri ve gizli kodlar
            s = s.replace(/[\u0615\u0653\u0656-\u065F\u06D6-\u06ED\u06DF\u06E0\u06E2-\u06E4\u06E7-\u06EA\uFD3E\uFD3F\uFEFF\u200B-\u200F\u00AD\uFFFD]/g, '');
            // 6. Yan yana birden fazla elif oluştuysa teke indir
            s = s.replace(/\u0627+/g, '\u0627');
            // 7. Fazla boşlukları temizle
            s = s.replace(/\s+/g, ' ');
            return s.trim();
        }

        function cleanForWhatsApp(text) {
            if (!text) return '';
            return text
                .replace(/[\u200B-\u200D\uFEFF\u00AD\u200E\u200F\u0080-\u009F\uFFFD]/g, '')
                .normalize('NFC')
                .trim();
        }

        // WhatsApp ile Sahih Hadis Paylaşımı (Sıfır Soru İşareti, Kusursuz Sade Tasarım)
        function shareHadithOnWhatsApp(hadithId) {
            const h = currentHadithsData.find(item => item.id === hadithId);
            if (!h) return;

            const cleanAr = cleanArabicForUniversal(h.text_ar);
            const cleanTr = cleanForWhatsApp(h.text_tr);
            const cleanExp = cleanForWhatsApp(h.explanation);

            let msg = `[ SAHÎH HADÎS-İ ŞERÎF ]\n`;
            msg += `*${h.title}*\n\n`;
            if (cleanAr) {
                msg += `${cleanAr}\n\n`;
            }
            msg += `Tercüme & İzah:\n`;
            msg += `> "${cleanTr}"\n\n`;
            msg += `Kaynak: ${h.source} [${h.grade}]\n`;
            msg += `Ravi: ${h.narrator}`;
            if (cleanExp) {
                msg += `\nÖğüt: ${cleanExp}`;
            }

            const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;
            window.open(url, '_blank');
        }

        // Hadis Görsel Kartı Oluşturma
        function openHadithImageModal(hadithId) {
            const h = currentHadithsData.find(item => item.id === hadithId);
            if (!h) return;

            currentCustomCard = {
                arabic: h.text_ar,
                meal: h.text_tr,
                title: h.title,
                verseRef: `${h.source} [${h.grade}]`,
                dua: `Ravi: ${h.narrator}`,
                theme: currentCustomCard.theme || 'cream'
            };

            renderCustomCard();
            document.getElementById('image-modal').classList.remove('hidden');
        }

        // Hadis Panoya Kopyalama
        function copyHadith(hadithId) {
            const h = currentHadithsData.find(item => item.id === hadithId);
            if (!h) return;

            let text = `${h.title}\n\n${h.text_ar}\n\n"${h.text_tr}"\n\nRavi: ${h.narrator}\nKaynak: ${h.source}\nSıhhat: ${h.grade}`;
            navigator.clipboard.writeText(text).then(() => {
                showToast('Sahih Hadis panoya kopyalandı!');
            });
        }

        // Hadis-i Şerifi Tok Sesle Canlı Seslendirme (Web Speech API)
        function playHadithVoice(hadithId) {
            const h = currentHadithsData.find(item => item.id === hadithId);
            if (!h) return;

            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel(); // Varsa eskiyi durdur
                const textToRead = `${h.title}. Peygamber Efendimiz buyuruyor ki: ${h.text_tr}. Kaynak: ${h.source}`;
                const utterance = new SpeechSynthesisUtterance(textToRead);
                utterance.lang = 'tr-TR';
                utterance.rate = 0.88; // Vakur ve sakin hız
                utterance.pitch = 0.80; // Tok ve kalın erkek tonu
                
                // Türkçe ses ara
                const voices = window.speechSynthesis.getVoices();
                const trVoice = voices.find(v => v.lang.includes('tr') || v.lang.includes('TR'));
                if (trVoice) utterance.voice = trVoice;

                window.speechSynthesis.speak(utterance);
                showToast('Hadis-i Şerif tok sesle seslendiriliyor...');
            } else {
                showToast('Tarayıcınız ses sentezini desteklemiyor', 'error');
            }
        }

        // =========================================================================
        // ÖZEL GÜNLER, CUMA & KANDİL TEBRİK VERİLERİ VE WHATSAPP PAYLAŞIMLARI
        // =========================================================================
        const GREETINGS_DATA = [
            {
                id: 'cuma-1',
                category: 'cuma',
                categoryLabel: 'Hayırlı Cumalar',
                icon: 'fa-kaaba',
                title: 'Hayırlı ve Bereketli Cumalar',
                arabic: 'رَبَّنَا آتِنَا مِن لَّدُنكَ رَحْمَةً وَهَيِّئْ لَنَا مِنْ أَمْرِنَا رَشَدًا',
                verseRef: 'Kehf Suresi, 10. Ayet',
                translation: 'Ey Rabbimiz! Bize katından bir rahmet ver ve içinde bulunduğumuz durumdan bize bir kurtuluş ve başarı yolu hazırla.',
                dua: 'Cumanız mübarek olsun. Rabbim gönlünüze ferahlık, hanenize bereket, ömrünüze sağlık ihsan eylesin.'
            },
            {
                id: 'cuma-2',
                category: 'cuma',
                categoryLabel: 'Hayırlı Cumalar',
                icon: 'fa-kaaba',
                title: 'Nurlu ve Huzurlu Cumalar',
                arabic: 'يَا أَيُّهَا الَّذِينَ آمَنُوا إِذَا نُودِيَ لِلصَّلَاةِ مِن يَوْمِ الْجُمُعَةِ فَاسْعَوْا إِلَىٰ ذِكْرِ اللَّهِ',
                verseRef: 'Cuma Suresi, 9. Ayet',
                translation: 'Ey iman edenler! Cuma günü namaz için çağrı yapıldığında, hemen Allah’ın zikrine koşun ve alışverişi bırakın. Eğer bilirseniz bu sizin için daha hayırlıdır.',
                dua: 'Rabbim dualarınızı kabul, kalbinizi sevgisiyle doldursun. Hayırlı Cumalar.'
            },
            {
                id: 'cuma-3',
                category: 'cuma',
                categoryLabel: 'Hayırlı Cumalar',
                icon: 'fa-kaaba',
                title: 'Gönül Aydınlığı Cuma Mesajı',
                arabic: 'اللَّهُ لَطِيفٌ بِعِبَادِهِ يَرْزُقُ مَن يَشَاءُ ۖ وَهُوَ الْقَوِيُّ الْعَزِيزُ',
                verseRef: 'Şûra Suresi, 19. Ayet',
                translation: 'Allah, kullarına karşı çok lütufkârdır. Dilediğini rızıklandırır. O güçlüdür, üstündür.',
                dua: 'Allah’ın sonsuz lütfu ve rahmeti üzerinize olsun. Selam ve dua ile hayırlı cumalar.'
            },
            {
                id: 'ramazan-1',
                category: 'ramazan',
                categoryLabel: 'Ramazan-ı Şerif',
                icon: 'fa-moon',
                title: 'Hayırlı Ramazanlar & Bereketli İftarlar',
                arabic: 'شَهْرُ رَمَضَانَ الَّذِي أُنزِلَ فِيهِ الْقُرْآنُ هُدًى لِّلنَّاسِ وَبَيِّنَاتٍ مِّنَ الْهُدَىٰ وَالْفُرْقَانِ',
                verseRef: 'Bakara Suresi, 185. Ayet',
                translation: 'O Ramazan ayı ki, insanlara bir rehber, hidayetin ve doğruyu yanlıştan ayıran ölçünün apaçık delilleri olarak Kur\'an onda indirilmiştir.',
                dua: 'On bir ayın sultanı Ramazan-ı Şerif hanenize huzur, sofranıza bereket getirsin. Oruçlarınız kabul olsun.'
            },
            {
                id: 'ramazan-2',
                category: 'ramazan',
                categoryLabel: 'Ramazan & Dua',
                icon: 'fa-moon',
                title: 'İcabet ve Mağfiret Vakti',
                arabic: 'وَإِذَا سَأَلَكَ عِبَادِي عَنِّي فَإِنِّي قَرِيبٌ ۖ أُجِيبُ دَعْوَةَ الدَّاعِ إِذَا دَعَانِ',
                verseRef: 'Bakara Suresi, 186. Ayet',
                translation: 'Kullarım sana beni sorduklarında, şüphesiz ben çok yakınım. Bana dua ettiği vakit dua edenin dileğine karşılık veririm.',
                dua: 'Tuttuğunuz oruçlar, ettiğiniz samimi dualar dergâh-ı izzette kabul olsun. Hayırlı Ramazanlar.'
            },
            {
                id: 'kadir-1',
                category: 'kadir',
                categoryLabel: 'Kadir Gecesi',
                icon: 'fa-star',
                title: 'Kadir Gecemiz Mübarek Olsun',
                arabic: 'لَيْلَةُ الْقَدْرِ خَيْرٌ مِّنْ أَلْفِ شَهْرٍ',
                verseRef: 'Kadir Suresi, 3. Ayet',
                translation: 'Kadir gecesi bin aydan daha hayırlıdır. Melekler ve Ruh, Rablerinin izniyle her iş için o gecede iner de iner. Bir esenliktir o; ta şafak sökünceye kadar.',
                dua: 'Bin aydan hayırlı bu kutlu gecede Rabbim günahlarımızı bağışlasın, kalplerimize hidayet ve esenlik indirsin.'
            },
            {
                id: 'bayram-1',
                category: 'bayram',
                categoryLabel: 'Bayram Tebriği',
                icon: 'fa-star-and-crescent',
                title: 'Ramazan & Kurban Bayramı Tebriği',
                arabic: 'إِنَّمَا الْمُؤْمِنُونَ إِخْوَةٌ فَأَصْلِحُوا بَيْنَ أَخَوَيْكُمْ',
                verseRef: 'Hucurat Suresi, 10. Ayet',
                translation: 'Müminler ancak kardeştirler. Öyleyse kardeşlerinizin arasını düzeltin ve Allah\'tan korkun ki esirgenesiniz.',
                dua: 'Sevdiklerinizle birlikte sağlık, neşe, huzur ve barış dolu nice bayramlara kavuşmanız dileğiyle. Bayramınız kutlu olsun.'
            },
            {
                id: 'kandil-1',
                category: 'kandil',
                categoryLabel: 'Kandil Tebriği',
                icon: 'fa-wand-magic-sparkles',
                title: 'Miraç & Berat Kandili Tebriği',
                arabic: 'سُبْحَانَ الَّذِي أَسْرَىٰ بِعَبْدِهِ لَيْلًا مِّنَ الْمَسْجِدِ الْحَرَامِ إِلَى الْمَسْجِدِ الْأَقْصَى',
                verseRef: 'İsra Suresi, 1. Ayet',
                translation: 'Kutludur o Zat ki, kulunu geceleyin Mescid-i Haram\'dan çevresini bereketlendirdiğimiz Mescid-i Aksa\'ya yürüttü.',
                dua: 'Kandiliniz mübarek olsun. Bu nurlu gecenin tüm İslam âlemine esenlik, adalet, merhamet ve aydınlık getirmesini dilerim.'
            },
            {
                id: 'kandil-2',
                category: 'kandil',
                categoryLabel: 'Mevlid Kandili',
                icon: 'fa-wand-magic-sparkles',
                title: 'Mevlid Kandiliniz Mübarek Olsun',
                arabic: 'وَمَا أَرْسَلْنَاكَ إِلَّا رَحْمَةً لِّلْعَالَمِينَ',
                verseRef: 'Enbiya Suresi, 107. Ayet',
                translation: 'Biz seni ancak âlemlere bir rahmet olarak gönderdik.',
                dua: 'Âlemlere rahmet olarak gönderilen Peygamber Efendimizin ahlakıyla ahlaklanmayı Rabbim cümlemize nasip etsin. Hayırlı kandiller.'
            },
            {
                id: 'dua-1',
                category: 'dua',
                categoryLabel: 'İnşirah & Şifa',
                icon: 'fa-hands-praying',
                title: 'Gönül Ferahlığı & Şifa Duası',
                arabic: 'فَإِنَّ مَعَ الْعُسْرِ يُسْرًا ﴿٥﴾ إِنَّ مَعَ الْعُسْرِ يُسْرًا',
                verseRef: 'İnşirah Suresi, 5-6. Ayetler',
                translation: 'Elbette zorluğun yanında bir kolaylık vardır. Gerçekten zorlukla beraber bir kolaylık vardır.',
                dua: 'Rabbim daralan göğsünüze inşirah ferahlığı, tüm dertlerinize deva, hastalarınıza acil şifalar lütfeylesin.'
            },
            {
                id: 'dua-2',
                category: 'dua',
                categoryLabel: 'Sabır & Şükür',
                icon: 'fa-hands-praying',
                title: 'Şükür ve Nimet Duası',
                arabic: 'لَئِن شَكَرْتُمْ لَأَزِيدَنَّكُمْ',
                verseRef: 'İbrahim Suresi, 7. Ayet',
                translation: 'Andolsun, eğer şükrederseniz size olan nimetimi mutlaka artırırım.',
                dua: 'Sahip olduğumuz tüm güzellikler için Yüce Yaradan’a şükürler olsun. Rabbim şükrümüzü ve nimetlerimizi artırsın.'
            },
            {
                id: 'ozturk-1',
                category: 'ozturk',
                categoryLabel: 'Tefekkür & Hikmet',
                icon: 'fa-feather-pointed',
                title: 'Kur\'an ile Aydınlanma & Tefekkür',
                arabic: 'أَفَلَا يَتَدَبَّرُونَ الْقُرْآنَ أَمْ عَلَىٰ قُلُوبٍ أَقْفَالُهَا',
                verseRef: 'Muhammed Suresi, 24. Ayet',
                translation: 'Onlar Kur\'an\'ı derin derin düşünmüyorlar mı? Yoksa kalpleri üzerinde kilitler mi var?',
                dua: 'Rabbim bizleri Kur\'an-ı Kerim\'i akıl, samimiyet ve ahlakla tefekkür edenlerden eylesin.'
            }
        ];

        let activeGreetingsCategory = 'all';
        let currentCustomCard = {
            arabic: '',
            meal: '',
            title: '',
            verseRef: '',
            theme: 'cream'
        };

        function initGreetingsView() {
            renderGreetingsCards(activeGreetingsCategory);
        }

        function filterGreetings(cat) {
            activeGreetingsCategory = cat;
            document.querySelectorAll('.greeting-filter-btn').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.classList.remove('bg-gray-800', 'text-gray-300');
                    btn.classList.add('bg-emerald-600', 'text-white');
                } else {
                    btn.classList.remove('bg-emerald-600', 'text-white');
                    btn.classList.add('bg-gray-800', 'text-gray-300');
                }
            });
            renderGreetingsCards(cat);
        }

        function renderGreetingsCards(cat) {
            const container = document.getElementById('greetings-cards-container');
            if (!container) return;

            const filtered = cat === 'all' ? GREETINGS_DATA : GREETINGS_DATA.filter(g => g.category === cat);
            
            let html = '';
            filtered.forEach((g, index) => {
                const globalIndex = GREETINGS_DATA.findIndex(item => item.id === g.id);
                html += `
                    <div class="greetings-card bg-gray-900/80 border border-gray-800 hover:border-emerald-700/60 rounded-2xl p-5 sm:p-6 transition-all duration-200 shadow-md space-y-4 relative flex flex-col justify-between">
                        <div class="space-y-3">
                            <!-- Kart Üst Bar -->
                            <div class="flex items-center justify-between gap-2 border-b border-gray-800/80 pb-3">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-950 text-emerald-400 font-bold text-xs border border-emerald-800/60 flex items-center gap-1.5">
                                        <i class="fa-solid ${g.icon} text-[11px]"></i> ${escapeHtml(g.categoryLabel)}
                                    </span>
                                    <span class="text-xs text-gray-400 font-medium">${escapeHtml(g.verseRef || '')}</span>
                                </div>
                                <span class="text-amber-400 text-xs font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-sparkles text-[10px]"></i> Tebrik
                                </span>
                            </div>

                            <!-- Başlık -->
                            <h3 class="font-bold text-white text-base sm:text-lg flex items-center gap-2">
                                ${escapeHtml(g.title)}
                            </h3>

                            <!-- Arapça Metin (Varsa) -->
                            ${g.arabic ? `
                                <div class="arabic-text text-gray-100 font-quran text-lg sm:text-xl py-1 text-right" dir="rtl">
                                    ${escapeHtml(g.arabic)}
                                </div>
                            ` : ''}

                            <!-- Türkçe Meali -->
                            <div class="bg-gray-950/70 p-3.5 rounded-xl border border-gray-800 text-xs sm:text-sm text-gray-200 leading-relaxed space-y-1">
                                <div class="text-[11px] font-bold text-emerald-400 flex items-center gap-1">
                                    <i class="fa-solid fa-book-quran"></i> Türkçe Meali:
                                </div>
                                <p class="italic text-gray-300">"${escapeHtml(g.translation)}"</p>
                            </div>

                            <!-- Dua / Mesaj -->
                            <p class="text-xs sm:text-sm text-amber-200/90 leading-relaxed font-medium bg-amber-950/20 border border-amber-900/30 p-2.5 rounded-lg">
                                🤲 ${escapeHtml(g.dua)}
                            </p>
                        </div>

                        <!-- Aksiyon Butonları -->
                        <div class="pt-3 border-t border-gray-800/80 flex flex-wrap items-center justify-between gap-2 mt-4">
                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <!-- WhatsApp Gönder -->
                                <button 
                                    onclick="shareGreetingOnWhatsApp(${globalIndex})" 
                                    class="flex-1 sm:flex-initial px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-md shadow-emerald-950/30"
                                >
                                    <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp'ta Gönder
                                </button>
                                <!-- Görsel Kart Yap -->
                                <button 
                                    onclick="openGreetingImageModal(${globalIndex})" 
                                    class="p-2 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white border border-gray-700 rounded-xl text-xs transition" 
                                    title="Sosyal Medya Kartı Oluştur"
                                >
                                    <i class="fa-regular fa-image"></i>
                                </button>
                                <!-- Kopyala -->
                                <button 
                                    onclick="copyGreetingText(${globalIndex})" 
                                    class="p-2 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white border border-gray-700 rounded-xl text-xs transition" 
                                    title="Metni Kopyala"
                                >
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // WhatsApp Karakter & Metin Temizleyici (Soru İşaretlerini ve Bozuk Karakterleri Önler)
        function cleanForWhatsApp(text) {
            if (!text) return '';
            return text
                // Görünmez kontrol karakterleri ve geçersiz ayırıcıları temizle
                .replace(/[\u200B-\u200D\uFEFF\u00AD\u200E\u200F\u0080-\u009F]/g, '')
                // WhatsApp URL kodlamasında bozulabilen özel mushaf durak işaretlerini temizle
                .replace(/[\u06D6-\u06ED\uFD3E\uFD3F]/g, '')
                .normalize('NFC')
                .trim();
        }

        // WhatsApp ile Ayet Paylaşımı (Sadece Kur'an-ı Kerim, Ayet ve Meali - Sıfır Soru İşareti, Kusursuz Tasarım)
        function shareAyahOnWhatsApp(id) {
            const cardEl = document.getElementById(`ayah-card-${id}`);
            if (!cardEl) return;

            let surah = cardEl.getAttribute('data-surah') || '';
            let num = cardEl.getAttribute('data-num') || '';
            let rawArabic = cardEl.getAttribute('data-arabic') ? decodeURIComponent(cardEl.getAttribute('data-arabic')) : '';
            let rawMeal = cardEl.getAttribute('data-meal') ? decodeURIComponent(cardEl.getAttribute('data-meal')) : '';

            if (!rawArabic || !rawMeal) {
                const arEl = cardEl.querySelector('.arabic-text');
                const mealEl = cardEl.querySelector('.meal-content');
                if (arEl) rawArabic = arEl.innerText.replace(/\d+/g, '').trim();
                if (mealEl) rawMeal = mealEl.innerText.trim();
            }

            const cleanArabic = cleanArabicForUniversal(rawArabic);
            const cleanMeal = cleanForWhatsApp(rawMeal);
            const ref = `${surah} Suresi, ${num}. Ayet`;

            let msg = `[ ${ref} ]\n\n`;
            if (cleanArabic) {
                msg += `${cleanArabic}\n\n`;
            }
            msg += `Türkçe Meali:\n`;
            msg += `> "${cleanMeal}"`;

            const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;
            window.open(url, '_blank');
        }

        // WhatsApp ile Tebrik Mesajı Paylaşımı (Temiz, Estetik ve Dualı)
        function shareGreetingOnWhatsApp(index) {
            const g = GREETINGS_DATA[index];
            if (!g) return;
            const senderInput = document.getElementById('card-sender-input');
            const sender = senderInput ? senderInput.value.trim() : '';

            const cleanAr = cleanArabicForUniversal(g.arabic);
            const cleanMeal = cleanForWhatsApp(g.translation);
            const cleanDua = cleanForWhatsApp(g.dua);

            let msg = `*${g.title.toUpperCase()}*\n\n`;
            if (cleanAr) {
                msg += `${cleanAr}\n\n`;
            }
            if (g.verseRef) {
                msg += `[ ${g.verseRef} ]\n`;
            }
            msg += `Türkçe Meali:\n`;
            msg += `> "${cleanMeal}"\n\n`;
            if (cleanDua) {
                msg += `Dua:\n> "${cleanDua}"\n\n`;
            }
            if (sender) {
                msg += `Tebrik Eden: ${sender}\n`;
            }

            const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;
            window.open(url, '_blank');
        }

        function copyGreetingText(index) {
            const g = GREETINGS_DATA[index];
            if (!g) return;

            const cleanAr = cleanArabicForUniversal(g.arabic);
            const cleanMeal = cleanForWhatsApp(g.translation);
            const cleanDua = cleanForWhatsApp(g.dua);

            let msg = `*${g.title.toUpperCase()}*\n\n`;
            if (cleanAr) msg += `${cleanAr}\n\n`;
            if (g.verseRef) msg += `[ ${g.verseRef} ]\n`;
            msg += `Türkçe Meali:\n"${cleanMeal}"\n\n`;
            if (cleanDua) msg += `Dua:\n"${cleanDua}"\n`;

            navigator.clipboard.writeText(msg).then(() => {
                showToast('Tebrik mesajı panoya kopyalandı!');
            });
        }

        // =========================================================================
        // GELİŞMİŞ GÖRSEL KART OLUŞTURUCU (CANVAS - 5 AYRI ESTETİK TEMA)
        // =========================================================================
        function openImageModal(ayahId) {
            const cardEl = document.getElementById(`ayah-card-${ayahId}`);
            if (!cardEl) return;
            const arabicText = cardEl.querySelector('.arabic-text') ? cardEl.querySelector('.arabic-text').innerText.replace(/\d+/g, '').trim() : '';
            const mealText = cardEl.querySelector('.meal-content') ? cardEl.querySelector('.meal-content').innerText.trim() : '';
            const surahBadge = cardEl.querySelector('.bg-emerald-950') ? cardEl.querySelector('.bg-emerald-950').innerText.trim() : '';

            currentCustomCard = {
                arabic: arabicText,
                meal: mealText,
                title: "KUR'AN-I KERİM MEALİ",
                verseRef: surahBadge,
                theme: currentCustomCard.theme || 'cream'
            };

            renderCustomCard();
            document.getElementById('image-modal').classList.remove('hidden');
        }

        function openGreetingImageModal(index) {
            const g = GREETINGS_DATA[index];
            if (!g) return;

            currentCustomCard = {
                arabic: g.arabic || '',
                meal: g.translation,
                title: g.title,
                verseRef: g.verseRef,
                dua: g.dua,
                theme: currentCustomCard.theme || 'cream'
            };

            renderCustomCard();
            document.getElementById('image-modal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('image-modal').classList.add('hidden');
        }

        function setCardTheme(themeName) {
            currentCustomCard.theme = themeName;
            document.querySelectorAll('.card-theme-selector').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-emerald-500', 'shadow-md');
            });
            const activeBtn = document.getElementById(`theme-btn-${themeName}`);
            if (activeBtn) activeBtn.classList.add('ring-2', 'ring-emerald-500', 'shadow-md');
            renderCustomCard();
        }

        function onCardCustomizerChange() {
            renderCustomCard();
        }

        function shareCurrentCardOnWhatsApp() {
            const sender = document.getElementById('card-sender-input') ? document.getElementById('card-sender-input').value.trim() : '';
            const cleanAr = cleanForWhatsApp(currentCustomCard.arabic);
            const cleanMeal = cleanForWhatsApp(currentCustomCard.meal);
            const cleanDua = cleanForWhatsApp(currentCustomCard.dua);

            let msg = `*بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ*\n\n`;
            if (currentCustomCard.title) {
                msg += `✨ *${currentCustomCard.title.toUpperCase()}* ✨\n\n`;
            }
            if (cleanAr) {
                msg += `${cleanAr}\n\n`;
            }
            if (currentCustomCard.verseRef) {
                msg += `📖 *[ ${currentCustomCard.verseRef} ]*\n`;
            }
            msg += `*Türkçe Meali:*\n`;
            msg += `> "${cleanMeal}"\n\n`;
            if (cleanDua) {
                msg += `🤲 ${cleanDua}\n\n`;
            }
            if (sender) {
                msg += `✍️ *${sender}*\n`;
            }

            const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;
            window.open(url, '_blank');
        }

        function renderCustomCard() {
            const canvas = document.getElementById('card-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const w = canvas.width;
            const h = canvas.height;
            const theme = currentCustomCard.theme || 'cream';
            const senderText = document.getElementById('card-sender-input') ? document.getElementById('card-sender-input').value.trim() : '';

            // 1. Arka Plan & Temalar
            if (theme === 'cream') {
                // Sıcak Ferah Krem & Tezhip Dokusu
                const grad = ctx.createLinearGradient(0, 0, w, h);
                grad.addColorStop(0, '#fbf8f0');
                grad.addColorStop(0.5, '#f5edd8');
                grad.addColorStop(1, '#ede2c4');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, w, h);

                // Dış ve İç Yaldız Çerçeveler
                ctx.strokeStyle = '#cca97e';
                ctx.lineWidth = 14;
                ctx.strokeRect(35, 35, w - 70, h - 70);

                ctx.strokeStyle = 'rgba(180, 83, 9, 0.4)';
                ctx.lineWidth = 3;
                ctx.strokeRect(55, 55, w - 110, h - 110);

                // Köşe Süsleri
                drawCornerMotifs(ctx, w, h, '#cca97e');

                // Başlık
                ctx.fillStyle = '#047857';
                ctx.font = 'bold 36px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(currentCustomCard.title || "KUR'AN-I KERİM MEALİ", w / 2, 130);

                ctx.fillStyle = '#b45309';
                ctx.font = 'italic 24px Inter, sans-serif';
                ctx.fillText("Açıklamalı Türkçe Meali", w / 2, 175);

                // Ayırıcı çizgi
                ctx.strokeStyle = 'rgba(180, 83, 9, 0.3)';
                ctx.beginPath();
                ctx.moveTo(260, 205);
                ctx.lineTo(w - 260, 205);
                ctx.stroke();

                // Arapça
                if (currentCustomCard.arabic) {
                    ctx.fillStyle = '#1c1917';
                    ctx.font = '44px "Amiri Quran", Amiri, serif';
                    ctx.direction = 'rtl';
                    wrapText(ctx, currentCustomCard.arabic, w / 2, 290, w - 180, 70);
                }

                // Meal
                ctx.fillStyle = '#382a1e';
                ctx.font = 'italic 32px Inter, sans-serif';
                ctx.direction = 'ltr';
                const mealY = currentCustomCard.arabic ? 600 : 440;
                wrapText(ctx, `"${currentCustomCard.meal}"`, w / 2, mealY, w - 200, 50);

                // Alt Bilgi
                ctx.fillStyle = '#047857';
                ctx.font = 'bold 28px Inter, sans-serif';
                ctx.fillText(`[ ${currentCustomCard.verseRef || "Kur'an-ı Kerim"} ]`, w / 2, 940);

                if (senderText) {
                    ctx.fillStyle = '#78350f';
                    ctx.font = '22px Inter, sans-serif';
                    ctx.fillText(senderText, w / 2, 990);
                }
            } else if (theme === 'emerald') {
                // Zümrüt Yeşili & Altın Yaldız
                const grad = ctx.createLinearGradient(0, 0, w, h);
                grad.addColorStop(0, '#022c22');
                grad.addColorStop(0.5, '#064e3b');
                grad.addColorStop(1, '#021e17');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, w, h);

                ctx.strokeStyle = '#f59e0b';
                ctx.lineWidth = 14;
                ctx.strokeRect(35, 35, w - 70, h - 70);

                ctx.strokeStyle = 'rgba(245, 158, 11, 0.4)';
                ctx.lineWidth = 3;
                ctx.strokeRect(55, 55, w - 110, h - 110);

                drawCornerMotifs(ctx, w, h, '#f59e0b');

                ctx.fillStyle = '#10b981';
                ctx.font = 'bold 36px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(currentCustomCard.title || "KUR'AN-I KERİM MEALİ", w / 2, 130);

                ctx.fillStyle = '#fbbf24';
                ctx.font = '24px Inter, sans-serif';
                ctx.fillText("Açıklamalı Türkçe Meali", w / 2, 175);

                ctx.strokeStyle = 'rgba(251, 191, 36, 0.3)';
                ctx.beginPath();
                ctx.moveTo(260, 205);
                ctx.lineTo(w - 260, 205);
                ctx.stroke();

                if (currentCustomCard.arabic) {
                    ctx.fillStyle = '#ffffff';
                    ctx.font = '44px "Amiri Quran", Amiri, serif';
                    ctx.direction = 'rtl';
                    wrapText(ctx, currentCustomCard.arabic, w / 2, 290, w - 180, 70);
                }

                ctx.fillStyle = '#ecfdf5';
                ctx.font = 'italic 32px Inter, sans-serif';
                ctx.direction = 'ltr';
                const mealY = currentCustomCard.arabic ? 600 : 440;
                wrapText(ctx, `"${currentCustomCard.meal}"`, w / 2, mealY, w - 200, 50);

                ctx.fillStyle = '#fbbf24';
                ctx.font = 'bold 28px Inter, sans-serif';
                ctx.fillText(`[ ${currentCustomCard.verseRef || "Kur'an-ı Kerim"} ]`, w / 2, 940);

                if (senderText) {
                    ctx.fillStyle = '#a7f3d0';
                    ctx.font = '22px Inter, sans-serif';
                    ctx.fillText(senderText, w / 2, 990);
                }
            } else if (theme === 'night') {
                // Gece & Hilal
                const grad = ctx.createLinearGradient(0, 0, w, h);
                grad.addColorStop(0, '#0f172a');
                grad.addColorStop(0.5, '#020617');
                grad.addColorStop(1, '#090d16');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, w, h);

                ctx.strokeStyle = '#38bdf8';
                ctx.lineWidth = 12;
                ctx.strokeRect(35, 35, w - 70, h - 70);

                ctx.strokeStyle = 'rgba(56, 189, 248, 0.3)';
                ctx.lineWidth = 3;
                ctx.strokeRect(55, 55, w - 110, h - 110);

                drawCornerMotifs(ctx, w, h, '#38bdf8');

                ctx.fillStyle = '#38bdf8';
                ctx.font = 'bold 36px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(currentCustomCard.title || "KUR'AN-I KERİM MEALİ", w / 2, 130);

                ctx.fillStyle = '#fde047';
                ctx.font = '24px Inter, sans-serif';
                ctx.fillText("Açıklamalı Türkçe Meali", w / 2, 175);

                ctx.strokeStyle = 'rgba(56, 189, 248, 0.3)';
                ctx.beginPath();
                ctx.moveTo(260, 205);
                ctx.lineTo(w - 260, 205);
                ctx.stroke();

                if (currentCustomCard.arabic) {
                    ctx.fillStyle = '#ffffff';
                    ctx.font = '44px "Amiri Quran", Amiri, serif';
                    ctx.direction = 'rtl';
                    wrapText(ctx, currentCustomCard.arabic, w / 2, 290, w - 180, 70);
                }

                ctx.fillStyle = '#f1f5f9';
                ctx.font = 'italic 32px Inter, sans-serif';
                ctx.direction = 'ltr';
                const mealY = currentCustomCard.arabic ? 600 : 440;
                wrapText(ctx, `"${currentCustomCard.meal}"`, w / 2, mealY, w - 200, 50);

                ctx.fillStyle = '#38bdf8';
                ctx.font = 'bold 28px Inter, sans-serif';
                ctx.fillText(`[ ${currentCustomCard.verseRef || "Kur'an-ı Kerim"} ]`, w / 2, 940);

                if (senderText) {
                    ctx.fillStyle = '#94a3b8';
                    ctx.font = '22px Inter, sans-serif';
                    ctx.fillText(senderText, w / 2, 990);
                }
            } else if (theme === 'rose') {
                // Gül Kurusu & Bordo
                const grad = ctx.createLinearGradient(0, 0, w, h);
                grad.addColorStop(0, '#4c0519');
                grad.addColorStop(0.5, '#1f0208');
                grad.addColorStop(1, '#2d040e');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, w, h);

                ctx.strokeStyle = '#fb7185';
                ctx.lineWidth = 12;
                ctx.strokeRect(35, 35, w - 70, h - 70);

                ctx.strokeStyle = 'rgba(251, 113, 133, 0.3)';
                ctx.lineWidth = 3;
                ctx.strokeRect(55, 55, w - 110, h - 110);

                drawCornerMotifs(ctx, w, h, '#fb7185');

                ctx.fillStyle = '#fda4af';
                ctx.font = 'bold 36px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(currentCustomCard.title || "KUR'AN-I KERİM MEALİ", w / 2, 130);

                ctx.fillStyle = '#fef08a';
                ctx.font = '24px Inter, sans-serif';
                ctx.fillText("Açıklamalı Türkçe Meali", w / 2, 175);

                ctx.strokeStyle = 'rgba(251, 113, 133, 0.3)';
                ctx.beginPath();
                ctx.moveTo(260, 205);
                ctx.lineTo(w - 260, 205);
                ctx.stroke();

                if (currentCustomCard.arabic) {
                    ctx.fillStyle = '#ffffff';
                    ctx.font = '44px "Amiri Quran", Amiri, serif';
                    ctx.direction = 'rtl';
                    wrapText(ctx, currentCustomCard.arabic, w / 2, 290, w - 180, 70);
                }

                ctx.fillStyle = '#ffe4e6';
                ctx.font = 'italic 32px Inter, sans-serif';
                ctx.direction = 'ltr';
                const mealY = currentCustomCard.arabic ? 600 : 440;
                wrapText(ctx, `"${currentCustomCard.meal}"`, w / 2, mealY, w - 200, 50);

                ctx.fillStyle = '#fecdd3';
                ctx.font = 'bold 28px Inter, sans-serif';
                ctx.fillText(`[ ${currentCustomCard.verseRef || "Kur'an-ı Kerim"} ]`, w / 2, 940);

                if (senderText) {
                    ctx.fillStyle = '#f43f5e';
                    ctx.font = '22px Inter, sans-serif';
                    ctx.fillText(senderText, w / 2, 990);
                }
            } else {
                // Turkuaz & Selçuklu
                const grad = ctx.createLinearGradient(0, 0, w, h);
                grad.addColorStop(0, '#083344');
                grad.addColorStop(0.5, '#041f2a');
                grad.addColorStop(1, '#02131b');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, w, h);

                ctx.strokeStyle = '#2dd4bf';
                ctx.lineWidth = 12;
                ctx.strokeRect(35, 35, w - 70, h - 70);

                ctx.strokeStyle = 'rgba(45, 212, 191, 0.3)';
                ctx.lineWidth = 3;
                ctx.strokeRect(55, 55, w - 110, h - 110);

                drawCornerMotifs(ctx, w, h, '#2dd4bf');

                ctx.fillStyle = '#2dd4bf';
                ctx.font = 'bold 36px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(currentCustomCard.title || "KUR'AN-I KERİM MEALİ", w / 2, 130);

                ctx.fillStyle = '#fde047';
                ctx.font = '24px Inter, sans-serif';
                ctx.fillText("Açıklamalı Türkçe Meali", w / 2, 175);

                ctx.strokeStyle = 'rgba(45, 212, 191, 0.3)';
                ctx.beginPath();
                ctx.moveTo(260, 205);
                ctx.lineTo(w - 260, 205);
                ctx.stroke();

                if (currentCustomCard.arabic) {
                    ctx.fillStyle = '#ffffff';
                    ctx.font = '44px "Amiri Quran", Amiri, serif';
                    ctx.direction = 'rtl';
                    wrapText(ctx, currentCustomCard.arabic, w / 2, 290, w - 180, 70);
                }

                ctx.fillStyle = '#ccfbf1';
                ctx.font = 'italic 32px Inter, sans-serif';
                ctx.direction = 'ltr';
                const mealY = currentCustomCard.arabic ? 600 : 440;
                wrapText(ctx, `"${currentCustomCard.meal}"`, w / 2, mealY, w - 200, 50);

                ctx.fillStyle = '#5eead4';
                ctx.font = 'bold 28px Inter, sans-serif';
                ctx.fillText(`[ ${currentCustomCard.verseRef || "Kur'an-ı Kerim"} ]`, w / 2, 940);

                if (senderText) {
                    ctx.fillStyle = '#99f6e4';
                    ctx.font = '22px Inter, sans-serif';
                    ctx.fillText(senderText, w / 2, 990);
                }
            }
        }

        function drawCornerMotifs(ctx, w, h, color) {
            ctx.strokeStyle = color;
            ctx.lineWidth = 2;
            const size = 30;
            // Sol Üst
            ctx.strokeRect(60, 60, size, size);
            // Sağ Üst
            ctx.strokeRect(w - 60 - size, 60, size, size);
            // Sol Alt
            ctx.strokeRect(60, h - 60 - size, size, size);
            // Sağ Alt
            ctx.strokeRect(w - 60 - size, h - 60 - size, size, size);
        }

        function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
            const words = text.split(' ');
            let line = '';
            let currentY = y;

            for (let n = 0; n < words.length; n++) {
                const testLine = line + words[n] + ' ';
                const metrics = ctx.measureText(testLine);
                const testWidth = metrics.width;
                if (testWidth > maxWidth && n > 0) {
                    ctx.fillText(line, x, currentY);
                    line = words[n] + ' ';
                    currentY += lineHeight;
                } else {
                    line = testLine;
                }
            }
            ctx.fillText(line, x, currentY);
        }

        function downloadGeneratedCard() {
            const canvas = document.getElementById('card-canvas');
            const link = document.createElement('a');
            link.download = `kuran-hadis-karti-${Date.now()}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
            showToast('Görsel kart başarıyla indirildi!');
        }

        // Arama Sonuçlarını Metin Olarak İndirme
        function exportResultsAsText() {
            if (!currentSearchData || !currentSearchData.results || currentSearchData.results.length === 0) {
                showToast('İndirilecek arama sonucu bulunamadı', 'error');
                return;
            }

            let textContent = `========================================================\n`;
            textContent += ` KUR'AN-I KERİM ARAMA SONUÇLARI\n`;
            textContent += ` Arama: "${currentSearchData.query}"\n`;
            textContent += ` Çeviri: Açıklamalı Türkçe Meali\n`;
            textContent += ` Toplam: ${currentSearchData.total} Ayet\n`;
            textContent += `========================================================\n\n`;

            currentSearchData.results.forEach((a, i) => {
                textContent += `[${i + 1}] ${a.surah_name_tr} Suresi ${a.ayah_number}. Ayet (Cüz: ${a.juz}, Sayfa: ${a.page})\n`;
                textContent += `Arapça: ${a.text_ar_uthmani}\n`;
                textContent += `Okunuş: ${a.text_tr_transliteration || '-'}\n`;
                textContent += `Türkçe Meali: ${a.text_tr_ozturk}\n`;
                textContent += `--------------------------------------------------------\n\n`;
            });

            const blob = new Blob([textContent], { type: 'text/plain;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `kuran-arama-${currentSearchData.query.replace(/[^a-zA-Z0-9]/g, '_')}.txt`;
            link.click();
            showToast('Arama sonuçları metin dosyası (.txt) olarak indirildi!');
        }

        // Font Boyutu Değiştirici
        function changeFontSize(delta) {
            currentArabicFontSize = Math.max(20, Math.min(48, currentArabicFontSize + delta));
            document.getElementById('font-size-label').innerText = `${currentArabicFontSize}px`;
            document.querySelectorAll('.arabic-text').forEach(el => {
                el.style.fontSize = `${currentArabicFontSize}px`;
            });
        }

        // Türkçe Okunuş Göster/Gizle
        function toggleTransliteration() {
            showTransliteration = !showTransliteration;
            document.querySelectorAll('.transliteration-container').forEach(el => {
                if (showTransliteration) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });
            showToast(showTransliteration ? 'Türkçe okunuş açıldı' : 'Türkçe okunuş gizlendi');
        }

        
        // =========================================================================
        // İSLAM TARİHİ FİHRİSTİ & KRONOLOJİSİ (DATA VE YÖNETİM FONKSİYONLARI)
        // =========================================================================
        const ISLAMIC_HISTORY_DATA = [
            {
                id: 1,
                period: "peygamberler",
                periodName: "Peygamberler Tarihi",
                dateStr: "Tarih Öncesi / İlk Yaratılış",
                title: "Hz. Âdem (a.s.) ve İnsanlığın Yaratılışı",
                summary: "Cenâb-ı Hakk'ın ilk insan ve ilk peygamber olarak Hz. Âdem'i topraktan yaratması, meleklerin secde etmesi, İblis'in kibri yüzünden isyanı ve Hz. Âdem ile Havva validemizin yeryüzüne halife kılınması.",
                verses: [
                    { surahId: 2, surahName: "Bakara", ayahNum: 30, text: "Hani Rabbin meleklere: 'Ben yeryüzünde bir halife yaratacağım' demişti..." },
                    { surahId: 7, surahName: "A'râf", ayahNum: 11, text: "Andolsun sizi yarattık, sonra size şekil verdik, sonra da meleklere: 'Âdem'e secde edin' dedik..." }
                ],
                lesson: "Kibrin felaket, tevazu ve samimi tevbenin ise kul için yegâne kurtuluş vesilesi olduğunu gösterir.",
                icon: "fa-seedling"
            },
            {
                id: 2,
                period: "peygamberler",
                periodName: "Peygamberler Tarihi",
                dateStr: "M.Ö. ~3000'ler",
                title: "Hz. Nûh (a.s.), Tevhid Mücadelesi ve Büyük Tufan",
                summary: "Hz. Nûh'un kavmini 950 yıl bıkmadan usanmadan bir olan Allah'a davet etmesi, putperest kavmin alay ve zulmü üzerine vahiy ile gemi inşa etmesi ve inkârcıların büyük tufanla helak olması.",
                verses: [
                    { surahId: 11, surahName: "Hûd", ayahNum: 37, text: "Gözlerimizin önünde ve vahyimiz doğrultusunda gemiyi yap..." },
                    { surahId: 71, surahName: "Nûh", ayahNum: 1, text: "Şüphesiz biz Nûh'u, kavmine bir uyarıcı olarak gönderdik..." }
                ],
                lesson: "Hak davada sabır ve sebatın, çokluğa değil Hakk'a tabi olmanın ehemmiyetini bildirir.",
                icon: "fa-ship"
            },
            {
                id: 3,
                period: "peygamberler",
                periodName: "Peygamberler Tarihi",
                dateStr: "M.Ö. ~2000'ler",
                title: "Hz. İbrâhim (a.s.) - Tevhid Önderi ve Kâbe'nin İnşası",
                summary: "Nemrut'un ateşine atılan ancak 'Ey ateş! İbrahim'e karşı serin ve esenlik ol' nidasıyla kurtulan Halîlullah Hz. İbrahim'in oğlu Hz. İsmail ile birlikte yeryüzünün ilk mabedi olan Kâbe-i Muazzama'yı inşa etmesi.",
                verses: [
                    { surahId: 2, surahName: "Bakara", ayahNum: 127, text: "Hani İbrahim, İsmail ile birlikte Beyt'in (Kâbe'nin) temellerini yükseltiyordu..." },
                    { surahId: 21, surahName: "Enbiyâ", ayahNum: 69, text: "Biz de dedik ki: 'Ey ateş! İbrahim'e karşı serin ve esenlik ol!'" }
                ],
                lesson: "Kayıtsız şartsız Allah'a teslimiyetin (ihlas ve tevekkül) her türlü ateşi gülistana çevireceğini öğretir.",
                icon: "fa-kaaba"
            },
            {
                id: 4,
                period: "peygamberler",
                periodName: "Peygamberler Tarihi",
                dateStr: "M.Ö. ~1700'ler",
                title: "Hz. Yûsuf (a.s.) - Kuyu, İffet, Zindan ve Mısır Azîzliği",
                summary: "Kur'an-ı Kerim'de 'Kıssaların En Güzeli' (Ahsenü'l-Kasas) olarak anılan; kardeşleri tarafından kuyuya atılan, köle olarak satılan, iftiraya uğrayıp zindana giren ancak iffet ve sabrıyla Mısır'a sultan olan Hz. Yûsuf'un destansı hayatı.",
                verses: [
                    { surahId: 12, surahName: "Yûsuf", ayahNum: 3, text: "Sana bu Kur'an'ı vahyederek kıssaların en güzelini anlatıyoruz..." },
                    { surahId: 12, surahName: "Yûsuf", ayahNum: 90, text: "Kim Allah'tan korkar ve sabrederse, bilsin ki Allah güzel davrananların mükâfatını zayi etmez." }
                ],
                lesson: "Zorluklar karşısında sabır, günahtan kaçınmada iffet ve intikam yerine affetmenin yüceliğini gösterir.",
                icon: "fa-gem"
            },
            {
                id: 5,
                period: "peygamberler",
                periodName: "Peygamberler Tarihi",
                dateStr: "M.Ö. ~1300'ler",
                title: "Hz. Mûsâ (a.s.), Firavun'a Karşı Kıyam ve Kızıldeniz'in Yarılması",
                summary: "Tûr Dağı'nda Kelîmullah olarak vahye muhatap olan Hz. Mûsâ'nın zalim Firavun'a tebliği, asâ mucizesi, İsrailoğullarını zulümden kurtarışı ve Kızıldeniz'in yarılarak Firavun'un ordusuyla boğulması.",
                verses: [
                    { surahId: 20, surahName: "Tâhâ", ayahNum: 24, text: "Firavun'a git, çünkü o azdı." },
                    { surahId: 26, surahName: "Şuarâ", ayahNum: 63, text: "Biz de Mûsâ'ya: 'Asân ile denize vur' diye vahyettik. Deniz yarıldı..." }
                ],
                lesson: "Zulüm ne kadar güçlü görünürse görünsün, ilahi adaletin tecelli edip zalimi helak edeceğini kanıtlar.",
                icon: "fa-water"
            },
            {
                id: 6,
                period: "peygamberler",
                periodName: "Peygamberler Tarihi",
                dateStr: "M.Ö. ~1000'ler",
                title: "Hz. Dâvûd ve Hz. Süleyman (a.s.) - Adalet ve İlim Devleti",
                summary: "Zebur'un indirildiği, dağların ve kuşların kendisiyle tesbihe durduğu Hz. Dâvûd ile rüzgârların, kuşların ve cinlerin emrine verildiği eşsiz hükümdar-peygamber Hz. Süleyman'ın adalet nizamı.",
                verses: [
                    { surahId: 38, surahName: "Sâd", ayahNum: 26, text: "Ey Dâvûd! Biz seni yeryüzünde halife kıldık; insanlar arasında adaletle hükmet..." },
                    { surahId: 27, surahName: "Neml", ayahNum: 15, text: "Andolsun ki biz Dâvûd'a ve Süleyman'a ilim verdik..." }
                ],
                lesson: "Maddi güç, iktidar ve servetin ancak Allah'a şükür ve kullara adalet vesilesi kılındığında kıymet bulduğunu anlatır.",
                icon: "fa-crown"
            },
            {
                id: 7,
                period: "peygamberler",
                periodName: "Peygamberler Tarihi",
                dateStr: "M.Ö. 1. Yüzyıl - M.S. 30",
                title: "Hz. Meryem ve Hz. Îsâ (a.s.) - Mucizevi Doğum ve Rûhullah",
                summary: "İffet timsali Hz. Meryem'in babasız olarak Hz. Îsâ'yı dünyaya getirmesi, Hz. Îsâ'nın beşikteyken konuşması, ölüleri diriltme ve hastaları iyileştirme mucizeleri ve İncil'in vahyedilmesi.",
                verses: [
                    { surahId: 19, surahName: "Meryem", ayahNum: 30, text: "Bebek dedi ki: 'Şüphesiz ben Allah'ın kuluyum. O bana kitabı verdi ve beni peygamber kıldı.'" },
                    { surahId: 3, surahName: "Âl-i İmrân", ayahNum: 45, text: "Hani melekler: 'Ey Meryem! Allah seni kendisinden bir kelime ile müjdeliyor, adı Meryem oğlu Îsâ Mesih'tir' demişti." }
                ],
                lesson: "Allah'ın kudretinin hiçbir sebebe bağlı olmadığını ('Ol' der ve olur) ve iffetin en büyük fazilet olduğunu vurgular.",
                icon: "fa-dove"
            },
            {
                id: 8,
                period: "mekke",
                periodName: "Mekke Dönemi",
                dateStr: "M.S. 571 (Fil Yılı)",
                title: "Fil Vakası ve Âlemlere Rahmet Efendimiz'in (s.a.v.) Doğumu",
                summary: "Yemen Valisi Ebrehe'nin filleriyle Kâbe'yi yıkmaya gelmesi üzerine Ebabil kuşlarıyla helak edilişi ve aynı yılın 12 Rebîülevvel gecesinde Sevgili Peygamberimiz Hz. Muhammed Mustafa'nın (s.a.v.) dünyayı teşrifi.",
                verses: [
                    { surahId: 105, surahName: "Fîl", ayahNum: 1, text: "Rabbinin fil sahiplerine ne yaptığını görmedin mi?" },
                    { surahId: 21, surahName: "Enbiyâ", ayahNum: 107, text: "Biz seni ancak âlemlere rahmet olarak gönderdik." }
                ],
                lesson: "Kâbe'nin bizzat Allah'ın hıfzında olduğunu ve Efendimiz'in (s.a.v.) tüm insanlığa rahmet müjdesiyle geldiğini müjdeler.",
                icon: "fa-star-and-crescent"
            },
            {
                id: 9,
                period: "mekke",
                periodName: "Mekke Dönemi",
                dateStr: "M.S. 610 (Ramazan / Kadir Gecesi)",
                title: "İlk Vahiy: Hira Mağarası ve 'İkra!' Emri",
                summary: "40 yaşındaki Peygamberimiz'e (s.a.v.) Hira Mağarası'nda tefekkür halindeyken Cebrail (a.s.) vasıtasıyla ilk vahyin (Alak Suresi 1-5) indirilmesi ve 23 yıllık nübüvvet çağının başlaması.",
                verses: [
                    { surahId: 96, surahName: "Alak", ayahNum: 1, text: "Yaratan Rabbinin adıyla oku!" },
                    { surahId: 97, surahName: "Kadr", ayahNum: 1, text: "Şüphesiz biz onu (Kur'an'ı) Kadir Gecesi'nde indirdik." }
                ],
                lesson: "İslam dininin ilk emrinin ilim, tefekkür, okuma ve Rabbin adıyla öğrenmek olduğunu ilan eder.",
                icon: "fa-mountain"
            },
            {
                id: 10,
                period: "mekke",
                periodName: "Mekke Dönemi",
                dateStr: "M.S. 613 (Bi'setin 3. Yılı)",
                title: "Açık Tebliğin Başlaması ve Safâ Tepesi Hitabı",
                summary: "Gizli davet döneminin ardından 'En yakın akrabalarını uyar' ve 'Sana emrolunanı açıkça bildir' ayetleriyle Efendimiz'in Safâ Tepesi'nde Kureyş'i açıkça Tevhid'e çağırması ve müşriklerin şiddetli muhalefetinin başlaması.",
                verses: [
                    { surahId: 15, surahName: "Hicr", ayahNum: 94, text: "Artık sen emrolunduğun şeyi açıkça ortaya koy ve müşriklerden yüz çevir." },
                    { surahId: 26, surahName: "Şuarâ", ayahNum: 214, text: "Ve en yakın hısımlarını uyar." }
                ],
                lesson: "Hakkı tebliğ ederken kınayanların kınamasından korkmamak ve ilk olarak en yakınlardan başlamak gerektiğini öğretir.",
                icon: "fa-bullhorn"
            },
            {
                id: 11,
                period: "mekke",
                periodName: "Mekke Dönemi",
                dateStr: "M.S. 615 (Bi'setin 5. Yılı)",
                title: "Habeşistan'a Hicretler ve Necâşî'nin Huzuru",
                summary: "Mekke'de dayanılmaz işkencelere maruz kalan ilk Müslümanların (Hz. Osman ve Cafer b. Ebî Tâlib öncülüğünde) adil hükümdar Necâşî Ashame'nin ülkesi Habeşistan'a sığınması ve Meryem Suresi tilaveti.",
                verses: [
                    { surahId: 16, surahName: "Nahl", ayahNum: 41, text: "Zulme uğradıktan sonra Allah yolunda hicret edenleri, dünyada güzel bir yere yerleştireceğiz..." }
                ],
                lesson: "Dini ve inancı muhafaza etmek için fedakârlıkta bulunmanın ve adalet arayışının faziletini gösterir.",
                icon: "fa-route"
            },
            {
                id: 12,
                period: "mekke",
                periodName: "Mekke Dönemi",
                dateStr: "M.S. 616 - 619 (Bi'setin 7-10. Yılları)",
                title: "Şi'b-i Ebî Tâlib Boykotu ve Büyük Sabır İmtihanı",
                summary: "Kureyş müşriklerinin Haşimoğullarına karşı kız alıp vermeme, ticaret yapmama ve görüşmeme maddelerini içeren zalimane bir boykot anlaşmasını Kâbe duvarına asması ve 3 yıllık açlık/sabır dönemi.",
                verses: [
                    { surahId: 2, surahName: "Bakara", ayahNum: 155, text: "Andolsun ki sizi biraz korku ve açlıkla, mallardan, canlardan ve ürünlerden eksiltmekle sınayacağız. Sabredenleri müjdele!" }
                ],
                lesson: "En çetin dünyevi ambargoların dahi samimi imanı ve kardeşlik bağını sarsamayacağını gösterir.",
                icon: "fa-hand-fist"
            },
            {
                id: 13,
                period: "mekke",
                periodName: "Mekke Dönemi",
                dateStr: "M.S. 620 (Bi'setin 10. Yılı)",
                title: "Hüzün Yılı (Âmü'l-Hüzn) ve Tâif Seferi",
                summary: "Efendimiz'in en büyük iki desteği olan muhterem eşi Hz. Hatice ve amcası Ebû Tâlib'in vefatı. Akabinde İslam'ı tebliğ için gittiği Tâif'te taşlanması ve buna rağmen oradakilerin hidayeti için dua etmesi.",
                verses: [
                    { surahId: 93, surahName: "Duhâ", ayahNum: 6, text: "O seni bir yetim bulup barındırmadı mı? Seni yolunu kaybetmiş bulup doğru yola iletmedi mi?" }
                ],
                lesson: "Büyük davaların en ağır hüzünlerle yoğrulduğunu ve hakiki peygamber ahlakının intikam değil af ve dua olduğunu anlatır.",
                icon: "fa-heart-crack"
            },
            {
                id: 14,
                period: "mekke",
                periodName: "Mekke Dönemi",
                dateStr: "M.S. 621 (27 Recep)",
                title: "İsrâ ve Mi'râc Mucizesi: Beş Vakit Namazın Hediyesi",
                summary: "Mescid-i Haram'dan Mescid-i Aksâ'ya gece yürüyüşü (İsrâ) ve oradan semâvatın en yüce makamı Sidretü'l-Müntehâ'ya yükseliş (Mi'râc). Müminlere beş vakit namaz, Bakara son ayetleri ve şirk koşmayanların affı müjdelendi.",
                verses: [
                    { surahId: 17, surahName: "İsrâ", ayahNum: 1, text: "Kendisine ayetlerimizden gösterelim diye kulunu bir gece Mescid-i Haram'dan Mescid-i Aksâ'ya götüren Allah her türlü eksiklikten münezzehtir." },
                    { surahId: 53, surahName: "Necm", ayahNum: 13, text: "Andolsun onu bir diğer inişinde de görmüştü; Sidretü'l-Müntehâ'nın yanında." }
                ],
                lesson: "Dünyevi hüzün ve sıkıntıların ardından Allah'ın kuluna yüce ferahlıklar ve namaz gibi bir miraç bahşettiğini müjdeler.",
                icon: "fa-moon"
            },
            {
                id: 15,
                period: "mekke",
                periodName: "Mekke Dönemi",
                dateStr: "M.S. 621 - 622 (Bi'setin 11-12. Yılları)",
                title: "Akabe Biatları ve Medine'ye İslam'ın Girişi",
                summary: "Medineli Evs ve Hazreç kabilelerinden heyetlerin Mekke yakınlarındaki Akabe mevkiinde Efendimiz'e biat etmesi, Hz. Mus'ab b. Umeyr'in ilk öğretmen olarak Medine'ye gönderilmesi ve şehrin İslam'a açılması.",
                verses: [
                    { surahId: 60, surahName: "Mümtehine", ayahNum: 12, text: "Ey Peygamber! İnanmış kadınlar sana biat etmeye geldiklerinde..." }
                ],
                lesson: "Bir ferdin samimi tebliğinin (Mus'ab b. Umeyr) koskoca bir şehrin kaderini değiştirebileceğini ispatlar.",
                icon: "fa-handshake"
            },
            {
                id: 16,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 622 (Rebîülevvel / 1 H.)",
                title: "Büyük Hicret: Sevr Mağarası ve Medine'ye Teşrif",
                summary: "Müşriklerin suikast planını Hz. Ali'yi yatağına yatırarak boşa çıkaran Efendimiz'in, Hz. Ebû Bekir ile birlikte Sevr Mağarası'ndan geçerek Kuba'ya ve ardından Medine-i Münevvere'ye tarihi hicreti.",
                verses: [
                    { surahId: 9, surahName: "Tevbe", ayahNum: 40, text: "Hani o iki kişiden biri iken, ikisi mağaradaydılar; hani o arkadaşına: 'Üzülme, şüphesiz Allah bizimle beraberdir' diyordu." }
                ],
                lesson: "'Lâ tahzen, innallâhe meânâ' (Üzülme, Allah bizimledir) bilincinin her türlü tuzağı yerle bir edeceğini gösterir.",
                icon: "fa-compass"
            },
            {
                id: 17,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 622 (1 H.)",
                title: "Mescid-i Nebevî'nin İnşası ve Muâhât (Kardeşlik Bağı)",
                summary: "Medine'de ilk iş olarak Mescid-i Nebevî ve Ashâb-ı Suffe mektebinin inşa edilmesi. Mekkeli Muhacirler ile Medineli Ensar arasında tarihte eşi benzeri olmayan kardeşlik (Muâhât) akdinin tesisi.",
                verses: [
                    { surahId: 59, surahName: "Haşr", ayahNum: 9, text: "Kendileri ihtiyaç içinde bulunsalar bile onları kendilerine tercih ederler. Kim nefsinin cimriliğinden korunursa, işte onlar kurtuluşa erenlerdir." }
                ],
                lesson: "İslam toplumunun temel harcının sevgi, fedakârlık ve karşılıksız paylaşım (îsâr) olduğunu gösterir.",
                icon: "fa-mosque"
            },
            {
                id: 18,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 624 (Şaban 2 H.)",
                title: "Kıblenin Kâbe'ye Çevrilmesi ve Ramazan Orucunun Farz Kılınması",
                summary: "16-17 ay boyunca Kudüs'teki Mescid-i Aksâ'ya doğru kılınan namazların kıblesinin nazil olan ayetle Kâbe-i Muazzama'ya çevrilmesi (Mescid-i Kıbleteyn) ve Ramazan ayı orucunun farz kılınması.",
                verses: [
                    { surahId: 2, surahName: "Bakara", ayahNum: 144, text: "Yüzünü Mescid-i Haram tarafına çevir. Nerede olursanız yüzlerinizi o yöne çevirin." },
                    { surahId: 2, surahName: "Bakara", ayahNum: 183, text: "Ey iman edenler! Oruç, sizden öncekilere farz kılındığı gibi size de farz kılındı..." }
                ],
                lesson: "İslam ümmetinin istiklalini, birlik kıblesini ve nefis terbiyesinde orucun arındırıcı gücünü temsil eder.",
                icon: "fa-arrows-spin"
            },
            {
                id: 19,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 624 (17 Ramazan 2 H.)",
                title: "Bedir Gazvesi: Hak ile Bâtılın Ayrıldığı Gün (Yevmü'l-Furkan)",
                summary: "313 kişilik mütevazı İslam ordusunun, meleklerin yardımıyla 1000 kişilik tam teçhizatlı müşrik ordusunu hezimete uğrattığı, Ebû Cehil gibi küfür önderlerinin öldürüldüğü ilk büyük zafer.",
                verses: [
                    { surahId: 8, surahName: "Enfâl", ayahNum: 9, text: "Hani siz Rabbinizden yardım istiyordunuz da O: 'Ben peş peşe gelen bin melekle size yardım edeceğim' diyerek duanızı kabul etmişti." },
                    { surahId: 3, surahName: "Âl-i İmrân", ayahNum: 123, text: "Andolsun, sizler güçsüz iken Allah size Bedir'de yardım etmişti." }
                ],
                lesson: "Zaferin sayı ve teçhizatta değil, samimi iman, dua ve Allah'ın yardımında olduğunu ortaya koyar.",
                icon: "fa-shield-halved"
            },
            {
                id: 20,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 625 (Şevval 3 H.)",
                title: "Uhud Gazvesi: Okçular Tepesi ve İtaat Dersi",
                summary: "Bedir'in intikamını almak isteyen 3000 kişilik müşrik ordusuna karşı Uhud Dağı eteklerinde yapılan savaş. Okçular Tepesi'ndeki emre itaatsizlik sonucu Hz. Hamza (r.a.) dahil 70 şehit verilen ibret dolu hadise.",
                verses: [
                    { surahId: 3, surahName: "Âl-i İmrân", ayahNum: 121, text: "Hani sen müminleri savaş mevzilerine yerleştirmek için erkenden ailenden ayrılmıştın..." },
                    { surahId: 3, surahName: "Âl-i İmrân", ayahNum: 152, text: "Andolsun, Allah size olan vaadini yerine getirmişti..." }
                ],
                lesson: "Lidere ve nizama itaatsizliğin, dünyalık ganimet arzusunun en büyük zaferleri dahi tehlikeye düşüreceğini öğretir.",
                icon: "fa-mountain-sun"
            },
            {
                id: 21,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 627 (Şevval 5 H.)",
                title: "Hendek (Ahzâb) Gazvesi: Kuşatma ve İlahi Fırtına",
                summary: "Müşrikler, Yahudiler ve Bedevi kabilelerden oluşan 10.000 kişilik müttefik ordusunun Medine'yi kuşatması. Selmân-ı Fârisî'nin teklifiyle hendek kazılması ve dondurucu bir fırtınayla düşmanın püskürtülmesi.",
                verses: [
                    { surahId: 33, surahName: "Ahzâb", ayahNum: 9, text: "Ey iman edenler! Allah'ın size olan nimetini hatırlayın; hani üzerinize ordular gelmişti de biz onların üzerine bir rüzgâr göndermiştik..." }
                ],
                lesson: "Akıl, istişare ve tedbirin (hendek) ardından gelen tevekkülün en çaresiz anlarda dahi ilahi zafer getireceğini gösterir.",
                icon: "fa-wind"
            },
            {
                id: 22,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 628 (Zilkade 6 H.)",
                title: "Hudeybiye Barışı ve Rıdvan Biatı: Feth-i Mübîn",
                summary: "Umre için yola çıkan Müslümanların Hudeybiye'de durdurulması, Semure ağacı altında canları pahasına Rıdvan Biatı yapılması ve görünüşte aleyhte sanılan ancak İslam'ın hızla yayılmasını sağlayan 10 yıllık barış antlaşması.",
                verses: [
                    { surahId: 48, surahName: "Fetih", ayahNum: 1, text: "Şüphesiz biz sana apaçık bir fetih (Feth-i Mübîn) verdik." },
                    { surahId: 48, surahName: "Fetih", ayahNum: 18, text: "Andolsun ki o ağacın altında sana biat ederlerken Allah müminlerden razı olmuştur..." }
                ],
                lesson: "Bazen geri adım gibi görünen sulh ve diplomasinin, savaştan çok daha büyük fetihlere kapı aralayacağını bildirir.",
                icon: "fa-file-contract"
            },
            {
                id: 23,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 628 (Muharrem 7 H.)",
                title: "Hayber'in Fethi ve Hükümdarlara Davet Mektupları",
                summary: "İhanet ve fitne merkezi haline gelen muhkem Hayber kalelerinin Hz. Ali'nin kahramanlığıyla fethedilmesi. Ardından Bizans, Sasani, Mısır ve Habeşistan hükümdarlarına İslam'a davet mektupları gönderilmesi.",
                verses: [
                    { surahId: 48, surahName: "Fetih", ayahNum: 20, text: "Allah size alacağınız birçok ganimetler vaad etti; bunu size hemen verdi..." }
                ],
                lesson: "İslam davetinin evrensel olduğunu ve hiçbir engelin hakkın çağrısını durduramayacağını ilan eder.",
                icon: "fa-envelope-open-text"
            },
            {
                id: 24,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 630 (20 Ramazan 8 H.)",
                title: "Mekke'nin Fethi: 'Hak Geldi, Bâtıl Zâil Oldu'",
                summary: "Kureyş'in antlaşmayı bozması üzerine 10.000 kişilik muhteşem ordusuyla Mekke'ye kansız giren Efendimiz'in Kâbe'deki 360 putu devirmesi, başı tevazu ile devesinin semerine değecek kadar eğik girmesi ve Mekkelilere genel af ilan etmesi.",
                verses: [
                    { surahId: 17, surahName: "İsrâ", ayahNum: 81, text: "De ki: 'Hak geldi, bâtıl zâil oldu. Şüphesiz bâtıl yok olmaya mahkûmdur.'" },
                    { surahId: 110, surahName: "Nasr", ayahNum: 1, text: "Allah'ın yardımı ve fetih geldiği zaman..." }
                ],
                lesson: "En büyük zafer gününde dahi kibre kapılmayıp sonsuz tevazu göstermeyi ve affediciliğin büyüklüğünü öğretir.",
                icon: "fa-flag"
            },
            {
                id: 25,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 630 (Recep 9 H.)",
                title: "Tebük Seferi (Gazvetü'l-Usre): Sadakat İmtihanı",
                summary: "Kavurucu yaz sıcağında, Bizans ordusuna karşı Medine'den Suriye sınırına yapılan en uzun ve en zorlu sefer. Hz. Ebû Bekir'in tüm malını, Hz. Osman'ın ordunun üçte birini donattığı büyük infak destanı.",
                verses: [
                    { surahId: 9, surahName: "Tevbe", ayahNum: 117, text: "Andolsun ki Allah Peygamber'i ve o güçlük saatinde (saatü'l-usre) ona uyan Muhacirlerle Ensar'ı affetti..." }
                ],
                lesson: "Darlık ve zorluk anlarında infak ve sadakatin mümin ile münafığı birbirinden ayıran kesin ölçü olduğunu gösterir.",
                icon: "fa-sun"
            },
            {
                id: 26,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 632 (Zilhicce 10 H.)",
                title: "Vedâ Haccı, Vedâ Hutbesi ve Dinin Kemâle Ermesi",
                summary: "100.000'i aşkın sahabiyle Arafat meydanında okunan evrensel İnsan Hakları Beyannamesi niteliğindeki Vedâ Hutbesi: Can, mal ve namus dokunulmazlığı, ırk üstünlüğünün reddi ('Arabın Aceme üstünlüğü yoktur'), kadın hakları ve faizin yasaklanması.",
                verses: [
                    { surahId: 5, surahName: "Mâide", ayahNum: 3, text: "Bugün sizin dininizi kemâle erdirdim, üzerinizdeki nimetimi tamamladım ve size din olarak İslam'ı seçip razı oldum." }
                ],
                lesson: "Tüm insanların eşitliğini, adaleti, emanet şuurunu ve Kur'an ile Sünnete sarılmanın kıyamete kadar tek kurtuluş olduğunu bildirir.",
                icon: "fa-users"
            },
            {
                id: 27,
                period: "medine",
                periodName: "Medine Dönemi",
                dateStr: "M.S. 632 (12 Rebîülevvel 11 H.)",
                title: "Fahr-i Kâinat Efendimiz'in (s.a.v.) Refîk-i A'lâ'ya İrtihâli",
                summary: "63 yıllık kutlu ömrünü tamamlayan İki Cihan Güneşi Efendimiz'in Medine'de Mescid-i Nebevî bitişiğindeki Hücre-i Saâdet'te Yüce Dost'a (er-Refîku'l-A'lâ) kavuşması. Hz. Ebû Bekir'in 'Muhammed'e tapan bilsin ki o vefat etti; Allah'a tapan bilsin ki O diridir' hitabı.",
                verses: [
                    { surahId: 3, surahName: "Âl-i İmrân", ayahNum: 144, text: "Muhammed ancak bir peygamberdir. Ondan önce de peygamberler gelip geçmiştir. Şimdi o ölür veya öldürülürse gerisin geriye mi döneceksiniz?" }
                ],
                lesson: "Fani şahısların gelip geçici, baki olanın ise yalnızca Âlemlerin Rabbi Allah ve O'nun dini olduğunu idrak ettirir.",
                icon: "fa-heart"
            },
            {
                id: 28,
                period: "halifeler",
                periodName: "Dört Halife Dönemi",
                dateStr: "M.S. 632 - 634 (11 - 13 H.)",
                title: "Hz. Ebû Bekir (r.a.) Dönemi ve Kur'an'ın Mushaf Haline Getirilmesi",
                summary: "İlk halife Hz. Sıddîk'ın irtidad hareketlerini bastırması, Yemâme Savaşı'nda hafız sahabelerin şehit düşmesi üzerine Hz. Ömer'in teklifiyle Zeyd b. Sâbit başkanlığındaki heyet tarafından Kur'an ayetlerinin iki kapak arasında ilk kez Mushaf (İmam Mushaf) haline toplanması.",
                verses: [
                    { surahId: 15, surahName: "Hicr", ayahNum: 9, text: "Şüphesiz o Zikr'i (Kur'an'ı) biz indirdik ve onu mutlaka biz koruyacağız." }
                ],
                lesson: "Kur'an-ı Kerim'in tek bir harfi dahi zayi olmadan en titiz usullerle (iki şahit ve yazılı belge) muhafaza altına alındığını gösterir.",
                icon: "fa-book-bookmark"
            },
            {
                id: 29,
                period: "halifeler",
                periodName: "Dört Halife Dönemi",
                dateStr: "M.S. 634 - 644 (13 - 23 H.)",
                title: "Hz. Ömer (r.a.) Dönemi: Adalet Nizamı, Fütûhât ve Hicri Takvim",
                summary: "Fâruk-ı A'zam Hz. Ömer devrinde Kudüs, Suriye, Filistin, Irak, İran ve Mısır'ın fethi. Beytülmal, kadılık ve divan teşkilatlarının kurulması; Hz. Ali'nin teklifiyle Hicret'in başlangıç kabul edildiği Hicri Takvim'in tanzimi.",
                verses: [
                    { surahId: 4, surahName: "Nisâ", ayahNum: 58, text: "Allah size emanetleri ehline vermenizi ve insanlar arasında hükmettiğiniz zaman adaletle hükmetmenizi emreder." }
                ],
                lesson: "Adaletin mülkün temeli olduğunu, hakiki yöneticiliğin halkın hizmetkârı olmak anlamına geldiğini öğretir.",
                icon: "fa-scale-balanced"
            },
            {
                id: 30,
                period: "halifeler",
                periodName: "Dört Halife Dönemi",
                dateStr: "M.S. 644 - 656 (23 - 35 H.)",
                title: "Hz. Osman (r.a.) Dönemi ve Kur'an Mushaflarının Çoğaltılması",
                summary: "Zinnûreyn Hz. Osman döneminde İslam coğrafyasının Kafkaslar'dan Kuzey Afrika'ya genişlemesi; farklı lehçe okuyuş ihtilaflarını önlemek amacıyla Hz. Ebû Bekir nüshasının Kureyş lehçesi esas alınarak 7 nüsha halinde çoğaltılıp ana merkezlere (Mekke, Kufe, Basra, Şam, Yemen, Bahreyn) gönderilmesi.",
                verses: [
                    { surahId: 15, surahName: "Hicr", ayahNum: 9, text: "Şüphesiz o Zikr'i biz indirdik ve onun koruyucusu da elbette biziz." }
                ],
                lesson: "Kur'an-ı Kerim'in kıyamete kadar ümmetin vahdet bağı ve tek ortak metni olarak korunmasını temin etmiştir.",
                icon: "fa-copy"
            },
            {
                id: 31,
                period: "halifeler",
                periodName: "Dört Halife Dönemi",
                dateStr: "M.S. 656 - 661 (35 - 40 H.)",
                title: "Hz. Ali (r.a.) Dönemi: İlim Kapısı ve Hakkaniyet Mücadelesi",
                summary: "Efendimiz'in 'Ben ilmin şehriyim, Ali onun kapısıdır' buyurduğu Haydar-ı Kerrâr Hz. Ali'nin çetin fitne ve imtihanlar karşısında tavizsiz adalet, takva ve hikmet rehberliği.",
                verses: [
                    { surahId: 9, surahName: "Tevbe", ayahNum: 119, text: "Ey iman edenler! Allah'tan korkun ve doğrularla beraber olun." }
                ],
                lesson: "Zor zamanlarda Hakk'ın hatırını her şeyin üstünde tutmanın ve ilimle amel etmenin önemini gösterir.",
                icon: "fa-book-open-reader"
            },
            {
                id: 32,
                period: "mushaf",
                periodName: "Kur'an & Mushaf Tarihi",
                dateStr: "M.S. 610 - 632",
                title: "Vahiy Kâtipleri ve İlk Yazım Materyalleri",
                summary: "Nazil olan ayetlerin Efendimiz'in emriyle vahiy kâtipleri (Zeyd b. Sâbit, Hz. Ali, Ubey b. Ka'b, Hz. Osman vb.) tarafından hurma yaprakları, yassı kemikler, beyaz taşlar ve deri parçaları üzerine günü gününe kaydedilip hafızlarca ezberlenmesi.",
                verses: [
                    { surahId: 68, surahName: "Kalem", ayahNum: 1, text: "Nûn. Kaleme ve satır satır yazdıklarına andolsun!" },
                    { surahId: 80, surahName: "Abese", ayahNum: 15, text: "Kâtiplerin ellerinde bulunan, çok şerefli, tertemiz sahifelerdedir." }
                ],
                lesson: "Kur'an'ın hem ezber (hıfz) hem de yazı (kitabet) yoluyla çift emniyetle korunduğunu gösterir.",
                icon: "fa-pen-nib"
            },
            {
                id: 33,
                period: "mushaf",
                periodName: "Kur'an & Mushaf Tarihi",
                dateStr: "M.S. 680 - 780 (Emevîler / Abbâsîler)",
                title: "Arapça Noktalama ve Harekeleme Sisteminin Geliştirilmesi",
                summary: "Arap olmayan milletlerin İslam'a girmesiyle Kur'an'ın doğru okunmasını temin etmek için Ebü'l-Esved ed-Düelî, Nasr b. Âsım ve Halil b. Ahmed el-Ferâhîdî tarafından harekelerin (üstün, esre, ötre) ve harf noktalarının sisteme bağlanması.",
                verses: [
                    { surahId: 54, surahName: "Kamer", ayahNum: 17, text: "Andolsun biz Kur'an'ı düşünüp öğüt almak için kolaylaştırdık. Yok mu düşünüp öğüt alan?" }
                ],
                lesson: "İlahi kelamın tüm diller ve milletler tarafından hatasız okunması için sergilenen muazzam ilmi hassasiyeti temsil eder.",
                icon: "fa-spell-check"
            },
            {
                id: 34,
                period: "mushaf",
                periodName: "Kur'an & Mushaf Tarihi",
                dateStr: "Tarihten Günümüze",
                title: "Hattatların Göz Nuru: Hat Sanatı ve Matbu Mushaflar",
                summary: "Şeyh Hamdullah, Ahmed Karahisârî ve Hafız Osman gibi Osmanlı hattatlarının yazdığı eşsiz mushaf nüshaları; matbaanın icadıyla tüm dünyada milyonlarca basılıp okunan bugünkü kusursuz Medine ve Diyanet mushafları.",
                verses: [
                    { surahId: 85, surahName: "Bürûc", ayahNum: 21, text: "Hayır! O, şerefli bir Kur'an'dır; Levh-i Mahfuz'dadır." }
                ],
                lesson: "Kur'an-ı Kerim'in lafzı, manası ve hattıyla kıyamete kadar dimdik ayakta kalan ebedi mucize olduğunu haykırır.",
                icon: "fa-book-quran"
            }
        ];

        let currentHistoryPeriod = 'all';
        let currentHistorySearchQuery = '';

        function initHistoryView() {
            renderHistoryCards(getFilteredHistoryData());
        }

        function getFilteredHistoryData() {
            return ISLAMIC_HISTORY_DATA.filter(item => {
                const matchesPeriod = currentHistoryPeriod === 'all' || item.period === currentHistoryPeriod;
                const q = currentHistorySearchQuery.toLowerCase();
                const matchesSearch = !q || 
                    item.title.toLowerCase().includes(q) ||
                    item.summary.toLowerCase().includes(q) ||
                    item.periodName.toLowerCase().includes(q) ||
                    item.dateStr.toLowerCase().includes(q) ||
                    item.lesson.toLowerCase().includes(q) ||
                    item.verses.some(v => v.surahName.toLowerCase().includes(q) || v.text.toLowerCase().includes(q));
                return matchesPeriod && matchesSearch;
            });
        }

        function renderHistoryCards(data) {
            const container = document.getElementById('history-list-container');
            const countText = document.getElementById('history-count-text');
            if (!container) return;

            countText.innerText = `${data.length} Tarihi Hadise`;

            if (data.length === 0) {
                container.innerHTML = `
                    <div class="py-16 text-center bg-gray-900/60 rounded-2xl border border-gray-800 space-y-3">
                        <i class="fa-solid fa-landmark-dome text-gray-600 text-4xl"></i>
                        <p class="text-gray-400 text-sm">Aradığınız kriterlere uygun tarihi hadise bulunamadı.</p>
                        <button onclick="clearHistorySearch()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold transition">Filtreleri Temizle</button>
                    </div>
                `;
                return;
            }

            let html = '';
            data.forEach((item, index) => {
                let versesHtml = '';
                item.verses.forEach(v => {
                    versesHtml += `
                        <div class="flex items-start justify-between gap-3 p-3 bg-gray-950/80 rounded-xl border border-gray-800/80">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-emerald-400">${escapeHtml(v.surahName)} Suresi, ${v.ayahNum}. Ayet</span>
                                </div>
                                <p class="text-xs text-gray-300 italic">"${escapeHtml(v.text)}"</p>
                            </div>
                            <button onclick="openSurah(${v.surahId})" class="px-2.5 py-1 bg-emerald-950 hover:bg-emerald-900 text-emerald-300 border border-emerald-800/80 rounded-lg text-[11px] font-semibold transition whitespace-nowrap flex items-center gap-1">
                                <span>Sureyi Aç</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    `;
                });

                html += `
                    <div class="history-card bg-gray-900/80 border border-gray-800 hover:border-gray-700 rounded-2xl p-5 sm:p-6 transition-all duration-200 shadow-md space-y-4 relative">
                        <!-- Üst Bar: Tarih & Dönem Rozetleri -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-800/80 text-xs">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-1 rounded-lg bg-amber-950 text-amber-300 font-bold border border-amber-800/60 font-mono flex items-center gap-1.5">
                                    <i class="fa-solid fa-calendar-days text-[11px]"></i> ${escapeHtml(item.dateStr)}
                                </span>
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-950/70 text-emerald-400 font-semibold border border-emerald-800/40 text-[11px]">
                                    ${escapeHtml(item.periodName)}
                                </span>
                            </div>

                            <!-- Aksiyon Butonları (Sesli Dinle, WhatsApp Paylaş) -->
                            <div class="flex items-center gap-1.5 text-gray-400">
                                <button onclick="speakHistoryEvent(${item.id})" class="px-2.5 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-lg transition text-xs font-semibold flex items-center gap-1.5" title="Sesli Oku">
                                    <i class="fa-solid fa-volume-high text-amber-400 text-xs"></i> <span>Dinle</span>
                                </button>
                                <button onclick="shareHistoryOnWhatsApp(${item.id})" class="p-2 hover:bg-emerald-900/30 text-emerald-500 hover:text-emerald-400 rounded-lg transition" title="WhatsApp'ta Paylaş">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Başlık -->
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-950/50 border border-amber-800/40 text-amber-400 flex items-center justify-center text-base shrink-0 mt-0.5">
                                <i class="fa-solid ${item.icon}"></i>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white tracking-tight">${escapeHtml(item.title)}</h3>
                                <p class="text-xs sm:text-sm text-gray-300 leading-relaxed mt-1">${escapeHtml(item.summary)}</p>
                            </div>
                        </div>

                        <!-- İlgili Kur'an Ayetleri -->
                        <div class="space-y-2 pt-1">
                            <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-book-quran text-xs"></i> Kur'an-ı Kerim'deki İlgili Ayetler
                            </h4>
                            <div class="space-y-2">
                                ${versesHtml}
                            </div>
                        </div>

                        <!-- Manevi / Ahlaki Hikmet & Çıkarılacak Ders -->
                        <div class="p-3.5 bg-amber-950/20 border border-amber-800/30 rounded-xl text-xs text-amber-200/90 leading-relaxed flex items-start gap-2.5">
                            <i class="fa-solid fa-lightbulb text-amber-400 text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="text-amber-300 font-semibold">Tarihi & Manevi Hikmet:</strong> ${escapeHtml(item.lesson)}
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function filterHistoryByPeriod(period) {
            currentHistoryPeriod = period;
            document.querySelectorAll('.history-period-btn').forEach(btn => {
                if (btn.getAttribute('data-period') === period) {
                    btn.className = 'history-period-btn active px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white transition whitespace-nowrap';
                } else {
                    btn.className = 'history-period-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition whitespace-nowrap';
                }
            });
            renderHistoryCards(getFilteredHistoryData());
        }

        function onHistorySearchInput() {
            const input = document.getElementById('history-search-input');
            const clearBtn = document.getElementById('history-clear-btn');
            currentHistorySearchQuery = input.value.trim();

            if (currentHistorySearchQuery) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
            renderHistoryCards(getFilteredHistoryData());
        }

        function clearHistorySearch() {
            const input = document.getElementById('history-search-input');
            input.value = '';
            document.getElementById('history-clear-btn').classList.add('hidden');
            currentHistorySearchQuery = '';
            filterHistoryByPeriod('all');
        }

        function shareHistoryOnWhatsApp(id) {
            const item = ISLAMIC_HISTORY_DATA.find(h => h.id === id);
            if (!item) return;

            let msg = `[ İSLÂM TARİHİ KRONOLOJİSİ ]\n`;
            msg += `*${cleanForWhatsApp(item.title)}*\n`;
            msg += `Tarih: ${cleanForWhatsApp(item.dateStr)} (${cleanForWhatsApp(item.periodName)})\n\n`;
            msg += `Hadise:\n> "${cleanForWhatsApp(item.summary)}"\n\n`;
            
            if (item.verses && item.verses.length > 0) {
                msg += `İlgili Ayetler:\n`;
                item.verses.forEach(v => {
                    msg += `• ${v.surahName} ${v.ayahNum}: "${cleanForWhatsApp(v.text)}"\n`;
                });
                msg += `\n`;
            }

            msg += `Manevi Hikmet:\n"${cleanForWhatsApp(item.lesson)}"`;

            const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;
            window.open(url, '_blank');
        }

        function speakHistoryEvent(id) {
            const item = ISLAMIC_HISTORY_DATA.find(h => h.id === id);
            if (!item) return;

            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const textToSpeak = `${item.title}. ${item.dateStr}. ${item.summary}. İlgili hikmet: ${item.lesson}`;
                const utterance = new SpeechSynthesisUtterance(textToSpeak);
                utterance.lang = 'tr-TR';
                utterance.rate = 0.95;
                utterance.pitch = 0.95;
                window.speechSynthesis.speak(utterance);
                showToast('Tarihi hadise seslendiriliyor...');
            } else {
                showToast('Tarayıcınız sesli okumayı desteklemiyor', 'error');
            }
        }

        // Ayeti Panoya Kopyalama (Tertemiz Format)
        function copyAyah(id) {
            const cardEl = document.getElementById(`ayah-card-${id}`);
            if (!cardEl) return;

            let surah = cardEl.getAttribute('data-surah') || '';
            let num = cardEl.getAttribute('data-num') || '';
            let rawArabic = cardEl.getAttribute('data-arabic') ? decodeURIComponent(cardEl.getAttribute('data-arabic')) : '';
            let rawMeal = cardEl.getAttribute('data-meal') ? decodeURIComponent(cardEl.getAttribute('data-meal')) : '';

            if (!rawArabic || !rawMeal) {
                const el = document.getElementById(`copy-data-${id}`);
                if (el) {
                    navigator.clipboard.writeText(el.innerText.trim()).then(() => {
                        showToast('Ayet ve Türkçe meali panoya kopyalandı!');
                    });
                    return;
                }
            }

            const arabic = cleanArabicForUniversal(rawArabic);
            const meal = cleanForWhatsApp(rawMeal);
            const ref = `${surah} Suresi, ${num}. Ayet`;

            let text = `[ ${ref} ]\n\n${arabic}\n\nTürkçe Meali:\n"${meal}"`;
            navigator.clipboard.writeText(text).then(() => {
                showToast('Ayet ve Türkçe meali panoya kopyalandı!');
            });
        }

        // Toast Bildirimi
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const msgEl = document.getElementById('toast-message');
            msgEl.innerText = message;

            if (type === 'error') {
                toast.className = 'fixed bottom-20 right-5 z-50 transform translate-y-0 opacity-100 transition-all duration-300 bg-red-600 text-white px-4 py-2.5 rounded-xl shadow-xl flex items-center gap-2 text-sm font-medium';
            } else {
                toast.className = 'fixed bottom-20 right-5 z-50 transform translate-y-0 opacity-100 transition-all duration-300 bg-emerald-600 text-white px-4 py-2.5 rounded-xl shadow-xl flex items-center gap-2 text-sm font-medium';
            }

            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
                toast.classList.remove('translate-y-0', 'opacity-100');
            }, 3000);
        }

        // HTML Kaçış Yardımcısı
        function escapeHtml(str) {
            if (!str) return '';
            const p = document.createElement('p');
            p.appendChild(document.createTextNode(str));
            return p.innerHTML;
        }
    </script>
</body>
</html>
