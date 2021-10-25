<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\base\Model;
use yii\db\ActiveRecord;
use app\models\Users;
use app\models\Auth;
use yii\helpers\Json;

use jambtc\oauthamazon;

class AmazonController extends Controller
{
	public function actionCheckAuthorization()
	{
		$client_id = Yii::$app->params['amazon.clientId'];
		$client_secret = Yii::$app->params['amazon.clientSecret'];

		$login = new \jambtc\oauthamazon\amazon($client_id,$client_secret);
		$auth_data = $login->checkAmazonAuthorization();
		$auth_data->oauth_provider = 'amazon';

		$user = $this->saveUserData($auth_data);

		$auth = new Auth([
			'user_id' => $user['model']->id,
			'source' => 'amazon',
			'source_id' => (string) $auth_data->user_id,
		]);

		if ($user['response'] && $auth->save()){
			Yii::$app->user->login($user['model'], Yii::$app->params['user.rememberMeDuration']);
			return $this->redirect(['site/index']);
		} else {
			Yii::$app->getSession()->setFlash('error', [
				Yii::t('app', 'Unable to save {client} account: {errors}', [
					'client' => $this->client->getTitle(),
					'errors' => json_encode($auth->getErrors()),
				]),
			]);
		}

		return $this->goHome();

  }


	private function saveUserData($auth_data)
	{
		$model = Users::find()
    	->where([
			'oauth_provider'=>$auth_data->oauth_provider,
			'oauth_uid'=>$auth_data->user_id,
		])->one();

		if (null === $model){
			$model = new Users();
			$model->username = $auth_data->email;
			$model->password = Yii::$app->security->generateRandomString();
			$model->activation_code = '0';
			$model->status_activation_code = 1;
			$model->oauth_provider = $auth_data->oauth_provider;
			$model->oauth_uid = $auth_data->user_id;
			$model->authKey = Yii::$app->security->generateRandomString();
			$model->accessToken = Yii::$app->getSecurity()->generatePasswordHash($model->getAuthKey());
			$model->email = $auth_data->email;
			$model->picture = (isset($auth_data->photo_url) ? $auth_data->photo_url : 'css/images/anonymous.png');
			$model->name = (isset($auth_data->name) ? $auth_data->name : '');
		}else{
			$model->picture = (isset($auth_data->photo_url) ? $auth_data->photo_url : 'css/images/anonymous.png');
			$model->name = (isset($auth_data->name) ? $auth_data->name : '');
		}

		$auth_data_json = Json::encode($auth_data);

		return ['response' => $model->save(), 'model' => $model];
	}
}
