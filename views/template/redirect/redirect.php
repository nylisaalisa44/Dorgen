<?php
$rand = rand(0, 1);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Redirecting...</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <?php if ($rand == 0) { ?>
        <meta http-equiv='refresh' content='0; url=<?= $url ?>'>
    <?php } ?>
    <meta name="description" content="Please wait..."/>
</head>
<?php if ($rand == 1) { ?>
    <script type='text/javascript'>
        location.replace('<?= $url ?>');
    </script>
<?php } ?>
<body>
</body>
</html>

