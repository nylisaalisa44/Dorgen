<?php
/** @var array $data */
/** @var string $name_domen_bez_zona */
/** @var string $href_lang */
/** @var string $anchor_key */
/** @var array $anchors */
/** @var array $domains */
/** @var integer $use_subs */
/** @var integer $max_snip */
/** @var integer $min_snip */
/** @var integer $min_links */
/** @var integer $max_links */

/** @var string $key */

/** @var string|array $snippets */

use app\components\Helpers;

$num_snippets = rand($min_snip, $max_snip);
$num_links = rand($min_links, $max_links);

$snippets = explode('|||', $snippets);

if (count($snippets) <= 1) {
    $snippets = explode('. ', $snippets[0]);
}

shuffle($snippets);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $key; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name='robots' content="noarchive, max-image-preview:large, max-snippet:-1, max-video-preview:-1"/>
</head>
<body>

<ul>
    <?php for ($i = 0; $i < $num_links; $i++) { ?>
        <?php
        $domain = $domains[array_rand($domains)];
        if ($subs) {
            $url = "https://{w6}." . $domain . '/{w6}';
        } else {
            $url = "https://" . $domain . '/{w6}';
        }

        $link = Helpers::formatRegexUrl($url);
        ?>
        <li><a href="<?= $link ?>"><?= $link; ?></a></li>
    <?php } ?>
</ul>
<?php for ($i = 0; $i < $num_snippets; $i++) { ?>
    <p><?= $snippets[array_rand($snippets)]; ?></p>
<?php } ?>

</html>
