<?php
require_once 'includes/baglanti.php';

// Ekleme, Güncelleme ve Silme (CRUD) İşlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem = $_POST['islem'] ?? '';

    if ($islem === 'ekle') {
        $stmt = $db->prepare("INSERT INTO kahveler (kahve_adi, ulke_id, kavrum_id, isleme_id, rakim, tadım_notlari) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            trim($_POST['kahve_adi']),
            (int)$_POST['ulke_id'],
            (int)$_POST['kavrum_id'],
            (int)$_POST['isleme_id'],
            trim($_POST['rakim']),
            trim($_POST['tadim_notlari'])
        ]);
    } elseif ($islem === 'guncelle') {
        $stmt = $db->prepare("UPDATE kahveler SET kahve_adi = ?, ulke_id = ?, kavrum_id = ?, isleme_id = ?, rakim = ?, tadım_notlari = ? WHERE id = ?");
        $stmt->execute([
            trim($_POST['kahve_adi']),
            (int)$_POST['ulke_id'],
            (int)$_POST['kavrum_id'],
            (int)$_POST['isleme_id'],
            trim($_POST['rakim']),
            trim($_POST['tadim_notlari']),
            (int)$_POST['id']
        ]);
    } elseif ($islem === 'sil') {
        $stmt = $db->prepare("DELETE FROM kahveler WHERE id = ?");
        $stmt->execute([(int)$_POST['id']]);
    }

    header("Location: index.php?durum=" . urlencode($islem));
    exit;
}

// Formlardaki Select Kutuları İçin İlişkisel Tablo Verileri
$ulkeler = $db->query("SELECT * FROM ulkeler ORDER BY ulke_adi ASC")->fetchAll(PDO::FETCH_ASSOC);
$kavrumlar = $db->query("SELECT * FROM kavrum_dereceleri ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$islemeYontemleri = $db->query("SELECT * FROM isleme_yontemleri ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Sunucu Taraflı Arama ve Filtreleme Parametreleri
$arama = trim($_GET['q'] ?? '');
$filtreUlke = $_GET['ulke'] ?? '';
$filtreKavrum = $_GET['kavrum'] ?? '';

$sql = "
SELECT 
    k.id,
    k.kahve_adi,
    k.ulke_id,
    k.kavrum_id,
    k.isleme_id,
    k.rakim,
    k.tadım_notlari,
    u.ulke_adi,
    u.kita,
    kd.kavrum_adi,
    iy.yontem_adi
FROM kahveler k
INNER JOIN ulkeler u ON k.ulke_id = u.id
INNER JOIN kavrum_dereceleri kd ON k.kavrum_id = kd.id
INNER JOIN isleme_yontemleri iy ON k.isleme_id = iy.id
WHERE 1=1
";

$params = [];
if ($arama !== '') {
    $sql .= " AND (k.kahve_adi LIKE ? OR u.ulke_adi LIKE ? OR k.tadım_notlari LIKE ? OR iy.yontem_adi LIKE ?)";
    $like = "%$arama%";
    array_push($params, $like, $like, $like, $like);
}
if ($filtreUlke !== '') {
    $sql .= " AND k.ulke_id = ?";
    $params[] = (int)$filtreUlke;
}
if ($filtreKavrum !== '') {
    $sql .= " AND k.kavrum_id = ?";
    $params[] = (int)$filtreKavrum;
}

$sql .= " ORDER BY k.id ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$kahveler = $stmt->fetchAll(PDO::FETCH_ASSOC);

$toplamKahve = $db->query("SELECT COUNT(*) FROM kahveler")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dünya Kahveleri İlişkisel Veritabanı</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background-color: #FAF6F1; color: #2C1E16; font-family: system-ui, -apple-system, sans-serif; }
  </style>
</head>
<body class="min-h-screen pb-14">

  <!-- Üst Başlık -->
  <header class="bg-[#2C1E16] text-[#FAF6F1] shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">☕ Dünya Kahveleri Veritabanı</h1>
        <p class="text-sm text-[#D7C4B7] mt-1">Çoklu Tablo İlişkisel SQLite (kahve.db) & PHP Yönetim Sistemi</p>
      </div>
      <button onclick="yeniModalAc()" class="bg-[#C87D4B] hover:bg-[#b56d3d] text-white font-medium px-5 py-2.5 rounded-lg shadow transition cursor-pointer">
        + Yeni Kahve Ekle
      </button>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 mt-8 space-y-6">

    <?php if (isset($_GET['durum'])): ?>
      <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium flex justify-between items-center">
        <span>
          <?php
            if ($_GET['durum'] === 'ekle') echo "Yeni kahve kaydı ilişkisel veritabanına eklendi.";
            elseif ($_GET['durum'] === 'guncelle') echo "Kahve kaydı başarıyla güncellendi.";
            elseif ($_GET['durum'] === 'sil') echo "Kahve kaydı veritabanından silindi.";
          ?>
        </span>
        <a href="index.php" class="text-emerald-900 underline text-xs">Kapat</a>
      </div>
    <?php endif; ?>

    <!-- İlişkisel Özet Kartları -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-5 rounded-xl border border-[#EADFD5] shadow-sm">
        <p class="text-xs uppercase tracking-wider text-[#7A6252] font-semibold">Ana Tablo (Kahveler)</p>
        <p class="text-3xl font-bold mt-1"><?= $toplamKahve ?></p>
        <p class="text-xs text-[#9C8270] mt-1">Listelenen: <?= count($kahveler) ?> kayıt</p>
      </div>
      <div class="bg-white p-5 rounded-xl border border-[#EADFD5] shadow-sm">
        <p class="text-xs uppercase tracking-wider text-[#7A6252] font-semibold">Ülkeler Tablosu</p>
        <p class="text-3xl font-bold mt-1"><?= count($ulkeler) ?></p>
        <p class="text-xs text-[#9C8270] mt-1">FK: ulke_id</p>
      </div>
      <div class="bg-white p-5 rounded-xl border border-[#EADFD5] shadow-sm">
        <p class="text-xs uppercase tracking-wider text-[#7A6252] font-semibold">Kavrum Tablosu</p>
        <p class="text-3xl font-bold mt-1"><?= count($kavrumlar) ?></p>
        <p class="text-xs text-[#9C8270] mt-1">FK: kavrum_id</p>
      </div>
      <div class="bg-white p-5 rounded-xl border border-[#EADFD5] shadow-sm">
        <p class="text-xs uppercase tracking-wider text-[#7A6252] font-semibold">İşleme Yöntemleri</p>
        <p class="text-3xl font-bold mt-1"><?= count($islemeYontemleri) ?></p>
        <p class="text-xs text-[#9C8270] mt-1">FK: isleme_id</p>
      </div>
    </div>

    <!-- Arama ve İlişkisel Filtreleme Formu -->
    <form method="GET" class="bg-white p-4 rounded-xl border border-[#EADFD5] shadow-sm grid grid-cols-1 md:grid-cols-12 gap-3">
      <div class="md:col-span-5">
        <input type="text" name="q" value="<?= htmlspecialchars($arama) ?>" placeholder="Kahve adı, ülke, işleme veya tadım notu ara..."
               class="w-full px-3.5 py-2 border border-[#D7C4B7] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#C87D4B]" />
      </div>
      <div class="md:col-span-3">
        <select name="ulke" class="w-full px-3 py-2 border border-[#D7C4B7] rounded-lg text-sm bg-white">
          <option value="">Tüm Ülkeler (Ülke Tablosu)</option>
          <?php foreach ($ulkeler as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $filtreUlke == $u['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['ulke_adi']) ?> (<?= htmlspecialchars($u['kita']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="md:col-span-2">
        <select name="kavrum" class="w-full px-3 py-2 border border-[#D7C4B7] rounded-lg text-sm bg-white">
          <option value="">Tüm Kavrumlar</option>
          <?php foreach ($kavrumlar as $kv): ?>
            <option value="<?= $kv['id'] ?>" <?= $filtreKavrum == $kv['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($kv['kavrum_adi']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="md:col-span-2 flex gap-2">
        <button type="submit" class="w-full bg-[#2C1E16] hover:bg-[#432f23] text-white text-sm font-medium py-2 px-3 rounded-lg cursor-pointer">
          Ara / Filtrele
        </button>
        <?php if ($arama !== '' || $filtreUlke !== '' || $filtreKavrum !== ''): ?>
          <a href="index.php" class="px-3 py-2 border border-[#D7C4B7] rounded-lg text-sm text-[#5C4332] hover:bg-[#FAF6F1]">Sıfırla</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- İlişkisel Kahve Listesi Tablosu -->
    <div class="bg-white rounded-xl border border-[#EADFD5] shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#F3ECE4] text-[#5C4332] text-xs uppercase tracking-wider border-b border-[#EADFD5]">
              <th class="py-3.5 px-4 font-semibold">ID</th>
              <th class="py-3.5 px-4 font-semibold">Kahve Adı</th>
              <th class="py-3.5 px-4 font-semibold">Köken (Ülke / Kıta)</th>
              <th class="py-3.5 px-4 font-semibold">Kavrum Derecesi</th>
              <th class="py-3.5 px-4 font-semibold">İşleme Yöntemi</th>
              <th class="py-3.5 px-4 font-semibold">Rakım</th>
              <th class="py-3.5 px-4 font-semibold">Tadım Notları</th>
              <th class="py-3.5 px-4 font-semibold text-right">İşlemler</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F0E6DD] text-sm">
            <?php if (empty($kahveler)): ?>
              <tr>
                <td colspan="8" class="py-8 text-center text-[#7A6252]">Arama kriterlerine uygun kayıt bulunamadı.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($kahveler as $k): ?>
                <tr class="hover:bg-[#FCF9F6] transition">
                  <td class="py-3 px-4 font-medium text-[#7A6252]"><?= $k['id'] ?></td>
                  <td class="py-3 px-4 font-semibold text-[#2C1E16]"><?= htmlspecialchars($k['kahve_adi']) ?></td>
                  <td class="py-3 px-4">
                    <span class="font-medium"><?= htmlspecialchars($k['ulke_adi']) ?></span>
                    <span class="text-xs text-[#8C7362] block"><?= htmlspecialchars($k['kita']) ?></span>
                  </td>
                  <td class="py-3 px-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-[#EFE4D8] text-[#5C4332]">
                      <?= htmlspecialchars($k['kavrum_adi']) ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-[#5C4332]"><?= htmlspecialchars($k['yontem_adi']) ?></td>
                  <td class="py-3 px-4 text-xs font-mono text-[#7A6252]"><?= htmlspecialchars($k['rakim']) ?></td>
                  <td class="py-3 px-4 text-xs text-[#5C4332]"><?= htmlspecialchars($k['tadım_notlari']) ?></td>
                  <td class="py-3 px-4 text-right whitespace-nowrap space-x-2">
                    <button onclick='duzenleModalAc(<?= json_encode($k, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                            class="text-amber-700 hover:underline font-medium cursor-pointer">Düzenle</button>
                    <form method="POST" class="inline" onsubmit="return confirm('ID #<?= $k['id'] ?> kaydını silmek istediğine emin misin?');">
                      <input type="hidden" name="islem" value="sil" />
                      <input type="hidden" name="id" value="<?= $k['id'] ?>" />
                      <button type="submit" class="text-red-600 hover:underline font-medium cursor-pointer">Sil</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Ekle / Güncelle Modalı -->
  <div id="kahveModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl">
      <h2 id="modalBaslik" class="text-xl font-bold mb-4">Yeni Kahve Ekle</h2>
      <form method="POST" class="space-y-4">
        <input type="hidden" name="islem" id="formIslem" value="ekle" />
        <input type="hidden" name="id" id="formId" value="" />

        <div>
          <label class="block text-sm font-medium mb-1">Kahve Adı</label>
          <input type="text" name="kahve_adi" id="formAd" required placeholder="Örn: Ethiopian Guji Natural"
                 class="w-full px-3 py-2 border border-[#D7C4B7] rounded-lg" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium mb-1">Köken Ülke (ulkeler tablosu)</label>
            <select name="ulke_id" id="formUlke" required class="w-full px-3 py-2 border border-[#D7C4B7] rounded-lg bg-white">
              <?php foreach ($ulkeler as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['ulke_adi']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Kavrum (kavrum_dereceleri)</label>
            <select name="kavrum_id" id="formKavrum" required class="w-full px-3 py-2 border border-[#D7C4B7] rounded-lg bg-white">
              <?php foreach ($kavrumlar as $kv): ?>
                <option value="<?= $kv['id'] ?>"><?= htmlspecialchars($kv['kavrum_adi']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium mb-1">İşleme (isleme_yontemleri)</label>
            <select name="isleme_id" id="formIsleme" required class="w-full px-3 py-2 border border-[#D7C4B7] rounded-lg bg-white">
              <?php foreach ($islemeYontemleri as $iy): ?>
                <option value="<?= $iy['id'] ?>"><?= htmlspecialchars($iy['yontem_adi']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Rakım</label>
            <input type="text" name="rakim" id="formRakim" required placeholder="Örn: 1850m"
                   class="w-full px-3 py-2 border border-[#D7C4B7] rounded-lg" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Tadım Notları</label>
          <input type="text" name="tadim_notlari" id="formTadim" required placeholder="Örn: Yasemin, Bergamot, Bal"
                 class="w-full px-3 py-2 border border-[#D7C4B7] rounded-lg" />
        </div>

        <div class="flex justify-end gap-2 pt-3">
          <button type="button" onclick="modalKapat()" class="px-4 py-2 rounded-lg border border-[#D7C4B7] cursor-pointer">İptal</button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-[#2C1E16] text-white font-medium cursor-pointer">Kaydet</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function yeniModalAc() {
      document.getElementById('modalBaslik').innerText = 'Yeni Kahve Ekle';
      document.getElementById('formIslem').value = 'ekle';
      document.getElementById('formId').value = '';
      document.getElementById('formAd').value = '';
      document.getElementById('formRakim').value = '1600m';
      document.getElementById('formTadim').value = '';
      document.getElementById('kahveModal').classList.remove('hidden');
      document.getElementById('kahveModal').classList.add('flex');
    }

    function duzenleModalAc(k) {
      document.getElementById('modalBaslik').innerText = 'Kahve Kaydını Güncelle (#' + k.id + ')';
      document.getElementById('formIslem').value = 'guncelle';
      document.getElementById('formId').value = k.id;
      document.getElementById('formAd').value = k.kahve_adi;
      document.getElementById('formUlke').value = k.ulke_id;
      document.getElementById('formKavrum').value = k.kavrum_id;
      document.getElementById('formIsleme').value = k.isleme_id;
      document.getElementById('formRakim').value = k.rakim;
      document.getElementById('formTadim').value = k.tadım_notlari;
      document.getElementById('kahveModal').classList.remove('hidden');
      document.getElementById('kahveModal').classList.add('flex');
    }

    function modalKapat() {
      document.getElementById('kahveModal').classList.add('hidden');
      document.getElementById('kahveModal').classList.remove('flex');
    }
  </script>
</body>
</html>
