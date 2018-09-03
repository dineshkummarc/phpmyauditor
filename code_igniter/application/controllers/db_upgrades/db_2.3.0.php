<?php
/**
#  Copyright 2003-2015 Opmantek Limited (www.opmantek.com)
#
#  ALL CODE MODIFICATIONS MUST BE SENT TO CODE@OPMANTEK.COM
#
#  This file is part of Open-AudIT.
#
#  Open-AudIT is free software: you can redistribute it and/or modify
#  it under the terms of the GNU Affero General Public License as published
#  by the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#
#  Open-AudIT is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU Affero General Public License for more details.
#
#  You should have received a copy of the GNU Affero General Public License
#  along with Open-AudIT (most likely in a file named LICENSE).
#  If not, see <http://www.gnu.org/licenses/>
#
#  For further information on Open-AudIT or for a license other than AGPL please see
#  www.opmantek.com or email contact@opmantek.com
#
# *****************************************************************************
*
**/

$this->log_db('Upgrade database to 2.3.0 commenced');

# Discoveries
$this->alter_table('discoveries', 'status', "ADD `status` varchar(20) NOT NULL DEFAULT '' AFTER `complete`", 'add');

$this->alter_table('discoveries', 'discovered', "ADD `discovered` varchar(20) NOT NULL DEFAULT '' AFTER `status`", 'add');

$this->alter_table('discoveries', 'last_log', "ADD `last_log` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' AFTER `discovered`", 'add');

$this->alter_table('discoveries', 'duration', "ADD `duration` time NOT NULL DEFAULT '00:00:00' AFTER `last_log`", 'add');

# set our versions
$sql = "UPDATE `configuration` SET `value` = '20180830' WHERE `name` = 'internal_version'";
$this->db->query($sql);
$this->log_db($this->db->last_query());

$sql = "UPDATE `configuration` SET `value` = '2.3.0' WHERE `name` = 'display_version'";
$this->db->query($sql);
$this->log_db($this->db->last_query());

$this->log_db("Upgrade database to 2.3.0 completed");
$this->config->config['internal_version'] = '20180830';
$this->config->config['display_version'] = '2.3.0';
