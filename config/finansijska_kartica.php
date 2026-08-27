<?php
/**
 * Sastavljanje stavki finansijske kartice (uplate + zaduženja) za JEDNOG djaka.
 * Izdvojeno iz kartica_djak_old.php da bi ista logika mogla da se pozove u petlji
 * za zbirnu karticu (sva deca jednog roditelja) - vidi zbirna_kartica.php. Vraća
 * SIROVE podatke, bez ikakvog HTML izlaza - render radi pozivalac.
 */
function sakupi_stavke_kartice($db, $idd)
{
    $grupa = new grupa($db);
    $povezivanje = new povezivanje($db);
    $zaduzenje = new zaduzenje($db);
    $evidencija = new evidencija($db);
    $cenovnik = new cenovnik($db);
    $uplata = new uplata($db);
    $jezik = new jezik($db);
    $nivo_znanja = new nivo_znanja($db);

    $kartica = [];
    $i = -1;
    $grupe_info = [];

    $ttss = $povezivanje->read_all_group_students_za_finasijsku($idd);
    while ($row_category_grupe = $ttss->fetch(PDO::FETCH_ASSOC)) {
        $sst = $grupa->read_one_grupa1($row_category_grupe['fk_grupa']);
        $row_citanje_grupe = $sst->fetch(PDO::FETCH_ASSOC);
        $status_grupe = $row_citanje_grupe['status'] == false ? 'neaktivna' : 'aktivna';
        extract($row_citanje_grupe);

        $stm_jezik = $jezik->read_one_jezik($fk_jezik, 'jezik');
        $row_jezik = $stm_jezik->fetch(PDO::FETCH_ASSOC);
        $fk_jezik_ime = $row_jezik['ime'];

        $stm_nivo = $nivo_znanja->read_one_nivo($nivo, 'nivo_znanja');
        $row_nivo_znanja = $stm_nivo->fetch(PDO::FETCH_ASSOC);
        $nivo_ime = $row_nivo_znanja['ime'];

        $vrsta_zaduzenja = '';
        $popust_djaka = 0;

        if ($fk_nacin_zaduzivanja == 1 || $fk_nacin_zaduzivanja == 2) {
            $vrsta_zaduzenja = "Po kursu";
            $smtp = $zaduzenje->read_all_zaduzenja_for_student_group($idd, $row_category_grupe['fk_grupa']);
            while ($row_zaduzenje = $smtp->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $kartica[$i]["id"] = $row_zaduzenje["id"];
                $kartica[$i]["fk_grupa"] = $row_zaduzenje["fk_grupa"];
                $kartica[$i]["iznos"] = $row_zaduzenje["iznos"];
                $kartica[$i]["zaduzio"] = $row_zaduzenje["zaduzio"];
                $kartica[$i]["created"] = $row_zaduzenje["valuta"] ?? null;
                $kartica[$i]["vrsta"] = "zaduzenje po kursu";
                $kartica[$i]["komentar"] = $row_zaduzenje["komentar"];
            }
        }

        if ($fk_nacin_zaduzivanja == 2) {
            $vrsta_zaduzenja = "Po času";
            $popust_djaka = floatval($row_category_grupe['popust'] ?? 0);
            $smtp_zaduzenje = $evidencija->read_all_all_prisustvo_djak_kartica($idd, $row_category_grupe['fk_grupa']);
            while ($row_zaduzenje = $smtp_zaduzenje->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $kartica[$i]["id"] = $row_zaduzenje["id"];
                $kartica[$i]["fk_grupa"] = $row_zaduzenje["fk_grupa"];

                if (!empty($fiksna_cena_za_djake)) {
                    $bazna_cena = (float)$fiksna_cena_za_djake;
                    $kartica[$i]["popust"] = 0;
                } else {
                    $bazna_cena = $cenovnik->odredi_zaduzenje($row_zaduzenje)["iznos"];
                    if ($popust_djaka > 0) {
                        $bazna_cena = round($bazna_cena * (1 - $popust_djaka / 100), 2);
                    }
                    $kartica[$i]["popust"] = $popust_djaka;
                }

                if ($row_zaduzenje["velicina"] == 1 || $row_zaduzenje["velicina"] == 2) {
                    $kartica[$i]["iznos"] = ($row_zaduzenje["prisutan"] == 1 || $row_zaduzenje["opravdao_otsustvo"] == 0)
                        ? $bazna_cena : 0;
                } else {
                    if ($row_zaduzenje["vrtic"] == 1) {
                        $kartica[$i]["iznos"] = ($row_zaduzenje["prisutan"] == 1) ? $bazna_cena : 0;
                    } else {
                        $kartica[$i]["iznos"] = $bazna_cena;
                    }
                }

                $kartica[$i]["created"] = substr($row_zaduzenje["start"], 0, 10);
                $kartica[$i]["vrsta"] = "zaduzenje po casu";
                $kartica[$i]["komentar"] = $row_zaduzenje["komentar"];
            }
        }

        $created_prikaz = vreme_u_nase_vreme($row_category_grupe['created']);
        $trajanje = ($row_category_grupe['status'] == true)
            ? "od: " . $created_prikaz
            : $created_prikaz . " - " . date('d.m.Y H:m', strtotime($row_category_grupe['deleted']));

        $grupe_info[] = [
            'jezik' => $fk_jezik_ime,
            'nivo' => $nivo_ime,
            'alias' => $alias,
            'vrsta_zaduzenja' => $vrsta_zaduzenja,
            'status_grupe' => $status_grupe,
            'popust' => ($fk_nacin_zaduzivanja == 2 && $popust_djaka > 0) ? $popust_djaka : 0,
            'trajanje' => $trajanje,
        ];
    }

    $ssdj = $uplata->read_all_uplate_students1($idd);
    while ($row_uplata = $ssdj->fetch(PDO::FETCH_ASSOC)) {
        $i++;
        $kartica[$i]['id'] = $row_uplata['id'];
        $kartica[$i]["datum_generisanja"] = $row_uplata["datum_generisanja"];
        $kartica[$i]["iznos"] = $row_uplata["iznos"];
        $kartica[$i]["fk_grupa"] = $row_uplata["fk_grupa"];
        $kartica[$i]["created"] = $row_uplata["datum_generisanja"];
        $kartica[$i]["vrsta"] = "uplata";
        $kartica[$i]["racun"] = $row_uplata["racun"];
        $kartica[$i]["broj_priznanice"] = $row_uplata["broj_priznanice"];
        $kartica[$i]["komentar"] = $row_uplata["komentar"];
    }

    return ['kartica' => $kartica, 'grupe_info' => $grupe_info];
}
