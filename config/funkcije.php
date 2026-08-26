<?php
function vreme_u_nase_vreme($datetime){
    $samo_date = substr ($datetime,0,10);
    $samo_vreme = substr ($datetime, -8);
    $tmp77 = explode ("-", $samo_date);
    $samo_date = $tmp77[2] . "." . $tmp77[1] . "." . $tmp77[0];
    $ukupno = $samo_date." ".$samo_vreme;
    return $ukupno;

}

function datum_u_nas_datum($date){
    $date = date("d.m.Y", strtotime($date));
    // $tmp77 = explode ("-", $date);
    // $date = $tmp77[2] . "." . $tmp77[1] . "." . $tmp77[0];
    // var_dump($tmp77);
    return $date;

}

/**
 * Pretvara "obican" link sa poznatih video platformi u link pogodan za <iframe src="">.
 *
 * Npr. youtube.com/watch?v=XXXX -> youtube.com/embed/XXXX (watch link odbija iframe).
 * Ne radi nikakvu proveru/whitelist domena - ako link ne prepoznamo (Wistia, Rumble
 * embed link, ili nepoznata platforma), vracamo ga nepromenjenog.
 */
function video_embed_url($url) {
    $url = trim((string)$url);

    if ($url === '') {
        return '';
    }

    // YouTube: youtu.be/ID | youtube.com/watch?v=ID | youtube.com/shorts/ID
    if (preg_match('#youtu\.be/([A-Za-z0-9_-]+)#', $url, $m)
        || preg_match('#youtube\.com/watch\?.*v=([A-Za-z0-9_-]+)#', $url, $m)
        || preg_match('#youtube\.com/shorts/([A-Za-z0-9_-]+)#', $url, $m)
    ) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    // Vimeo: vimeo.com/123456789
    if (preg_match('#vimeo\.com/(?:.*/)?(\d+)#', $url, $m)) {
        return 'https://player.vimeo.com/video/' . $m[1];
    }

    // Dailymotion: dailymotion.com/video/ID | dai.ly/ID
    if (preg_match('#dailymotion\.com/video/([A-Za-z0-9]+)#', $url, $m)
        || preg_match('#dai\.ly/([A-Za-z0-9]+)#', $url, $m)
    ) {
        return 'https://www.dailymotion.com/embed/video/' . $m[1];
    }

    // Loom: loom.com/share/ID -> loom.com/embed/ID
    if (preg_match('#loom\.com/share/([A-Za-z0-9]+)#', $url, $m)) {
        return 'https://www.loom.com/embed/' . $m[1];
    }

    // Rumble: rumble.com/vXXXXXX-naslov.html -> rumble.com/embed/vXXXXXX/
    if (preg_match('#rumble\.com/(v[A-Za-z0-9]+)-#', $url, $m)) {
        return 'https://rumble.com/embed/' . $m[1] . '/';
    }

    // Wistia i ostalo - korisnik obicno vec dobije "embed" link, koristi se kao jeste
    return $url;
}

function imageResize($imageSrc,$imageWidth,$imageHeight) {
    $odnos = $imageWidth/$imageHeight;
   
   // $newImageWidth =200;
    $newImageHeight =40;
    $newImageWidth = 40*$odnos;

    $newImageLayer=imagecreatetruecolor($newImageWidth,$newImageHeight);
    imagecopyresampled($newImageLayer,$imageSrc,0,0,0,0,$newImageWidth,$newImageHeight,$imageWidth,$imageHeight);

    return $newImageLayer;
}


?>