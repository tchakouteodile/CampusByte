<?php
// config/controllo_scadenze.php
// Incluso in cima alla dashboard per verificare lo stato dei prestiti attivi

$stmt_attivi = $pdo->query("SELECT * FROM transazioni WHERE stato_transazione IN ('in_corso', 'ritardo_morbido')");
$transazioni_attive = $stmt_attivi->fetchAll();

foreach ($transazioni_attive as $t) {
    if ($t['data_inizio_effettiva'] !== NULL) {
        $inizio = strtotime($t['data_inizio_effettiva']);
        $durata_secondi = $t['durata_ore_max'] * 3600;
        $scadenza = $inizio + $durata_secondi;
        $adesso = time();

        if ($adesso > $scadenza) {
            $secondi_ritardo = $adesso - $scadenza;
            $ore_ritardo = $secondi_ritardo / 3600;

            if ($ore_ritardo <= 2) {
                // LIVELLO 1: Ritardo entro le 2 ore -> Diminuzione punti Karma
                $update = $pdo->prepare("UPDATE transazioni SET stato_transazione = 'ritardo_morbido' WHERE id = ?");
                $update->execute([$t['id']]);

            } elseif ($ore_ritardo > 2 && $ore_ritardo <= 5) {
                // LIVELLO 2: Oltre le 2 ore -> Scatta l'obbligo della penale monetaria minima
                // L'utente viene bloccato nel sistema finché non risolve
                $update = $pdo->prepare("UPDATE transazioni SET stato_transazione = 'bloccato_denaro' WHERE id = ?");
                $update->execute([$t['id']]);

            } elseif ($ore_ritardo > 5) {
                // LIVELLO 3: Oltre le 5 ore -> RISCATTO FORZOSO DEFINITIVO
                // Calcolo indennità completa: Valore Totale - Penale minima già applicata
                $differenza_riscatto = $t['valore_stima_oggetto'] - $t['penale_denaro_min'];
                if ($differenza_riscatto < 0) $differenza_riscatto = 0;

                // Chiudiamo d'ufficio la transazione come riscatto completo
                $update = $pdo->prepare("UPDATE transazioni SET stato_transazione = 'riscatto_totale', data_restituzione_effettiva = NOW() WHERE id = ?");
                $update->execute([$t['id']]);

                // Impostiamo il post come completato (l'oggetto ormai è stato comprato forzosamente dal ritardatario)
                $update_post = $pdo->prepare("UPDATE bacheca SET stato = 'completato' WHERE id = ?");
                $update_post->execute([$t['id_post']]);
                
            }
        }
    }
}