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
class TMSOptions
{
    //使用全局密钥
    const GLOBAL_KEY = 0;
    //使用自定义密钥
    const CUSTOM_KEY = 1;
    //检测发帖
    const EXAMINE_POST = 1;
    //检测回帖
    const EXAMINE_REPLY = 1;
    //
    const DEFAULT_INTERCEPT_TYPE = array(
        'Polity',
        'Porn',
        'Illegal',
        'Abuse',
        'Ad',
        'Terror',
        'Custom'
    );

    private $commonOptions;
    private $secretID;
    private $secretKey;
    private $customKey;
    private $examinePost;
    private $examineReply;
    private $interceptType;
    public function __construct($customKey = self::GLOBAL_KEY, $secretID = '', $secretKey = '',
                                $examinePost=self::EXAMINE_POST,$examineReply=self::EXAMINE_REPLY,
                                $interceptType=self::DEFAULT_INTERCEPT_TYPE)
    {
        $this->customKey = intval($customKey);
        $this->secretID = $secretID;
        $this->secretKey = $secretKey;
        $this->examinePost = intval($examinePost);
        $this->examineReply = intval($examineReply);
        $this->interceptType = $interceptType;
        global $_G;
        if (isset($_G['setting']['tencentcloud_center'])) {
            $this->commonOptions = unserialize($_G['setting']['tencentcloud_center']);
        }
    }
    /**
     * 获取全局的配置项
     */
    public function getCommonOptions()
    {
        return $this->commonOptions;
    }
    public function setSecretID($secretID)
    {
        if ( empty($secretID) && $this->customKey !== self::GLOBAL_KEY) {
            throw new \Exception('secretID'.lang('plugin/tencentcloud_tms','param_error'));
        }
        $this->secretID = $secretID;
    }
    public function setSecretKey($secretKey)
    {
        if ( empty($secretKey) && $this->customKey !== self::GLOBAL_KEY ) {
            throw new \Exception('secretKey'.lang('plugin/tencentcloud_tms','param_error'));
        }
        $this->secretKey = $secretKey;
    }
    public function setCustomKey($customKey)
    {
        if ( !in_array($customKey, array(self::GLOBAL_KEY, self::CUSTOM_KEY)) ) {
            throw new \Exception(lang('plugin/tencentcloud_tms','custom_error'));
        }
        $this->customKey = intval($customKey);
    }

    public function setExaminePost($examinePost)
    {
        $this->examinePost = intval($examinePost);
    }

    public function setExamineReply($examineReply)
    {
        $this->examineReply = intval($examineReply);
    }

    public function setInterceptType($interceptType)
    {
        if ( !is_array($interceptType) ) {
            throw new \Exception(lang('plugin/tencentcloud_tms','custom_error'));
        }
        $this->interceptType = $interceptType;
    }

    public function getInterceptType()
    {
        return $this->interceptType;
    }

    public function getSecretID()
    {
        if ( $this->customKey === self::GLOBAL_KEY && isset($this->commonOptions['secretId']) ) {
            $this->secretID = $this->commonOptions['secretId'] ?: '';
        }
        return $this->secretID;
    }

    public function getSecretKey()
    {
        if ( $this->customKey === self::GLOBAL_KEY && isset($this->commonOptions['secretKey']) ) {
            $this->secretKey = $this->commonOptions['secretKey'] ?: '';
        }
        return $this->secretKey;
    }
    public function getCustomKey()
    {
        return $this->customKey;
    }
    public function getExaminePost()
    {
        return $this->examinePost;
    }
    public function getExamineReply()
    {
        return $this->examineReply;
    }

    public function toArray()
    {
        return array(
            'customKey'=>$this->customKey,
            'secretId'=>$this->secretID,
            'secretKey'=>$this->secretKey,
            'examinePost'=>$this->examinePost,
            'examineReply'=>$this->examineReply,
            'interceptType'=>$this->interceptType,
        );
    }
}
