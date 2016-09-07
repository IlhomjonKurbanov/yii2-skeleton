<?php
/**
 * @author Harry Tang <harry@modernkernel.com>
 * @link https://modernkernel.com
 * @copyright Copyright (c) 2016 Modern Kernel
 */

namespace common\components;
use common\models\Service;
use Yii;


/**
 * Class FlickrUploadAction
 * @package common\components
 */
class FlickrUploadAction extends FlickrAction
{
    /**
     * run action
     * @return string
     */
    public function run(){
        /* POST: qquuid, qqfilename, qqtotalfilesize */
        /* @var FlickrPhoto $client */
        $client=$this->getFlickr();
        if($client){
            $data=[
                'title'=>Yii::$app->request->post('qqfilename')
            ];
            $response=$client->apiUpload($data, $_FILES['qqfile']['tmp_name']);

            /* error */
            if(empty($response['photoid'])){
                return json_encode(['success' => false, 'uuid' => Yii::$app->request->post('qquuid')]);
            }
            /* success */
            else {
                $photos=Yii::$app->session['flickr'];
                if(empty($photos)){
                    $photos=[];
                }
                $photos[]=$response['photoid'];
                Yii::$app->session->set('flickr', $photos);
                $this->addPhotoToSet($response['photoid']);
                return json_encode(['success' => true, 'uuid' => Yii::$app->request->post('qquuid')]);
            }

        }

        return json_encode(['success' => false, 'uuid' => Yii::$app->request->post('qquuid')]);
    }

    /**
     * add photo to a set
     * @param $photoid
     */
    protected function addPhotoToSet($photoid){
        $flickr=Service::findOne('flickr-photo');
        if($flickr){
            $client=$this->getFlickr();
            $data=json_decode($flickr->data, true);
            if(!empty($data['photoset'])){
                $params = [
                    'method' => 'flickr.photosets.addPhoto',
                    'photoset_id'=>$data['photoset'],
                    'photo_id'=>$photoid
                ];
                $client->api('', 'POST', $params);
            }
        }
    }
}