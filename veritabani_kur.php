<?php
if (file_exists('kahve.db')) {
    unlink('kahve.db');
}

$db = new PDO('sqlite:kahve.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("PRAGMA foreign_keys = ON;");

// 1. İlişkisel Tabloların Oluşturulması
$db->exec("
CREATE TABLE ulkeler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ulke_adi TEXT NOT NULL UNIQUE,
    kita TEXT NOT NULL
);

CREATE TABLE kavrum_dereceleri (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kavrum_adi TEXT NOT NULL UNIQUE
);

CREATE TABLE isleme_yontemleri (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    yontem_adi TEXT NOT NULL UNIQUE
);

CREATE TABLE kahveler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kahve_adi TEXT NOT NULL,
    ulke_id INTEGER NOT NULL,
    kavrum_id INTEGER NOT NULL,
    isleme_id INTEGER NOT NULL,
    rakim TEXT NOT NULL,
    tadım_notlari TEXT NOT NULL,
    FOREIGN KEY (ulke_id) REFERENCES ulkeler(id),
    FOREIGN KEY (kavrum_id) REFERENCES kavrum_dereceleri(id),
    FOREIGN KEY (isleme_id) REFERENCES isleme_yontemleri(id)
);
");

// 2. Yardımcı Tabloların Doldurulması
$ulkeler = [
    ['Etiyopya', 'Afrika'], ['Kolombiya', 'Güney Amerika'], ['Endonezya', 'Asya'],
    ['Kenya', 'Afrika'], ['Guatemala', 'Orta Amerika'], ['Brezilya', 'Güney Amerika'],
    ['Kosta Rika', 'Orta Amerika'], ['Honduras', 'Orta Amerika'], ['Peru', 'Güney Amerika'],
    ['Ruanda', 'Afrika'], ['Burundi', 'Afrika'], ['Tanzanya', 'Afrika'],
    ['El Salvador', 'Orta Amerika'], ['Nikaragua', 'Orta Amerika'], ['Panama', 'Orta Amerika'],
    ['Yemen', 'Asya'], ['Hindistan', 'Asya'], ['Vietnam', 'Asya'],
    ['Papua Yeni Gine', 'Okyanusya'], ['Meksika', 'Kuzey Amerika']
];
$stmtUlke = $db->prepare("INSERT INTO ulkeler (ulke_adi, kita) VALUES (?, ?)");
foreach ($ulkeler as $u) $stmtUlke->execute($u);

$kavrumlar = ['Hafif (Light)', 'Orta (Medium)', 'Orta-Koyu (Medium-Dark)', 'Koyu (Dark)'];
$stmtKavrum = $db->prepare("INSERT INTO kavrum_dereceleri (kavrum_adi) VALUES (?)");
foreach ($kavrumlar as $k) $stmtKavrum->execute([$k]);

$yontemler = ['Yıkanmış (Washed)', 'Doğal (Natural)', 'Bal İşleme (Honey)', 'Islak Kabuklu (Wet-Hulled)'];
$stmtYontem = $db->prepare("INSERT INTO isleme_yontemleri (yontem_adi) VALUES (?)");
foreach ($yontemler as $y) $stmtYontem->execute([$y]);

// 3. Ana Tabloya 105 Adet Kahve Kaydı Eklenmesi
$ilkAlti = [
    ['Ethiopian Yirgacheffe', 1, 1, 1, '1950m', 'Yasemin, Bergamot, Limon'],
    ['Colombian Supremo', 2, 2, 1, '1700m', 'Karamel, Fındık, Kırmızı Elma'],
    ['Sumatra Mandheling', 3, 4, 4, '1400m', 'Bitter Çikolata, Sedir, Baharat'],
    ['Kenya AA', 4, 2, 1, '1850m', 'Siyah Frenk Üzümü, Greyfurt'],
    ['Guatemala Antigua', 5, 3, 1, '1600m', 'Kakao, Baharat, Esmer Şeker'],
    ['Türk Kahvesi (Rio Minas)', 6, 2, 2, '1100m', 'Kavrulmuş Fındık, Kakao']
];

$bolgeler = [
    1 => ['Guji', 'Sidamo', 'Harrar', 'Limu', 'Gedeb'],
    2 => ['Huila', 'Narino', 'Tolima', 'Antioquia', 'Caldas'],
    3 => ['Aceh Gayo', 'Java Estate', 'Toraja', 'Bali Kintamani', 'Flores'],
    4 => ['Nyeri', 'Kirinyaga', 'Kiambu', 'Muranga', 'Embu'],
    5 => ['Huehuetenango', 'Acatenango', 'Atitlan', 'Coban', 'Fraijanes'],
    6 => ['Cerrado', 'Mogiana', 'Sul de Minas', 'Bahia', 'Carmo de Minas'],
    7 => ['Tarrazu', 'West Valley', 'Central Valley', 'Brunca', 'Tres Rios'],
    8 => ['Marcala', 'Copan', 'Santa Barbara', 'Opalaca', 'Comayagua'],
    9 => ['Cajamarca', 'Chanchamayo', 'Cusco', 'Amazonas', 'Puno'],
    10 => ['Nyamasheke', 'Huye', 'Gakenke', 'Rulindo', 'Karongi'],
    11 => ['Kayanza', 'Ngozi', 'Muyinga', 'Kirundo', 'Gitega'],
    12 => ['Kilimanjaro', 'Mbeya', 'Arusha', 'Ruvuma', 'Mbinga'],
    13 => ['Santa Ana', 'Apaneca', 'Chalatenango', 'Cacahuatique', 'Usulutan'],
    14 => ['Jinotega', 'Matagalpa', 'Nueva Segovia', 'Madriz', 'Esteli'],
    15 => ['Boquete', 'Volcan', 'Renacimiento', 'Santa Clara', 'Piedra Candela'],
    16 => ['Mocha Matari', 'Haraz', 'Bani Ismail', 'Sanani', 'Dhamar'],
    17 => ['Monsooned Malabar', 'Chikmagalur', 'Coorg', 'Nilgiris', 'Bababudangiri'],
    18 => ['Da Lat', 'Son La', 'Buon Ma Thuot', 'Lam Dong', 'Kon Tum'],
    19 => ['Wahgi Valley', 'Goroka', 'Mount Hagen', 'Kainantu', 'Aiyura'],
    20 => ['Chiapas', 'Oaxaca', 'Veracruz', 'Puebla', 'Guerrero']
];

$tadımHavuzu = [
    'Çiçeksi, Narenciye, Bal', 'Sütlü Çikolata, Badem, Karamel', 'Böğürtlen, Şarap, Kakao',
    'Şeftali, Kayısı, Vanilya', 'Kavrulmuş Ceviz, Tarçın, Pekmez', 'Portakal Çiçeği, Yeşil Elma',
    'Fındık Ezmesi, Toffee, Hurma', 'Vişne, Bitter Çikolata, Karanfil'
];

$stmtKahve = $db->prepare("INSERT INTO kahveler (kahve_adi, ulke_id, kavrum_id, isleme_id, rakim, tadım_notlari) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($ilkAlti as $k) {
    $stmtKahve->execute($k);
}

$sayac = 7;
for ($uId = 1; $uId <= 20; $uId++) {
    foreach ($bolgeler[$uId] as $index => $bolge) {
        if ($sayac > 105) break 2;
        $ulkeAdi = $ulkeler[$uId - 1][0];
        $ad = "$ulkeAdi $bolge Reserve";
        $kavrumId = (($sayac + $index) % 4) + 1;
        $islemeId = (($uId + $index) % 4) + 1;
        $rakim = (1150 + (($sayac * 35) % 950)) . 'm';
        $tadim = $tadımHavuzu[$sayac % count($tadımHavuzu)];
        $stmtKahve->execute([$ad, $uId, $kavrumId, $islemeId, $rakim, $tadim]);
        $sayac++;
    }
}

echo "Başarılı: 4 ilişkisel tablo ve toplam " . $db->query("SELECT COUNT(*) FROM kahveler")->fetchColumn() . " kahve kaydı oluşturuldu.\n";
