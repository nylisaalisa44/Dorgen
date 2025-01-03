<?php

/** @var string $name_domen_bez_zona */
/** @var string $href_lang */
/** @var string $anchor_key */
/** @var array $anchors */
/** @var array $anchors_hidden */
/** @var integer $num_html_links */
/** @var array $domains */
/** @var integer $use_subs_white */
/** @var integer $max_snip */
/** @var integer $min_snip */
/** @var integer $num_links */
/** @var integer $min_snippets_links_white */
/** @var integer $max_snippets_links_white */
/** @var integer $project_type */
/** @var string $key */
/** @var string $domain_format */
/** @var array $domains_dor_hidden_link */
/** @var array $domains_map */
/** @var array $farm_domains */

/** @var string|array $snippets */

use app\components\Helpers;

$snippets = explode('|||', $snippets);

if (count($snippets) <= 1) {
    $snippets = explode('. ', $snippets[0]);
}

shuffle($snippets);

$random_number = rand(1, 100000);
$num_snippets = rand($min_snip, $max_snip);
$host = "https://$_SERVER[HTTP_HOST]";
$canonical = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";


$num_links_to_insert = rand($min_snippets_links_white, $max_snippets_links_white);
$max_positions = range(0, $num_snippets - 1);
$num_links_to_insert = min($num_links_to_insert, count($max_positions));

// Генерация случайных позиций
$link_positions = array_rand($max_positions, $num_links_to_insert);
$rand = rand(3, 5);
?>


<!DOCTYPE html>
<html lang="<?= $href_lang; ?>">
<head>
    <title><?= ucfirst($key) . ' ' . ucfirst($name_domen_bez_zona); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name='robots' content="noarchive, max-image-preview:large, max-snippet:-1, max-video-preview:-1"/>
    <meta name="googlebot" content="index"/>
    <meta name="googlebot" content="indexifembedded"/>
    <meta name="Language" content="<?php echo $href_lang; ?>"/>
    <link rel="self" type="application/rss+xml" href="<?php echo $host; ?>/rss">
    <meta property="og:image" content="https://picsum.photos/2800/2400?random=<?php echo $random_number; ?>"/>
    <meta property="og:site_name" content="<?php echo $key; ?>"/>
    <link rel="canonical" href="<?php echo $canonical; ?>">
    <style>
        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 20px;
            border: 2px solid #ccc;
        }
    </style>
    <script type="application/ld+json">
        {
                        "@context": "https:\/\/schema.org\/",
                        "@type": "CreativeWorkSeries",
                        "name": "",
                        "description": "<?php echo $key; ?>",
                        "image": {
                            "@type": "ImageObject",
                            "url": "https://picsum.photos/2800/2400?random=<?php echo $random_number; ?>",
                            "width": null,
                            "height": null
                        }
        }
    </script>
</head>
<body>

<h1>
    <a href="<?php echo $canonical; ?>"><?php echo ucfirst($key); ?></a>
</h1>


<ul>
    <?php $num_links = min($num_links, count($anchors));
    for ($i = 0; $i < $num_links; $i++) {
        $domain = $domains[array_rand($domains)];
        $link = Helpers::formatRegexUrl($domain_format, $domain);
        ?>
        <li>
            <a href="<?php echo $link; ?>"><?php echo $anchors[$i]; ?></a>
        </li>
    <?php } ?>
</ul>

<div class="content">
    <?php for ($i = 0; $i < $num_snippets; $i++) {
        // Генерация случайного сниппета
        $snippet = ucfirst($snippets[array_rand($snippets)]);

        // Если текущая позиция должна содержать ссылку
        if (in_array($i, $link_positions)) {
            $text = array_slice(explode(' ', $snippet), 0, 3);
            $imlode_string = implode('-', $text);
            $link = Helpers::formatRegexUrl($domain_format, $domains[array_rand($domains)]);

            echo '<a href="' . $link . '" title="' . htmlspecialchars($text[0]) . '">' . htmlspecialchars(implode(' ', $text)) . '</a> ';
        } else {
            // Без ссылки, просто выводим текст
            echo $snippet . ". ";
        }
    }
    ?>
</div>

</body>
</html>
