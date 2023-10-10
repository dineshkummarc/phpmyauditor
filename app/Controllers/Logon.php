<?php
# Copyright © 2023 FirstWave. All Rights Reserved.
# SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use \Config\Services;

/**
 * PHP version 7.4
 *
 * @category  Controller
 * @package   Open-AudIT\Controller
 * @author    Mark Unwin <mark.unwin@firstwave.com>
 * @copyright 2023 FirstWave
 * @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
 * @version   GIT: Open-AudIT_5.0.0
 * @link      http://www.open-audit.org
 */

/**
 * Base Object Logon
 *
 * @access   public
 * @category Object
 * @package  Open-AudIT\Controller\Logon
 * @author   Mark Unwin <mark.unwin@firstwave.com>
 * @license  http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
 * @link     http://www.open-audit.org
 */
class Logon extends Controller
{
    public function createForm()
    {
        $this->checkDefaults();
        $this->session = session();
        if (!empty($this->session->get('user_id'))) {
            return redirect()->to(site_url('orgs'));
        }
        return view('logon', ['config' => new \Config\OpenAudit()]);
    }

    public function checkDefaults()
    {
        $db = db_connect();
        $sql = "UPDATE configuration SET value = 'community' WHERE name = 'product'";
        $db->query($sql);
        $sql = "UPDATE configuration SET value = 'none' WHERE name = 'license'";
        $db->query($sql);
        if (file_exists(APPPATH . '../other/modules.json')) {
            $modules = file_get_contents(APPPATH.'../other/modules.json');
            if (!empty($modules)) {
                $modules = json_decode($modules);
                # echo "<pre>"; print_r($modules); exit;
                $configFiles = array('/usr/local/opmojo/conf/opCommon.json', '/usr/local/omk/conf/opCommon.json');
                foreach ($configFiles as $configFile) {
                    if (is_file($configFile)) {
                        $installed = file_get_contents($configFile);
                    }
                }
                if (!empty($installed)) {
                    $modules->opLicensing->installed = true;
                    $installed = json_decode($installed);
                    foreach ($installed->omkd->load_applications as $app) {
                        if (!empty($modules->{$app})) {
                            $modules->{$app}->installed = true;
                        }
                    }
                    if (file_exists($modules->NMIS->file)) {
                        $modules->NMIS->installed = true;
                    }
                    $sql = "UPDATE configuration SET value = ? WHERE name = 'modules'";
                    $db->query($sql, [json_encode($modules)]);
                } else {
                    unset($modules->opLicensing);
                }
            }
        }
    }

    public function create()
    {
        $this->session = session();
        $this->logonModel = model('App\Models\LogonModel');
        $this->config =  new \Config\OpenAudit();

        $username = (!empty($_POST['username'])) ? $_POST['username'] : '';
        if (empty($username) && ! empty($_SERVER['HTTP_USERNAME'])) {
            # The actual header should just be USERNAME
            $username = $_SERVER['HTTP_USERNAME'];
        }

        $password = @$_POST['password'];
        if (empty($password) && ! empty($_SERVER['HTTP_PASSWORD'])) {
            # The actual header should just be PASSWORD
            $password = $_SERVER['HTTP_PASSWORD'];
        }

        if (empty($username) or empty($password)) {
            # set flash need creds
            $this->session->setFlashdata('flash', '{"level":"danger", "message":"Credentials required"}');
            log_message('error', '{"level":"danger", "message":"Credentials required"}');
            return redirect()->to(site_url('logon'));
        }

        $http_accept = (!empty($_SERVER['HTTP_ACCEPT'])) ? $_SERVER['HTTP_ACCEPT'] : '';
        $format = '';
        if (strpos($http_accept, 'application/json') !== false) {
            $format = 'json';
        }
        if (strpos($http_accept, 'html') !== false) {
            $format = 'html';
        }
        if (isset($_GET['format'])) {
            $format = $_GET['format'];
        }
        if (isset($_POST['format'])) {
            $format = $_POST['format'];
        }
        if ($format == '') {
            $format = 'json';
        }

        $user = $this->logonModel->logon($username, $password);
        if ($user) {
            $this->session->set('user_id', $user->id);
            if ($format !== 'json') {
                if (!empty($_POST['url'])) {
                    header('Location: ' . $_POST['url']);
                    exit;
                }
                if ($this->config->device_count === 0) {
                    return redirect()->to(url_to('welcome'));
                } else {
                    return redirect()->to(url_to('home'));
                }
            }
            if (!empty($user->id)) {
                $user->id = intval($user->id);
            }
            if (!empty($user->org_id)) {
                $user->org_id = intval($user->org_id);
            }
            if (!empty($user->roles)) {
                $user->roles = json_decode($user->roles);
            }
            print_r(json_encode($user));
            exit;
        }
        log_message('error', json_encode($user));
        return redirect()->to(site_url('logon'));
    }

    public function delete()
    {
        $this->session = session();
        $this->session->destroy();
        return redirect()->to(site_url('logon'));
    }

    public function license()
    {
        $this->response->setContentType('application/json');
        $json = '{"license":"none","product":"free"}';
        $enterprise_binary = '';
        $binaries = array('/usr/local/opmojo/private/enterprise.pl', '/usr/local/open-audit/other/enterprise.bin', 'c:\\xampp\\open-audit\\enterprise.exe');
        foreach ($binaries as $binary) {
            if (file_exists($binary)) {
                $enterprise_binary = $binary;
            }
        }
        if (!empty($enterprise_binary)) {
            if (php_uname('s') === 'Windows NT') {
                $command = "%comspec% /c start /b " . $enterprise_binary . " --license";
                exec($command, $output);
                pclose(popen($command, 'r'));
            } else {
                $command = $enterprise_binary . " --license";
                exec($command, $output);
            }
            if (!empty($output)) {
                $json = $output[0];
            }
        }
        echo $json;
    }
}
