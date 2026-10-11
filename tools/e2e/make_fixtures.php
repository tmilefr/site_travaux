<?php
/**
 * Fabrique les fichiers de test de tools/e2e.sh dans le dossier donné :
 * import CSV ABCM (CP1252, 57 colonnes), images JPEG/PNG, document, faux fichiers.
 * Usage : php tools/e2e/make_fixtures.php <dossier>
 */
$dir = rtrim($argv[1] ?? '', '/');
if ($dir === '' || ! is_dir($dir)) {
    fwrite(STDERR, "Usage : php make_fixtures.php <dossier existant>\n");
    exit(1);
}

$row = static function (array $c): string {
    $r = array_fill(0, 57, '');
    foreach (['fam' => 0, 'mem' => 1, 'mnom' => 2, 'mprenom' => 3, 'classe' => 9, 'ecole' => 10, 'pnom' => 28, 'pprenom' => 29,
        'adr' => 31, 'cp' => 32, 'ville' => 33, 'mail' => 38, 'comp' => 56] as $k => $i) {
        $r[$i] = $c[$k] ?? '';
    }

    return implode(';', $r);
};
$base  = ['pnom' => 'Martin', 'pprenom' => 'Paul', 'adr' => '1 rue A', 'cp' => '68100', 'ville' => 'Riedisheim', 'mail' => 'famille1@recette.local', 'ecole' => 'MUL'];
$lines = [
    implode(';', array_map(static fn ($i) => "col$i", range(0, 56))),
    $row($base + ['fam' => 'ZZTEST001', 'mem' => 'ZZM001', 'mnom' => 'Martin', 'mprenom' => 'Lea', 'classe' => '2025 2026 MUL CL04']),
    $row($base + ['fam' => 'ZZTEST001', 'mem' => 'ZZM099', 'mnom' => 'Martin', 'mprenom' => 'Zoé', 'classe' => '2025 2026 MUL CL01']),
    $row(['fam' => 'ZZNEW001', 'mem' => 'ZZM500', 'mnom' => 'Müller', 'mprenom' => 'Hélène', 'classe' => 'CL02', 'ecole' => 'LUT', 'pnom' => 'Müller', 'pprenom' => 'Renée',
        'adr' => "12 rue d\\'Ensisheim", 'cp' => '68200', 'ville' => 'Brunstatt', 'mail' => 'nouvelle.famille@recette.local', 'comp' => 'comp@recette.local']),
    $row(['fam' => 'ZZNEW001', 'mem' => 'ZZM501', 'mnom' => 'Müller', 'mprenom' => 'Noël', 'classe' => 'CL06', 'ecole' => 'MUL', 'pnom' => 'Müller', 'pprenom' => 'Renée',
        'adr' => "12 rue d\\'Ensisheim", 'cp' => '68200', 'ville' => 'Brunstatt', 'mail' => 'nouvelle.famille@recette.local']),
    'ZZBAD;trop;peu;de;colonnes',
    $row(['fam' => 'ZZNOMAIL', 'mem' => 'ZZM600', 'mnom' => 'X', 'mprenom' => 'Y']),
];
file_put_contents("$dir/import_ok.csv", mb_convert_encoding(implode("\r\n", $lines) . "\r\n", 'CP1252', 'UTF-8'));
file_put_contents("$dir/import_bad.txt", 'pas un csv');
file_put_contents("$dir/import_empty.csv", '');

$img = imagecreatetruecolor(800, 600);
imagefill($img, 0, 0, imagecolorallocate($img, 200, 50, 50));
imagejpeg($img, "$dir/photo.jpg");
imagepng($img, "$dir/photo.png");
file_put_contents("$dir/doc.txt", "document de test\n");
file_put_contents("$dir/evil.php", '<?php echo "PWNED";');
file_put_contents("$dir/disguised.jpg", '<?php echo "PWNED";');
