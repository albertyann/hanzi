<?php

namespace frontend\controllers;

use Yii;
use common\models\Hanzi;
use common\models\HanziSearch;
use common\models\HanziTask;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;
use yii\filters\VerbFilter;

/**
 * HanziController implements the CRUD actions for Hanzi model.
 */
class HanziSplitController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Hanzi models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HanziSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, true);

        $dataProvider->pagination->pageSize = Yii::$app->get('keyStorage')->get('frontend.task-per-page', null, false);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

 
    /**
     * Displays a single Hanzi model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Hanzi model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Hanzi();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Hanzi model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $userId = Yii::$app->user->id;
        $seq = HanziTask::getSeq($userId, $id);        
        if ($seq == 1) {
            $this->redirect(['first','id' => $id]);
        } elseif ($seq == 2) {
            $this->redirect(['second','id' => $id]);
        } elseif ($seq ==3) {
            $this->redirect(['determine','id' => $id]);
        } else {
            throw new HttpException(401, '对不起，您无权访问。'); 
        }
    }

    /**
     * Updates an existing Hanzi model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    private function actionModify($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Hanzi model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    private function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Hanzi model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Hanzi the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Hanzi::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    /**
     * Updates an existing Hanzi model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionFirst($id)
    {
        $seq = 1; // 初次拆分
        $userId = Yii::$app->user->id;
        
        if (!HanziTask::checkIdPermission($userId, $id, $seq)){
            throw new HttpException(401, '对不起，您无权访问。'); 
        }

        $next = Yii::$app->request->post('next');

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $next == 'true' ? $this->redirect(['first', 'id' => $model->id + 1]) : $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('first', [
                'model' => $model,
                'seq' => $seq,
            ]);
        }
    }


    /**
     * Updates an existing Hanzi model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionSecond($id)
    {
        $seq = 2; // 二次拆分
        $userId = Yii::$app->user->id;

        if (!HanziTask::checkIdPermission($userId, $id, $seq)){
            throw new HttpException(401, '对不起，您无权访问。'); 
        }

        $next = Yii::$app->request->post('next');


        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $next == 'true' ?  $this->redirect(['second', 'id' => $model->id + 1]) : $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('second', [
                'model' => $model,
                'seq' => $seq,
            ]);
        }
    }

    /**
     * Updates an existing Hanzi model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionDetermine($id)
    {
        $seq = 3; // 判取
        $userId = Yii::$app->user->id;

        if (!HanziTask::checkIdPermission($userId, $id, $seq)){
            throw new HttpException(401, '对不起，您无权访问。'); 
        }

        $next = Yii::$app->request->post('next');

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $next == 'true' ? $this->redirect(['determine', 'id' => $model->id + 1]) : $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('determine', [
                'model' => $model,
                'seq' => $seq,
            ]);
        }
    }

    

    /**
     * Components.
     * @return mixed
     */
    public function actionComponent()
    {
        return $this->render('component');

    }

    /**
     * Help.
     * @return mixed
     */
    public function actionHelp()
    {
        return $this->render('help');

    }

}
