<?php
/**
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
if (!defined('IN_DISCUZ')){
    exit('Access Denied');
}
defined('TENCENT_DISCUZX_TMS_DIR')||define( 'TENCENT_DISCUZX_TMS_DIR', __DIR__.DIRECTORY_SEPARATOR);
defined('TENCENT_DISCUZX_TMS_PLUGIN_NAME')||define( 'TENCENT_DISCUZX_TMS_PLUGIN_NAME', 'tencentcloud_tms');
if (!is_file(TENCENT_DISCUZX_TMS_DIR.'vendor/autoload.php')) {
    exit(lang('plugin/tencentcloud_tms','require_sdk'));
}
require_once 'vendor/autoload.php';

use TencentDiscuzTMS\TMSActions;
use TencentDiscuzTMS\TMSOptions;
class mobileplugin_tencentcloud_tms
{
    public static $pluginOptions;
    public function __construct()
    {
        global $_G;
        self::$pluginOptions = unserialize($_G['setting'][TENCENT_DISCUZX_TMS_PLUGIN_NAME]);
    }

    public function common()
    {
        if ( $_GET['mod'] !== 'post' ) {
            return;
        }
        $dzxTMS = new TMSActions();
        try {
            if (empty(self::$pluginOptions)) {
                self::$pluginOptions = TMSActions::getTMSOptionsObject()->toArray();
            }
            //发帖时检测帖子的标贴和内容
            if ( $_GET['action'] === 'newthread'
                && $_GET['handlekey'] === 'postform'
                && $_GET['inajax'] == '1'
                && self::$pluginOptions['examinePost'] === TMSOptions::EXAMINE_POST
            ) {
                $dzxTMS->examineContent($dzxTMS->filterPostParam('subject'),$dzxTMS::TYPE_POST_TITLE);
                $dzxTMS->examineContent($dzxTMS->filterPostParam('message'),$dzxTMS::TYPE_POST_CONTENT);
            }
            //检测回帖的内容
            if ( $_GET['action'] === 'reply'&& self::$pluginOptions['examineReply'] === TMSOptions::EXAMINE_REPLY) {
                $dzxTMS->examineContent($dzxTMS->filterPostParam('message'),$dzxTMS::TYPE_REPLY_CONTENT);
            }
        } catch (\Exception $exception) {
            showmessage($exception->getMessage());
        }
    }

}
