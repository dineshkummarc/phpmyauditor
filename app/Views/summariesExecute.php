<?php
# Copyright © 2023 FirstWave. All Rights Reserved.
# SPDX-License-Identifier: AGPL-3.0-or-later
include 'shared/collection_functions.php';
?>
        <main class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <?= collection_card_header($meta->collection, $meta->icon, $user, $meta->name) ?>
                </div>
                <div class="card-body">
                    <table class="table <?= $GLOBALS['table'] ?> table-striped table-hover dataTable">
                        <thead>
                            <tr>
                                <th class="text-center"><?= __('Details') ?></th>
                                <th><?= __('Name') ?></th>
                                <th class="text-center"><?= __('Count') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data)) {
                                foreach ($data as $item) {
                                    if (empty($item->attributes->name)) {
                                        $item->attributes->name = '-';
                                    }
                                    echo "<tr>
                                        <td class=\"text-center\"><a class=\"btn btn-sm btn-primary\" href=\"" . $item->attributes->link . "\"><span class=\"fa fa-eye\" aria-hidden=\"true\"></span></a></td>\n
                                        <td>" . ucwords($item->attributes->name) . "</td>
                                        <td class=\"text-center\">" . $item->attributes->count . "</td>
                                    </tr>\n";
                                }
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

<script {csp-script-nonce}>
window.onload = function () {
    $(document).ready(function() {
        $("#button_create").remove();
        $("#button_import_csv").remove();
        $("#button_import_json").remove();
        $("#button_default_items").remove();
        $("#button_export_json").attr("href", "<?= url_to('summariesExecute', $meta->id) ?>?format=json");
        $("#button_export_csv").attr("href", "<?= url_to('summariesExecute', $meta->id) ?>?format=csv");
    });
}
</script>