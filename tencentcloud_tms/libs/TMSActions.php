<?php
/*
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
namespace TencentDiscuzTMS;
use C;
use DB;
use Exception;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Cms\V20190321\CmsClient;
use TencentCloud\Cms\V20190321\Models\TextModerationRequest;
use TencentCloud\Cms\V20190321\Models\TextModerationResponse;
defined('TENCENT_DISCUZX_TMS_PLUGIN_NAME')||define( 'TENCENT_DISCUZX_TMS_PLUGIN_NAME', 'tencentcloud_tms');
class TMSActions
{
    const PLUGIN_TYPE = 'tms';
    const CODE_SUCCESS = 0;
    const CODE_EXCEPTION = 10000;



    /**
     * post参数过滤
     * @param $key
     * @param string $default
     * @return string|void
     */
    public function filterPostParam($key, $default = '')
    {
        return isset($_POST[$key]) ? dhtmlspecialchars($_POST[$key]) : $default;
    }
    /**
     * get参数过滤
     * @param $key
     * @param string $default
     * @return string|void
     */
    public function filterGetParam($key, $default = '')
    {
        return isset($_GET[$key]) ? dhtmlspecialchars($_GET[$key]) : $default;
    }


    /**
     * 文本检测
     * @param $text
     * @return bool
     * @throws Exception
     */
    public function examineContent($text)
    {
        if (empty($text)) {
            return true;
        }
        $TMSOptions = self::getTMSOptionsObject();
        //发帖和回帖检测都未开启
        if ($TMSOptions->getExaminePost() !== TMSOptions::EXAMINE_POST
            && $TMSOptions->getExamineReply() !== TMSOptions::EXAMINE_REPLY ) {
            return true;
        }
        $response = $this->textModeration($TMSOptions, $text);
        //检测接口异常不影响用户发帖回帖
        if ( !($response instanceof TextModerationResponse) ) {
            return true;
        }
        $lang = lang('plugin/tencentcloud_tms');
        if ( $response->getData()->EvilFlag !== 0 || $response->getData()->EvilType !== 100 ) {
            $msg = !empty($response->getData()->Keywords[0])?
                $lang['include_keyword'].'：【'.$response->getData()->Keywords[0].'】'.$lang['please_delete']
                : $lang['include'].$lang['evil_label_desc'][$response->getData()->EvilType].$lang['please_delete'];
            throw new \Exception($msg);
        }
        return true;
    }

    /**
     * 调用腾讯云文本检测接口
     * @param TMSOptions $TMSOptions
     * @param $text
     * @return Exception|TextModerationResponse|TencentCloudSDKException
     * @throws Exception
     */
    private function textModeration($TMSOptions, $text)
    {
        try {
            $cred = new Credential($TMSOptions->getSecretID(), $TMSOptions->getSecretKey());
            $clientProfile = new ClientProfile();
            $client = new CmsClient($cred, "ap-shanghai", $clientProfile);
            $req = new TextModerationRequest();
            $params['Content'] = base64_encode($text);
            $req->fromJsonString(\GuzzleHttp\json_encode($params, JSON_UNESCAPED_UNICODE));
            return $client->TextModeration($req);
        } catch (TencentCloudSDKException $e) {
            return $e;
        }
    }

    /**
     * 获取配置对象
     * @return TMSOptions
     * @throws Exception
     */
    public static function getTMSOptionsObject()
    {
        global $_G;
        $TMSOptions = new TMSOptions();
        $options = $_G['setting'][TENCENT_DISCUZX_TMS_PLUGIN_NAME];
        if (empty($options)) {
            $options = C::t('common_setting')->fetch(TENCENT_DISCUZX_TMS_PLUGIN_NAME);
        }
        if (empty($options)) {
            return $TMSOptions;
        }
        $options = unserialize($options);
        $TMSOptions->setCustomKey($options['customKey']);
        $TMSOptions->setSecretID($options['secretId']);
        $TMSOptions->setSecretKey($options['secretKey']);
        $TMSOptions->setExaminePost($options['examinePost']);
        $TMSOptions->setExamineReply($options['examineReply']);
        return $TMSOptions;
    }

    public static function uploadDzxStatisticsData($action)
    {
        try {
            $file = DISCUZ_ROOT . './source/plugin/tencentcloud_center/lib/tencentcloud_helper.class.php';
            if (!is_file($file)) {
                return;
            }
            require_once $file;
            $data['action'] = $action;
            $data['plugin_type'] = self::PLUGIN_TYPE;
            $data['data']['site_url'] = \TencentCloudHelper::siteUrl();
            $data['data']['site_app'] = \TencentCloudHelper::getDiscuzSiteApp();
            $data['data']['site_id'] = \TencentCloudHelper::getDiscuzSiteID();
            $options = self::getTMSOptionsObject();
            $data['data']['uin'] = \TencentCloudHelper::getUserUinBySecret(
                $options->getSecretID(),
                $options->getSecretKey()
            );
            $data['data']['cust_sec_on'] = $options->getCustomKey() === $options::CUSTOM_KEY ? 1 : 2;
            \TencentCloudHelper::sendUserExperienceInfo($data);
        } catch (\Exception $exception){
            return;
        }
    }
}
