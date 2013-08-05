<section class="title">
    <h4>Deploy Log</h4>
    <?php if (group_has_role('deploy', 'clear_log')) { ?>
    <a href="admin/deploy/clear_log" class="button alignright">Clear Log</a>
    <?php } ?>
    <?php if (group_has_role('deploy', 'deploy')) { ?>
    <form action="deploy/<?= $hash_key ?>" method="POST">
        <input type="hidden" name="hash" value="<?= $hash_key ?>">
        <input type="hidden" name="admin" value="admin">
        <button type="submit" class="button">Full deploy</button>
    </form>
    <?php } ?>
    <?php if (group_has_role('deploy', 'migrate')) { ?>
    <form action="deploy/migrate/<?= $hash_key ?>" method="POST">
        <input type="hidden" name="hash" value="<?= $hash_key ?>">
        <input type="hidden" name="admin" value="admin">
        <button type="submit" class="button">Run Migrations</button>
    </form>
    <?php } ?>
</section>

<section class="item">
    <div class="content">
        <?php if($logs) { ?>
            <table cellspacing="0">
                <?php foreach ($logs as $log) { ?>
                    <tr><td><?= $log->msg ?></td><td><?= $log->date ?></td></tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            The log is currently empty
        <?php } ?>
    </div>
</section>