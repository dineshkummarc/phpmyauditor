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
 * @version   GIT: Open-AudIT_5.0.0
 * @link      http://www.open-audit.org
 */

/**
 * Base Object Components
 *
 * @access   public
 * @category Object
 * @package  Open-AudIT\Controller\Components
 * @author   Mark Unwin <mark.unwin@firstwave.com>
 * @license  http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
 * @link     http://www.open-audit.org
 */
class Components extends BaseController
{
    public function createForm($type, $device_id)
    {
        $this->resp->included = $this->{$this->resp->meta->collection.'Model'}->includedCreateForm();
        $dictionary = $this->{$this->resp->meta->collection.'Model'}->dictionary();
        $this->resp->meta->id = $device_id;
        return view('shared/header', [
            'config' => $this->config,
            'dashboards' => filter_response($this->dashboards),
            'dictionary' => $dictionary,
            'included' => $this->resp->included,
            'meta' => filter_response($this->resp->meta),
            'queries' => filter_response($this->queriesUser),
            'roles' => filter_response($this->roles),
            'orgs' => filter_response($this->orgsUser),
            'user' => filter_response($this->user)]) .
            view($this->resp->meta->collection . ucfirst($this->resp->meta->action) . ucfirst($type));
    }

    public function delete($id, $type)
    {
        $this->resp->meta->sub_resource = $type;
        if ($this->{'componentsModel'}->delete($id)) {
            \Config\Services::session()->setFlashdata('success', 'Item deleted.');
            $this->response->setStatusCode(200);
            $temp = new \stdClass();
            $temp->type = $type;
            $this->resp->data = array();
            $this->resp->data[] = $temp;
        } else {
            $this->resp->meta->header = 500;
            $this->response->setStatusCode($this->resp->meta->header);
            if (!empty(\Config\Services::session()->getFlashdata('error'))) {
                $this->resp->errors = \Config\Services::session()->getFlashdata('error');
            } else if (!empty(\Config\Services::session()->getFlashdata('warning'))) {
                $this->resp->errors = \Config\Services::session()->getFlashdata('warning');
            } else {
                $this->resp->errors = 'Item in ' . $this->resp->meta->collection . ' not deleted.';
                \Config\Services::session()->setFlashdata('error', 'Item in ' . $this->resp->meta->collection . ' not deleted.');
            }
            log_message('error', $this->resp->errors);
        }
        output($this);
    }

    public function download($id, $type)
    {
        if ($type !== 'attachment') {
            $this->resp->meta->header = 500;
            $this->response->setStatusCode($this->resp->meta->header);
            return;
        }
        $filter = new \stdClass();
        $filter->name = 'type';
        $filter->value = $type;
        $this->resp->meta->filter[] = $filter;
        $item = $this->componentsModel->read(intval($id));
        return $this->response->download(APPPATH.'/Attachments/' . $item[0]->attributes->filename, null);
    }
}
