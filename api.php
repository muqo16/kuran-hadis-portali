<?php
/**
 * Kur'an-ı Kerim JSON API Endpoint
 * Çeviri: Prof. Dr. Yaşar Nuri Öztürk
 * Karşılaştırmalı Mealler: Elmalılı Hamdi Yazır, Süleyman Ateş, Diyanet İşleri
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? 'search';

try {
    switch ($action) {
        case 'search':
            $q = $_GET['q'] ?? '';
            $surah = isset($_GET['surah']) && is_numeric($_GET['surah']) ? (int)$_GET['surah'] : null;
            $revelation = $_GET['revelation'] ?? 'all';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(5, (int)($_GET['limit'] ?? 25)));

            $data = QuranDB::search($q, $surah, $revelation, $page, $limit);
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'surahs':
            $surahs = QuranDB::getSurahs();
            echo json_encode([
                'status' => 'success',
                'data' => $surahs
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'surah':
            $id = (int)($_GET['id'] ?? 1);
            $surah = QuranDB::getSurah($id);
            if (!$surah) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Sure bulunamadı']);
                break;
            }
            $offset = max(0, (int)($_GET['offset'] ?? 0));
            $limit = min(500, max(1, (int)($_GET['limit'] ?? 500)));
            $ayahs = QuranDB::getAyahsBySurah($id, $offset, $limit);

            echo json_encode([
                'status' => 'success',
                'surah' => $surah,
                'ayahs' => $ayahs
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'juz':
            $id = (int)($_GET['id'] ?? 1);
            if ($id < 1 || $id > 30) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz Cüz numarası (1-30 olmalı)']);
                break;
            }
            $ayahs = QuranDB::getAyahsByJuz($id);
            echo json_encode([
                'status' => 'success',
                'juz' => $id,
                'ayahs' => $ayahs
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'page':
            $pageNum = (int)($_GET['id'] ?? 1);
            if ($pageNum < 1 || $pageNum > 604) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz Sayfa numarası (1-604 olmalı)']);
                break;
            }
            $ayahs = QuranDB::getAyahsByPage($pageNum);
            echo json_encode([
                'status' => 'success',
                'page' => $pageNum,
                'ayahs' => $ayahs
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'favorites':
            $rawIds = $_GET['ids'] ?? '';
            $ids = array_filter(array_map('intval', explode(',', $rawIds)));
            $ayahs = QuranDB::getAyahsByIds($ids);
            echo json_encode([
                'status' => 'success',
                'count' => count($ayahs),
                'ayahs' => $ayahs
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'sajdah':
            $ayahs = QuranDB::getSajdahAyahs();
            echo json_encode([
                'status' => 'success',
                'count' => count($ayahs),
                'ayahs' => $ayahs
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'stats':
            $stats = QuranDB::getStats();
            echo json_encode([
                'status' => 'success',
                'stats' => $stats
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'hadiths':
            $category = $_GET['category'] ?? null;
            $q = $_GET['q'] ?? null;
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(5, (int)($_GET['limit'] ?? 30)));
            $data = QuranDB::getHadiths($category, $q, $page, $limit);
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'hadith_categories':
            $categories = QuranDB::getHadithCategories();
            echo json_encode([
                'status' => 'success',
                'categories' => $categories
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'hadith':
            $id = (int)($_GET['id'] ?? 1);
            $hadith = QuranDB::getHadith($id);
            if (!$hadith) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Hadis bulunamadı']);
                break;
            }
            echo json_encode([
                'status' => 'success',
                'hadith' => $hadith
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz işlem: ' . htmlspecialchars($action)
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
