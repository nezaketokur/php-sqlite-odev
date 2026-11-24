<?php
require_once 'includes/baglanti.php';
include 'includes/header.php';

$sorgu = $db->query("SELECT * FROM ulkeler");
$ulkeler = $sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Kahve Menşei (Ülkeler)</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Ülke Adı</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ulkeler as $ulke): ?>
            <tr>
                <td><?php echo htmlspecialchars($ulke['id']); ?></td>
                <td><?php echo htmlspecialchars($ulke['ulke_adi']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>