<?php
# Copyright © 2023 FirstWave. All Rights Reserved.
# SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

$this->networksModel = new \App\Models\NetworksModel;
$this->devicesModel = new \App\Models\DevicesModel();
$this->discoveryLogModel = new \App\Models\DiscoveryLogModel();
$this->componentsModel = new \App\Models\ComponentsModel();

helper('components');
helper('device');
helper('network');
helper('macaddress');
helper('utility');

$log = new \stdClass();
$log->discovery_id = null;
$log->device_id = null;
$log->timestamp = null;
$log->severity = 7;
$log->pid = getmypid();
$log->file = 'Input';
$log->function = 'devices';
$log->message = 'Audit result submitted';
$log->command = '';
$log->command_status = 'notice';
$log->display = 'y';
$initial_log_id = $this->discoveryLogModel->create($log);

$id = deviceMatch($device->system);
if (empty($id) && !empty($device->system->id)) {
    $id = intval($device->system->id);
}
if (!empty($id) && !empty($device->system->id) && intval($id) !== intval($device->system->id)) {
    // We delete this original system as likely with limited data (from
    // nmap and/or snmp) we couldn't match an existing system
    // Now we have an actual audit result with plenty of data
    // we have found a match and it's not the original
    $this->devicesModel->delete($device->system->id);
    $log_message('info', 'Device ID provided differs from Device ID found for ' . $device->system->hostname);
}
$device->system->id = intval($id);

if (empty($device->system->last_seen)) {
    $device->system->last_seen = $this->config->timestamp;
}

if (empty($device->system->last_seen_by)) {
    $device->system->last_seen_by = 'audit';
}

if (!empty($device->system->os_installation_date)) {
    $device->system->os_installation_date = date("Y-m-d", strtotime($device->system->os_installation_date));
}

if (empty($id)) {
    // insert a new system
    $device->system->first_seen = $device->system->last_seen;
    $device->system->id = $this->devicesModel->create($device->system);
    $log->command_status = 'fail';
    if (!empty($device->system->id)) {
        $log->command_status = 'success';
        $log->device_id = $device->system->id;
    }
    // log_message('info', 'CREATE entry for ' . $device->system->hostname . ', ID ' . $device->system->id);
    $log->ip = @ip_address_from_db($device->system->ip);
    $log->message = 'CREATE entry for ' . @$device->system->hostname . ', ID ' . $device->system->id;
    $this->discoveryLogModel->create($log);
    // In the case where we inserted a new device, m_device::match will add a log entry, but have no
    // associated device_id. Update this one row.
    $sql = 'UPDATE `discovery_log` SET device_id = ? WHERE device_id IS NULL AND pid = ? AND ip = ?';
    $query = $db->query($sql, [$device->system->id, $log->pid, @ip_address_from_db($device->system->ip)]);
} else {
    // update an existing system
    // log_message('info', 'UPDATE entry for ' . $device->system->hostname . ', ID ' . $device->system->id);
    $log->message = 'UPDATE entry for ' . @$device->system->hostname . ', ID ' . $device->system->id;
    $log->device_id = $device->system->id;
    $log->ip = @ip_address_from_db($device->system->ip);

    $test = $this->devicesModel->update($device->system->id, $device->system);
    $log->command_status = 'fail';
    if ($test) {
        $log->command_status = 'success';
    }
    $this->discoveryLogModel->create($log);

    $db_device = $this->devicesModel->read($device->system->id);
    $device->system->first_seen = $db_device[0]->attributes->first_seen;
    $device->system->last_seen = $db_device[0]->attributes->last_seen;
    $device->system->last_seen_by = $db_device[0]->attributes->last_seen_by;
}
$log = new \stdClass();
$log->id = $initial_log_id;
$log->device_id = $device->system->id;
$log->ip = (!empty($device->system->ip)) ? ip_address_from_db($device->system->ip) : '';
$this->discoveryLogModel->update($initial_log_id, $log);

$audit_ip = '127.000.000.001';
if (!empty($_SERVER['REMOTE_ADDR'])) {
    $audit_ip = ip_address_to_db($_SERVER['REMOTE_ADDR']);
}
if ($audit_ip === '::1') {
    $audit_ip = '127.000.000.001';
}

// We now have a devices.id - either supplied, found or created.
if (!empty($device->system->discovery_id)) {
    // we have a discovery_id, insert our $log_message's and delete anything where log.pid != our pid
    $sql = "DELETE FROM `discovery_log` WHERE device_id = ? AND `command` = 'process audit' AND pid != ?";
    $query = $db->query($sql, [intval($device->system->id), intval(getmypid())]);

    // And update any existing discovery logs
    $sql = "UPDATE discovery_log SET device_id = ? WHERE device_id is NULL and discovery_id = ? and ip = ?";
    $query = $db->query($sql, [$device->system->id, $device->system->discovery_id, ip_address_from_db(@$device->system->ip)]);
} else {
    // we were supplied an audit result, but no discovery_id
    // delete all dicovery logs where device_id = our ID and log.pid != our pid
    $sql = "DELETE FROM `discovery_log` WHERE `device_id` = ? AND (`pid` != ? or `timestamp` != ?) AND discovery_id IS NULL";
    $query = $db->query($sql, [intval($device->system->id), intval(getmypid()), $device->system->last_seen]);
}
$script_version = (!empty($device->system->script_version)) ? $device->system->script_version : '';
$username = (!empty($this->user->full_name)) ? $this->user->full_name : '';
$sql = "INSERT INTO audit_log VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?)";
$db->query($sql, [$device->system->id, $username, $device->system->last_seen_by, $audit_ip, '', '', $device->system->last_seen, $script_version]);

foreach ($device as $key => $value) {
    if ($key !== 'system' && $key !== 'audit_wmi_fail' && $key !== 'dns') {
        $this->componentsModel->upsert($key, $device->system, $device->{$key});
    }
}

$rulesModel = new \App\Models\RulesModel();
$rulesModel->execute(null, @intval($device->system->discovery_id), 'update', intval($device->system->id));

# Because Rules may set the last_seen_by, update it here.
$sql = "UPDATE devices SET last_seen_by = ? WHERE id = ?";
$db->query($sql, [$device->system->last_seen_by, $device->system->id]);

