<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2017 Power Kernel
 */

namespace common\actions;
use common\components\FlickrPhoto;
use Yii;


/**
 * Class FlickrDeleteAction
 * @package common\actions
 */
class FlickrDeleteAction extends FlickrAction
{
    /**
     * run action
     * @param $id
     */
    public function run($id){
        /* @var FlickrPhoto $client */
        $client=$this->getFlickr();
        if($client){
            $params=[
                'method'=>'flickr.photos.delete',
                'photo_id'=>$id
            ];
            $client->api('','GET', $params);
        }

        /* remove session */
        $photos=Yii::$app->session['flickr'];
        if(($key = array_search($id, $photos)) !== false) {
            unset($photos[$key]);
        }
        Yii::$app->session->set('flickr', $photos);
    }


}