<?php
# Copyright © 2023 FirstWave. All Rights Reserved.
# SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace App\Controllers;

/**
 * PHP version 7.4
 *
 * @category  Controller
 * @package   Open-AudIT\Controller
 * @author    Mark Unwin <mark.unwin@firstwave.com>
 * @copyright 2023 FirstWave
 * @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
 * @version   GIT: Open-AudIT_5.2.2
 * @link      http://www.open-audit.org
 */

/**
 * Base Object Tasks
 *
 * @access   public
 * @category Object
 * @package  Open-AudIT\Controller\Tasks
 * @author   Mark Unwin <mark.unwin@firstwave.com>
 * @license  http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
 * @link     http://www.open-audit.org
 */
class Tasks extends BaseController
{
    /**
     * Provide a form to choose a group so we can execute a baseline
     *
     * @access public
     * @return void
     */
    public function execute($id)
    {
        return redirect()->route('tasksRead', [$id]);
    }
}
