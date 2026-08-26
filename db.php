<?php
/**
 * Kur'an-ı Kerim Veritabanı ve Akıllı Arama Motoru
 * Çeviri: Prof. Dr. Yaşar Nuri Öztürk (Ana Meal)
 * Karşılaştırmalı Mealler: Elmalılı Hamdi Yazır, Süleyman Ateş, Diyanet İşleri
 */

class QuranDB {
    private static ?PDO $pdo = null;
    private static string $dbPath = __DIR__ . '/quran.db';

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            if (!file_exists(self::$dbPath)) {
                throw new Exception("Veritabanı dosyası (quran.db) bulunamadı. Lütfen önce build_db.py çalıştırın.");
            }
            self::$pdo = new PDO('sqlite:' . self::$dbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 10
            ]);

            // Özel SQLite fonksiyonları
            self::$pdo->sqliteCreateFunction('TR_NORMALIZE', [self::class, 'normalizeTurkishText'], 1);
            self::$pdo->sqliteCreateFunction('AR_NORMALIZE', [self::class, 'normalizeArabicText'], 1);
        }
        return self::$pdo;
    }

    /**
     * Türkçe metin normalizasyonu (Büyük/küçük harf, Türkçe karakterler, şapkalı harfler)
     */
    public static function normalizeTurkishText(string $text): string {
        $text = mb_strtolower($text, 'UTF-8');
        $replace = [
            'ı' => 'i', 'i' => 'i', 'İ' => 'i', 'I' => 'i',
            'ç' => 'c', 'ğ' => 'g', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
            'â' => 'a', 'î' => 'i', 'û' => 'u', 'ê' => 'e', 'ô' => 'o',
            '’' => "'", '‘' => "'", '`' => "'", '´' => "'"
        ];
        return strtr($text, $replace);
    }

    /**
     * Arapça metin normalizasyonu (Harekeleri ve harf varyasyonlarını kaldırma)
     */
    public static function normalizeArabicText(string $text): string {
        // 1. Dagger alef ve elif varyasyonlarını düz elif yap
        $text = preg_replace('/[\x{0670}\x{0671}\x{0622}\x{0623}\x{0625}\x{0672}\x{0673}\x{0675}]/u', 'ا', $text);
        // 2. Harekeleri, kur'an durak işaretlerini, tatweel ve özel sembolleri temizle
        $text = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{06D6}-\x{06ED}\x{06E1}\x{0653}\x{0640}]/u', '', $text);
        // 3. Noktalı/noktasız ye
        $text = preg_replace('/[ىي]/u', 'ي', $text);
        // 4. Noktalı/noktasız te marbuta ve he
        $text = preg_replace('/[ةه]/u', 'ه', $text);
        return $text;
    }

    /**
     * Yazım hatası veya harf tekrarı temizleme (Örn: "healal" -> "helal", "kuraan" -> "kuran")
     */
    public static function cleanTypos(string $word): array {
        $variants = [$word];
        
        // 1. Yan yana tekrarlanan sesli harfleri teke düşür (ea -> e/a, aa -> a, ee -> e vb.)
        $reducedVowels = preg_replace('/([aeiouıöü])\1+/ui', '$1', $word);
        if ($reducedVowels !== $word) {
            $variants[] = $reducedVowels;
        }

        // 2. "ea" harf birleşimi hatası (healal -> helal)
        if (stripos($word, 'ea') !== false) {
            $variants[] = str_ireplace('ea', 'e', $word);
            $variants[] = str_ireplace('ea', 'a', $word);
        }

        // 3. Bilinen popüler dini kavram eşleştirmeleri
        $commonSynonyms = [
            'healal' => 'helal',
            'halal' => 'helal',
            'harram' => 'haram',
            'cennet' => 'cennet',
            'cehennem' => 'cehennem',
            'namas' => 'namaz',
            'salat' => 'namaz',
            'zekat' => 'zekat',
            'infak' => 'infak',
            'kuran' => 'kuran',
            'kuran-i kerim' => 'kuran'
        ];
        $norm = self::normalizeTurkishText($word);
        if (isset($commonSynonyms[$norm])) {
            $variants[] = $commonSynonyms[$norm];
        }

        return array_values(array_unique($variants));
    }

    /**
     * Tüm sure listesini getirir
     */
    public static function getSurahs(): array {
        $db = self::getConnection();
        $stmt = $db->query("SELECT * FROM surahs ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Belirli bir surenin bilgilerini getirir
     */
    public static function getSurah(int $id): ?array {
        $db = self::getConnection();
        $stmt = $db->prepare("SELECT * FROM surahs WHERE id = ?");
        $stmt->execute([$id]);
        $surah = $stmt->fetch();
        return $surah ?: null;
    }

    /**
     * Belirli bir surenin ayetlerini getirir
     */
    public static function getAyahsBySurah(int $surahId, int $offset = 0, int $limit = 500): array {
        $db = self::getConnection();
        $stmt = $db->prepare("
            SELECT a.*, s.name_tr as surah_name_tr, s.name_ar as surah_name_ar, s.name_en as surah_name_en, s.revelation_type
            FROM ayahs a
            JOIN surahs s ON s.id = a.surah_id
            WHERE a.surah_id = ?
            ORDER BY a.ayah_number ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$surahId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Cüze göre ayetleri getirir (1-30)
     */
    public static function getAyahsByJuz(int $juz, int $limit = 400): array {
        $db = self::getConnection();
        $stmt = $db->prepare("
            SELECT a.*, s.name_tr as surah_name_tr, s.name_ar as surah_name_ar, s.name_en as surah_name_en, s.revelation_type
            FROM ayahs a
            JOIN surahs s ON s.id = a.surah_id
            WHERE a.juz = ?
            ORDER BY a.id ASC
            LIMIT ?
        ");
        $stmt->execute([$juz, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Mushaf Sayfasına göre ayetleri getirir (1-604)
     */
    public static function getAyahsByPage(int $page): array {
        $db = self::getConnection();
        $stmt = $db->prepare("
            SELECT a.*, s.name_tr as surah_name_tr, s.name_ar as surah_name_ar, s.name_en as surah_name_en, s.revelation_type
            FROM ayahs a
            JOIN surahs s ON s.id = a.surah_id
            WHERE a.page = ?
            ORDER BY a.id ASC
        ");
        $stmt->execute([$page]);
        return $stmt->fetchAll();
    }

    /**
     * ID Listesine göre ayetleri getirir (Favoriler / Yer İmleri)
     */
    public static function getAyahsByIds(array $ids): array {
        if (empty($ids)) return [];
        $db = self::getConnection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("
            SELECT a.*, s.name_tr as surah_name_tr, s.name_ar as surah_name_ar, s.name_en as surah_name_en, s.revelation_type
            FROM ayahs a
            JOIN surahs s ON s.id = a.surah_id
            WHERE a.id IN ($placeholders)
            ORDER BY a.id ASC
        ");
        $stmt->execute(array_values($ids));
        return $stmt->fetchAll();
    }

    /**
     * Secde ayetlerini getirir
     */
    public static function getSajdahAyahs(): array {
        $db = self::getConnection();
        $stmt = $db->query("
            SELECT a.*, s.name_tr as surah_name_tr, s.name_ar as surah_name_ar, s.name_en as surah_name_en, s.revelation_type
            FROM ayahs a
            JOIN surahs s ON s.id = a.surah_id
            WHERE a.sajdah = 1
            ORDER BY a.id ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Gelişmiş Kur'an Arama Motoru
     * Mantıksal Operatörler (AND, OR, "Tam Kalıp"), Türkçe Yaşar Nuri Öztürk meali, Arapça metin ve Ayet referansları
     */
    public static function search(string $query, ?int $surahId = null, string $revelationFilter = 'all', int $page = 1, int $perPage = 30): array {
        $db = self::getConnection();
        $query = trim($query);
        if ($query === '') {
            return [
                'total' => 0,
                'page' => 1,
                'per_page' => $perPage,
                'total_pages' => 0,
                'results' => [],
                'query' => '',
                'search_terms' => []
            ];
        }

        // 1. "Sure No:Ayet No" formatı (Örn: "2:255" veya "2.255")
        if (preg_match('/^(\d+)\s*[:\.]\s*(\d+)$/', $query, $matches)) {
            $sId = (int)$matches[1];
            $aNum = (int)$matches[2];
            $stmt = $db->prepare("
                SELECT a.*, s.name_tr as surah_name_tr, s.name_ar as surah_name_ar, s.name_en as surah_name_en, s.revelation_type
                FROM ayahs a
                JOIN surahs s ON s.id = a.surah_id
                WHERE a.surah_id = ? AND a.ayah_number = ?
            ");
            $stmt->execute([$sId, $aNum]);
            $res = $stmt->fetchAll();
            return [
                'total' => count($res),
                'page' => 1,
                'per_page' => $perPage,
                'total_pages' => 1,
                'results' => $res,
                'query_type' => 'reference',
                'query' => $query,
                'search_terms' => []
            ];
        }

        // 2. "Sure Adı Ayet No" formatı (Örn: "Bakara 168", "Fatiha 1", "Yasin 58")
        if (preg_match('/^([^\d]+)\s+(\d+)$/u', $query, $matches)) {
            $sName = trim($matches[1]);
            $aNum = (int)$matches[2];
            $normSName = self::normalizeTurkishText($sName);
            $stmt = $db->prepare("
                SELECT a.*, s.name_tr as surah_name_tr, s.name_ar as surah_name_ar, s.name_en as surah_name_en, s.revelation_type
                FROM ayahs a
                JOIN surahs s ON s.id = a.surah_id
                WHERE (TR_NORMALIZE(s.name_tr) LIKE ? OR TR_NORMALIZE(s.name_en) LIKE ?) AND a.ayah_number = ?
            ");
            $stmt->execute(['%' . $normSName . '%', '%' . $normSName . '%', $aNum]);
            $res = $stmt->fetchAll();
            if (!empty($res)) {
                return [
                    'total' => count($res),
                    'page' => 1,
                    'per_page' => $perPage,
                    'total_pages' => 1,
                    'results' => $res,
                    'query_type' => 'reference',
                    'query' => $query,
                    'search_terms' => []
                ];
            }
        }

        // 3. Sadece Sure Adı girilmişse (Örn: "İhlas", "Fatiha", "Kevser")
        $normQuery = self::normalizeTurkishText($query);
        $stmtSurah = $db->prepare("
            SELECT id FROM surahs 
            WHERE TR_NORMALIZE(name_tr) = ? OR TR_NORMALIZE(name_en) = ?
        ");
        $stmtSurah->execute([$normQuery, $normQuery]);
        $exactSurah = $stmtSurah->fetch();
        if ($exactSurah && $surahId === null) {
            $sId = (int)$exactSurah['id'];
            $surahData = self::getSurah($sId);
            $ayahs = self::getAyahsBySurah($sId, 0, $perPage);
            return [
                'total' => $surahData['ayahs_count'],
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int)ceil($surahData['ayahs_count'] / $perPage),
                'results' => $ayahs,
                'query_type' => 'surah_direct',
                'surah' => $surahData,
                'query' => $query,
                'search_terms' => []
            ];
        }

        // 4. Genel Metin Arama (Mantıksal AND / OR / Tırnak İçi Tam Kalıp)
        $isArabic = (bool)preg_match('/[\x{0600}-\x{06FF}]/u', $query);
        $searchTerms = [];
        $conditions = [];
        $params = [];

        if ($isArabic) {
            $normQueryAr = self::normalizeArabicText($query);
            $searchTerms[] = $normQueryAr;
            $conditions[] = "(a.text_ar_clean LIKE ? OR AR_NORMALIZE(a.text_ar_uthmani) LIKE ?)";
            $params[] = '%' . $normQueryAr . '%';
            $params[] = '%' . $normQueryAr . '%';
        } else {
            // Tırnak içindeki tam kalıplar ("temiz ve helal")
            if (preg_match('/^"([^"]+)"$/', $query, $qMatches)) {
                $phrase = trim($qMatches[1]);
                $normPhrase = self::normalizeTurkishText($phrase);
                $searchTerms[] = $normPhrase;
                $conditions[] = "(a.text_tr_clean LIKE ? OR TR_NORMALIZE(a.text_tr_ozturk) LIKE ?)";
                $params[] = '%' . $normPhrase . '%';
                $params[] = '%' . $normPhrase . '%';
            } elseif (stripos($query, ' OR ') !== false) {
                // OR mantığı: A OR B
                $orParts = explode(' OR ', $query);
                $orConditions = [];
                foreach ($orParts as $part) {
                    $part = trim($part);
                    if ($part === '') continue;
                    $normPart = self::normalizeTurkishText($part);
                    $searchTerms[] = $normPart;
                    $orConditions[] = "(a.text_tr_clean LIKE ? OR TR_NORMALIZE(a.text_tr_ozturk) LIKE ?)";
                    $params[] = '%' . $normPart . '%';
                    $params[] = '%' . $normPart . '%';
                }
                if (!empty($orConditions)) {
                    $conditions[] = '(' . implode(' OR ', $orConditions) . ')';
                }
            } else {
                // Standart veya AND mantığı
                $cleanQ = str_ireplace(' AND ', ' ', $query);
                $rawWords = preg_split('/\s+/', $cleanQ);
                $wordConditions = [];
                
                foreach ($rawWords as $rawWord) {
                    if (trim($rawWord) === '') continue;
                    $variants = self::cleanTypos($rawWord);
                    $subConditions = [];
                    foreach ($variants as $v) {
                        $normV = self::normalizeTurkishText($v);
                        $searchTerms[] = $normV;
                        $subConditions[] = "(a.text_tr_clean LIKE ? OR TR_NORMALIZE(a.text_tr_ozturk) LIKE ? OR TR_NORMALIZE(a.text_tr_transliteration) LIKE ?)";
                        $params[] = '%' . $normV . '%';
                        $params[] = '%' . $normV . '%';
                        $params[] = '%' . $normV . '%';
                    }
                    if (!empty($subConditions)) {
                        $wordConditions[] = '(' . implode(' OR ', $subConditions) . ')';
                    }
                }

                if (!empty($wordConditions)) {
                    $conditions[] = '(' . implode(' AND ', $wordConditions) . ')';
                }
            }
        }

        // Sure filtresi
        if ($surahId !== null && $surahId > 0) {
            $conditions[] = "a.surah_id = ?";
            $params[] = $surahId;
        }

        // İniş yeri filtresi (Mekke / Medine)
        if ($revelationFilter === 'Mekke' || $revelationFilter === 'Medine') {
            $conditions[] = "s.revelation_type = ?";
            $params[] = $revelationFilter;
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Toplam sonuç sayısı
        $countSql = "
            SELECT COUNT(*)
            FROM ayahs a
            JOIN surahs s ON s.id = a.surah_id
            {$whereClause}
        ";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        // Sonuçları getir
        $sql = "
            SELECT a.*, s.name_tr as surah_name_tr, s.name_ar as surah_name_ar, s.name_en as surah_name_en, s.revelation_type
            FROM ayahs a
            JOIN surahs s ON s.id = a.surah_id
            {$whereClause}
            ORDER BY a.surah_id ASC, a.ayah_number ASC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'results' => $results,
            'query' => $query,
            'search_terms' => array_values(array_unique($searchTerms)),
            'is_arabic' => $isArabic
        ];
    }

    /**
     * İstatistikler
     */
    public static function getStats(): array {
        $db = self::getConnection();
        $surahCount = $db->query("SELECT COUNT(*) FROM surahs")->fetchColumn();
        $ayahCount = $db->query("SELECT COUNT(*) FROM ayahs")->fetchColumn();
        $meccanCount = $db->query("SELECT COUNT(*) FROM surahs WHERE revelation_type = 'Mekke'")->fetchColumn();
        $medinanCount = $db->query("SELECT COUNT(*) FROM surahs WHERE revelation_type = 'Medine'")->fetchColumn();

        return [
            'surahs' => (int)$surahCount,
            'ayahs' => (int)$ayahCount,
            'hadiths' => (int)$db->query("SELECT COUNT(*) FROM hadiths")->fetchColumn(),
            'meccan_surahs' => (int)$meccanCount,
            'medinan_surahs' => (int)$medinanCount,
            'translator' => 'Prof. Dr. Yaşar Nuri Öztürk',
            'comparison_translators' => [
                'Elmalılı Hamdi Yazır',
                'Süleyman Ateş',
                'Diyanet İşleri'
            ]
        ];
    }

    /**
     * Sahih Hadis Kategorilerini Getirir
     */
    public static function getHadithCategories(): array {
        $db = self::getConnection();
        $stmt = $db->query("SELECT category, category_name, COUNT(*) as count FROM hadiths GROUP BY category, category_name ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Sahih Hadisleri Filtreler ve Listeler
     */
    public static function getHadiths(?string $category = null, ?string $query = null, int $page = 1, int $perPage = 30): array {
        $db = self::getConnection();
        $conditions = [];
        $params = [];

        if (!empty($category) && $category !== 'all') {
            $conditions[] = "category = ?";
            $params[] = $category;
        }

        if (!empty($query)) {
            $words = explode(' ', trim($query));
            $wordConditions = [];
            foreach ($words as $w) {
                if (mb_strlen($w) >= 2) {
                    $norm = self::normalizeTurkishText($w);
                    $wordConditions[] = "(text_tr_clean LIKE ? OR title LIKE ? OR narrator LIKE ? OR source LIKE ?)";
                    $params[] = '%' . $norm . '%';
                    $params[] = '%' . $w . '%';
                    $params[] = '%' . $w . '%';
                    $params[] = '%' . $w . '%';
                }
            }
            if (!empty($wordConditions)) {
                $conditions[] = '(' . implode(' AND ', $wordConditions) . ')';
            }
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $countStmt = $db->prepare("SELECT COUNT(*) FROM hadiths {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("SELECT * FROM hadiths {$whereClause} ORDER BY id ASC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'results' => $results,
            'category' => $category,
            'query' => $query
        ];
    }

    /**
     * Tek bir hadisin detayını getirir
     */
    public static function getHadith(int $id): ?array {
        $db = self::getConnection();
        $stmt = $db->prepare("SELECT * FROM hadiths WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}
