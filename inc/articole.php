<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sablon.php';

/* Toate interogările despre articole. Paginile nu scriu SQL: dacă o pagină
   are nevoie de altă interogare, se adaugă aici, nu acolo.

   Toate sunt pregătite, cu parametrii legați. Niciun text venit din formular
   sau din adresă nu ajunge în interogare prin lipire de șiruri. */

const CAMPURI = 'id, slug, titlu, eticheta, rezumat, text, data_publicare, stare, creat_la, actualizat_la';

function articole_publicate(): array
{
    $s = db()->prepare(
        'SELECT ' . CAMPURI . ' FROM articole
          WHERE stare = "publicat"
          ORDER BY data_publicare DESC, id DESC'
    );
    $s->execute();
    return $s->fetchAll();
}

/* Partea publică cere articolul doar dacă e publicat. Ciornele nu se văd
   nici dacă cineva ghicește adresa: întoarcem null, iar pagina face 404. */
function articol_dupa_slug(string $slug): ?array
{
    $s = db()->prepare(
        'SELECT ' . CAMPURI . ' FROM articole WHERE slug = ? AND stare = "publicat" LIMIT 1'
    );
    $s->execute([$slug]);
    $r = $s->fetch();
    return $r === false ? null : $r;
}

function articole_toate(): array
{
    $s = db()->prepare(
        'SELECT ' . CAMPURI . ' FROM articole ORDER BY data_publicare DESC, id DESC'
    );
    $s->execute();
    return $s->fetchAll();
}

function articol_dupa_id(int $id): ?array
{
    $s = db()->prepare('SELECT ' . CAMPURI . ' FROM articole WHERE id = ? LIMIT 1');
    $s->execute([$id]);
    $r = $s->fetch();
    return $r === false ? null : $r;
}

/* Găsește o adresă liberă. La modificare, articolul nu trebuie să se
   lovească de propriul slug, de aceea se poate exclude un id: fără asta,
   salvarea unui articol fără schimbarea titlului l-ar muta la titlu-2, apoi
   titlu-3, la fiecare salvare. */
function slug_liber(string $dorit, ?int $exceptand = null): string
{
    $baza = slug($dorit);
    $incercare = $baza;
    $n = 1;
    while (true) {
        $sql = 'SELECT id FROM articole WHERE slug = ?';
        $param = [$incercare];
        if ($exceptand !== null) {
            $sql .= ' AND id <> ?';
            $param[] = $exceptand;
        }
        $s = db()->prepare($sql . ' LIMIT 1');
        $s->execute($param);
        if ($s->fetch() === false) {
            return $incercare;
        }
        $n++;
        $incercare = $baza . '-' . $n;
    }
}

/* Una singură pentru creare și modificare: câmpurile sunt aceleași, iar două
   funcții aproape identice ar însemna două locuri de ținut la fel. */
function articol_salveaza(array $date, ?int $id = null): int
{
    $slug = $date['slug'] ?? '';
    $slug = slug_liber($slug !== '' ? $slug : $date['titlu'], $id);
    $acum = date('Y-m-d H:i:s');

    if ($id === null) {
        $s = db()->prepare(
            'INSERT INTO articole
             (slug, titlu, eticheta, rezumat, text, data_publicare, stare, creat_la, actualizat_la)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $s->execute([
            $slug, $date['titlu'], $date['eticheta'], $date['rezumat'],
            $date['text'], $date['data_publicare'], $date['stare'], $acum, $acum,
        ]);
        return (int) db()->lastInsertId();
    }

    $s = db()->prepare(
        'UPDATE articole
            SET slug = ?, titlu = ?, eticheta = ?, rezumat = ?, text = ?,
                data_publicare = ?, stare = ?, actualizat_la = ?
          WHERE id = ?'
    );
    $s->execute([
        $slug, $date['titlu'], $date['eticheta'], $date['rezumat'],
        $date['text'], $date['data_publicare'], $date['stare'], $acum, $id,
    ]);
    return $id;
}

function articol_sterge(int $id): void
{
    $s = db()->prepare('DELETE FROM articole WHERE id = ?');
    $s->execute([$id]);
}
