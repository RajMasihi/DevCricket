most ///////////////////

<?php
$oldUrl = 'https://static.cricbuzz.com/a/img/v1/0x0/i1/c776231/vanuatu-women.jpg';

$newUrl = str_replace('static.cricbuzz.com', 'static.your_sitename', $oldUrl);

echo '<img class="img" src="'.$newUrl.'" alt="Vanuatu Women" />';
?>


/////////////////////////
