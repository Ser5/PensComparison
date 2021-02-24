<?require_once __DIR__.'/src/php/lib/vendor/autoload.php';
use \Symfony\Component\Yaml\Yaml;

$urlsData  = Yaml::parseFile(__DIR__.'/src/yaml/urls.yaml');
$tableData = Yaml::parseFile(__DIR__.'/src/yaml/ru.yaml');
//dump($tableData); exit();

$typesList = [];
foreach ($tableData[0] as $cell) {
	foreach ($cell as $type) {
		$typesList[] = $type;
	}
}
//dump($typesList); exit();

$processUrls = function ($matches) use ($urlsData) {
	$r = $matches[0];
	foreach ($urlsData as $key => $url) {
		if (strpos(strtolower($matches[2]), $key) !== false) {
			$r = '<a href="'.$url.'" target="_blank">'.$matches[1].'</a>';
			break;
		}
	}
	return $r;
};


ob_start();
?><!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="Cache-control" content="no-cache">
	<title>Сравнение типов ручек</title>
	<link rel="stylesheet" href="styles/styles.css?t=<?=date('Ymd-His')?>">
</head>
<body>

<table class="float">
	<tr>
		<?foreach ($typesList as $type) {?>
			<th><?=$type?></th>
		<?}?>
	</tr>
</table>

<table class="main">
	<?foreach ($tableData as $tr) {?>
		<?$typeIx = 1?>
		<tr>
			<?foreach ($tr as $cell) {?>
				<?foreach ($cell as $tag => $data) {
					if (is_string($data)) {
						$text        = $data;
						$spanCounter = 1;
						$colspan     = '';
						$cssClass    = '';
					} else {
						$text        = $data['text'];
						$spanCounter = isset($data['span']) ? $data['span'] : 1;
						$colspan     = isset($data['span']) ? "colspan='$data[span]'" : '';
						$cssClass    = isset($data['mark']) ? "m$data[mark]"  : '';
					}
					$text = str_replace(' —', '&nbsp;—', $text);
					if (strpos($text, '](')) {
						$text = preg_replace_callback('/\[(.+?)\]\((.+?)\)/ui', $processUrls, $text);
					}
					?>
					<?="<$tag $colspan>"?>
						<?if ($tag == 'th') {?>
							<?=$text?>
						<?} else {?>
							<div class="left-headers">
								<?while ($spanCounter--) {?>
									<div class="left-headers__item"><?=$typesList[$typeIx++]?></div>
								<?}?>
							</div>
							<div class="text <?=$cssClass?>"><?=$text?></div>
						<?}?>
					<?="</$tag>"?>
				<?}?>
			<?}?>
		</tr>
	<?}?>
</table>

<script>
let floatingTable      = document.querySelector('.float');
let floatingTableCells = document.querySelectorAll('.float th');
let mainTableCells     = document.querySelectorAll('.main tr:first-child th');

window.onresize = function () {
	for (let a = 0; a < mainTableCells.length; a++) {
		floatingTableCells[a].style.width = `${mainTableCells[a].clientWidth}px`;
	}
};
window.onresize();

window.onscroll = function () {
	if (window.scrollY >= 17) {
		floatingTable.style.zIndex = 1;
	} else {
		floatingTable.style.zIndex = -1;
	}
};
window.onscroll();
</script>

</body>
</html>
<?



if (isset($_GET['gen']) or (isset($argv[1]) and $argv[1] == 'gen')) {
	file_put_contents(__DIR__.'/index.html', ob_get_clean());
} else {
	echo ob_get_clean();
}
