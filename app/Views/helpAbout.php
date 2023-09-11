<?php
# Copyright © 2023 FirstWave. All Rights Reserved.
# SPDX-License-Identifier: AGPL-3.0-or-later
include 'shared/collection_functions.php';
$this->db = db_connect();
if (php_uname('s') == "Windows NT") {
    exec("echo. |WMIC OS Get Caption", $output);
    if (isset($output[1])) {
        $operating_system = $output[1];
    } else {
        $operating_system = "unknown (You must run Apache as an administrator on Windows to determine this)";
    }
} else {
    $operating_system = php_uname('s');
}
?>
        <main class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h6 style="padding-top:10px;"><span class="fa-solid fa-arrows-spin oa-icon"></span><?= __('About') ?></h6>
                </div>
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-8 offset-2 text-center">
                            <h1><?= ucwords(str_replace('_', ' ', $meta->id)) ?></h1>
                            <br>


                You are running version <?= config('Openaudit')->display_version ?> of Open-AudIT.<br />
                Your Host is: <?= php_uname('n'); ?>, and it's OS is <?php echo $operating_system; ?>.<br />
                Your database platform is <?= $this->db->getPlatform()." (version ".$this->db->getVersion().")"; ?>.<br />
                Your web server is <?= getenv("SERVER_SOFTWARE") ?> .<br />
                Your PHP version is <?= phpversion(); ?> and it's current time is <?= config('Openaudit')->timestamp ?>.<br /><br />
                <?php
                if (!extension_loaded('snmp')) {
                    echo "<i>You do not have the PHP SNMP extension installed. This extension is highly recommended.</i><br />";
                }
                if (!extension_loaded('mbstring')) {
                    echo "<i>You do not have the PHP MBString extension installed. This extension is required.</i><br />";
                }
                ?>

                <br /><br />
                <p>
                    Most of Open-AudIT is Licensed with GNU AGPL.<br>
                    Written in the PHP scripting language.<br>
                    Uses the CodeIgniter PHP Framework.<br>
                </p>
                <br />
                Some other technologies Open-AudIT uses are:
                <p>
                    jQuery javascript framework<br>
                    MariaDB - our default database<br>
                    Apache - our default webserver<br>
                    xHTML W3C standards<br>
                </p>
                Please visit the homepage at <a target="_blank" href="http://www.open-audit.org" style="color: blue;">http://www.open-audit.org</a>.<br />
                Consulting, implementation and other services are available through <a target="_blank" href="https://www.firstwave.com" style="color: blue;">Firstwave</a>.<br /><br />
                We hope you find Open-AudIT as useful as we do.<br /><br /><br />

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-8 offset-2 text-center">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td style="vertical-align: middle;"><a href="http://www.fsf.org/licensing/licenses/agpl.html"><img src='<?= base_url() ?>images/logo-agpl.png' style='width:120px' alt=''/></a></td>
                                        <td style="vertical-align: middle;"><a href="http://www.php.net"><img src='<?= base_url() ?>images/logo-php.png' style='width:120px' alt=''/></a></td>
                                        <td style="vertical-align: middle;"><a href="http://www.codeigniter.com"><img src='<?= base_url() ?>images/logo-code_igniter.png' style='width:120px' alt=''/></a></td>
                                        <td style="vertical-align: middle;"><a href="http://jquery.com/"><img src='<?= base_url() ?>images/logo-jquery.png' style='width:120px' alt=''/></a></td>
                                        <td style="vertical-align: middle;"><a href="http://www.w3.org/"><img src='<?= base_url() ?>images/logo-w3c.png' style='width:120px' alt=''/></a></td>
                                        <td style="vertical-align: middle;"><a href="http://mapicons.nicolasmollet.com/"><img src='<?= base_url() ?>images/logo-miclogo-88x31.gif' style='width:120px' alt=''/></a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
