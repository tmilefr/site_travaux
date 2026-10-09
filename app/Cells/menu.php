<?php
/**
 * Balisage du menu (voir MenuCell).
 *
 * @var string $type    li | link | dropdown
 * @var string $icon    classe Open Iconic du bouton (dropdown)
 * @var array  $entries entrées déjà filtrées par les droits
 * @var bool   $visible faux : rien à afficher
 */
if (! $visible) {
    return;
}
?>
<?php if ($type === 'li'): ?>
	<?php foreach ($entries as $entry): ?>
		<li class="<?= esc($entry['color']) ?>">
			<?php if ($entry['href'] !== null): ?>
				<a class="dropdown-item" href="<?= esc($entry['href']) ?>"><?= $entry['label'] ?></a>
			<?php endif ?>
			<?php if ($entry['sub']): ?>
				<ul class="sub-menu">
					<?php foreach ($entry['sub'] as $sub): ?>
						<?php if (isset($sub['divider'])): ?>
							<li><div class="dropdown-divider"></div></li>
						<?php else: ?>
							<li><a class="dropdown-item" href="<?= esc($sub['href']) ?>"><?= $sub['label'] ?></a></li>
						<?php endif ?>
					<?php endforeach ?>
				</ul>
			<?php endif ?>
		</li>
	<?php endforeach ?>
<?php elseif ($type === 'link'): ?>
	<nav class="navbar navbar-dark bg-dark">
		<ul class="navbar-nav mr-auto">
			<?php foreach ($entries as $entry): ?>
				<?php if (isset($entry['divider'])): ?>
					<div class="dropdown-divider"></div>
				<?php else: ?>
					<li class="nav-item">
						<a class="nav-link" href="<?= esc($entry['href']) ?>">
							<div class="d-flex w-100 justify-content-start align-items-center">
								<span class="oi <?= esc($entry['icon']) ?>"></span>
								<span class="collapse-text menu-collapsed"><?= $entry['label'] ?></span>
							</div>
						</a>
					</li>
				<?php endif ?>
			<?php endforeach ?>
		</ul>
	</nav>
<?php elseif ($type === 'dropdown'): ?>
	<li class="nav-item dropdown">
		<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><span class="oi <?= esc($icon) ?>"></span></a>
		<div class="dropdown-menu">
			<?php foreach ($entries as $entry): ?>
				<?php if (isset($entry['divider'])): ?>
					<div class="dropdown-divider"></div>
				<?php else: ?>
					<a class="dropdown-item" href="<?= esc($entry['href']) ?>"><?= $entry['label'] ?></a>
				<?php endif ?>
			<?php endforeach ?>
		</div>
	</li>
<?php endif ?>
