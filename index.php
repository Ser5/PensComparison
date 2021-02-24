<?require_once __DIR__.'/src/php/lib/vendor/autoload.php';
use \Symfony\Component\Yaml\Yaml;

$urlsData  = Yaml::parseFile(__DIR__.'/src/yaml/urls.yaml');
$tableData = Yaml::parseFile(__DIR__.'/src/yaml/ru.yaml');
//dump($tableData); exit();

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
	<title>Сравнение типов ручек</title>
	<link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<table class="float">
	<?foreach ($tableData as $tr) {?>
		<tr>
			<?foreach ($tr as $cell) {?>
				<?foreach ($cell as $tag => $data) {?>
					<?="<$tag>$data</$tag>"?>
				<?}?>
			<?}?>
		</tr>
		<?break?>
	<?}?>
</table>

<table class="main">
	<?foreach ($tableData as $tr) {?>
		<tr>
			<?foreach ($tr as $cell) {?>
				<?foreach ($cell as $tag => $data) {
					if (is_string($data)) {
						$text     = $data;
						$colspan  = '';
						$cssClass = '';
					} else {
						$text     = $data['text'];
						$colspan  = isset($data['span']) ? "colspan='$data[span]'" : '';
						$cssClass = isset($data['mark']) ? "class='m$data[mark]'"  : '';
					}
					$text = str_replace(' —', '&nbsp;—', $text);
					if (strpos($text, '](')) {
						$text = preg_replace_callback('/\[(.+?)\]\((.+?)\)/ui', $processUrls, $text);
					}
					?>
					<?="<$tag $colspan $cssClass>$text</$tag>"?>
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
		floatingTable.style.display = 'table';
	} else {
		floatingTable.style.display = null;
	}
};
window.onscroll();
</script>

</body>
</html>
<?



if (isset($_GET['gen'])) {
	file_put_contents(__DIR__.'/index.html', ob_get_clean());
} else {
	echo ob_get_clean();
}
