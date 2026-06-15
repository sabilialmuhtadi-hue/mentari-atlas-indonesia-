<?php
$zip = new ZipArchive;
if ($zip->open('NamaLengkap_CV_Academy.docx') === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    $xml = preg_replace('/<[^>]*>/', ' ', $xml);
    echo trim(preg_replace('/\s+/', ' ', $xml));
} else {
    echo 'Failed to open zip';
}
