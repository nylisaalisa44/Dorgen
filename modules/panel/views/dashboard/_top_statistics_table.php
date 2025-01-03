<?php
// _top_statistics_table.php

?>

<table class="table table-striped table-sm table-hover">
	<thead>
	<tr class="bg-secondary bg-opacity-10" style="border-bottom:3px #3d4050 solid">
		<?php if ($block !== 'topproject'): ?>
			<th scope="col">Домен</th>
		<?php endif; ?>
		<th scope="col">Проект</th>
		<th scope="col"><?= $what === 'bot' ? 'Боты' : 'Трафик' ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($data as $item): ?>
		<tr>
			<?php if ($block !== 'topproject'): ?>
				<td>
					<a target="_blank" href="https://www.google.com/search?q=site%3A<?= $item['domain'] ?>"><i class="bx bxl-google"></i></a>
					<a target="_blank" href="/panel/default/log?domain=<?= $item['domain'] ?>"><i class="bx bx-poll"></i></a>
					<i data-bs-toggle="modal" onClick="statdomain('<?= $item['domain'] ?>');" data-bs-target="#statdomain" class="cursor-hand bx bx-pulse me-1"></i>
					<?= $item['domain'] ?>
				</td>
			<?php endif; ?>
			<td>
				<i data-bs-toggle="modal" onClick="statproject('<?= $item['db'] ?>');" data-bs-target="#statdomain" class="cursor-hand bx bx-pulse me-1"></i>
				<a class="link-info" href="/theme/<?= $item['db'] ?>.html"><?= $item['db'] ?></a>
			</td>
			<td class="tduser"><?= number_format($item['total'], 0, '.', ' ') ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
