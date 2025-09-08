<?php
$BOT_USER_AGENTS = [
    'googlebot',
    'bingbot',
    'slurp',
    'duckduckbot',
    'baiduspider',
    'yandexbot',
    'sogou',
    'exabot',
    'facebot',
    'ia_archiver',
];
$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
$isBot = false;
foreach ($BOT_USER_AGENTS as $bot) {
    if (strpos($userAgent, $bot) !== false) {
        $isBot = true;
        break;
    }
}

if ($isBot) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="it">
<head>
    <title>Collaborazione Pastorale San Giorgio di Nogaro</title>
    <meta name="description" content="Sito ufficiale della Collaborazione Pastorale di San Giorgio di Nogaro, Marano, Porpetto, Carlino e Zellina. Orari messe, eventi, foglietto parrocchiale e contatti.">
    <meta name="keywords" content="cp sangiorgio, collaborazione pastorale san giorgio, san giorgio di nogaro, collaborazione pastorale san giorgio di nogaro, parrocchia san giorgio di nogaro, parrocchia marano, parrochia carlino, parrochia porpetto, parrochia zellina, orari messe, eventi, foglietto parrocchiale, contatti">
</head>
<body>
    <h1>Collaborazione Pastorale di San Giorgio di Nogaro</h1>
    <p>Benvenuti nel sito ufficiale della Collaborazione Pastorale di San Giorgio di Nogaro. Qui troverete informazioni sulle parrocchie di San Giorgio, Marano Lagunare, Porpetto, Carlino e Zellina.</p>
    <ul>
        <li><a href="/pages/index.html">Home</a></li>
        <li><a href="/pages/chi-siamo.html">Chi Siamo</a></li>
        <li><a href="/pages/contatti.html">Contatti</a></li>
        <li><a href="/pages/eventi.html">Eventi</a></li>
        <li><a href="/pages/privacy.html">Privacy Policy</a></li>
        <li><a href="/pages/segreteria.html">Segreteria</a></li>
        <li><a href="/pages/tutteLeParrocchie.html">Tutte le Parrocchie</a></li>
        <li><a href="/pages/foglietto/">Foglietto Parrocchiale</a></li>
    </ul>
</body>
</html>';
} else {
    header('Location: /pages/index.html');
    exit;
}
?>