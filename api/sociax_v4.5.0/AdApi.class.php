<?php

defined('SITE_PATH') || exit('Forbidden');

class AdApi extends Api
{
    public function getAppIndexAd(){
        return D('AdSpace')->getAdSpace(11);
    }
    

}