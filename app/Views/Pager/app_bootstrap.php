<?php

/**
 * Gabarit du pager (Bootstrap) des listes : liens .../list/page/N
 *
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>
<?php if ($pager->getPageCount() > 1) : ?>
<div class="pagging text-center">
    <nav>
        <ul class="pagination">
            <?php if ($pager->hasPrevious()) : ?>
                <li class="page-item"><a class="page-link" href="<?= $pager->getFirst() ?>"><?= lang('Pagination.pagination_first_link') ?></a></li>
            <?php endif ?>
            <?php if ($pager->hasPreviousPage()) : ?>
                <li class="page-item"><a class="page-link" href="<?= $pager->getPreviousPage() ?>"><?= lang('Pagination.pagination_prev_link') ?></a></li>
            <?php endif ?>

            <?php foreach ($pager->links() as $link) : ?>
                <?php if ($link['active']) : ?>
                    <li class="page-item active"><span class="page-link"><?= $link['title'] ?><span class="sr-only">(current)</span></span></li>
                <?php else : ?>
                    <li class="page-item"><a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a></li>
                <?php endif ?>
            <?php endforeach ?>

            <?php if ($pager->hasNextPage()) : ?>
                <li class="page-item"><a class="page-link" href="<?= $pager->getNextPage() ?>"><?= lang('Pagination.pagination_next_link') ?></a></li>
            <?php endif ?>
            <?php if ($pager->hasNext()) : ?>
                <li class="page-item"><a class="page-link" href="<?= $pager->getLast() ?>"><?= lang('Pagination.pagination_last_link') ?></a></li>
            <?php endif ?>
        </ul>
    </nav>
</div>
<?php endif ?>
